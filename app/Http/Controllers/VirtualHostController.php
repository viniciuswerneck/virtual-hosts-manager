<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVirtualHostRequest;
use App\Models\VirtualHost;
use App\Services\ActivityLogService;
use App\Services\ApacheService;
use App\Services\HostsFileService;
use App\Services\MkcertService;
use App\Services\ProjectScaffoldService;
use App\Services\VhostManagerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use ZipArchive;

class VirtualHostController extends Controller
{
    public function index(ApacheService $apache, \Illuminate\Http\Request $request)
    {
        $search = $request->input('search');

        $vhosts = VirtualHost::when($search, fn($q) => $q->where('server_name', 'like', "%{$search}%")
            ->orWhere('document_root', 'like', "%{$search}%")
            ->orWhere('notes', 'like', "%{$search}%"))
            ->orderBy('server_name')
            ->paginate(15)
            ->withQueryString();

        $apacheVhosts = $apache->parseExisting();
        $apacheNames = array_column($apacheVhosts, 'server_name');

        $certDir = config('virtualhosts.mkcert_dir');
        $certStatus = [];
        foreach ($vhosts as $v) {
            $certStatus[$v->server_name] = File::exists("{$certDir}/{$v->server_name}.pem");
        }

        return view('virtual-hosts.index', compact('vhosts', 'apacheNames', 'search', 'certStatus'));
    }

    public function show(VirtualHost $virtualHost)
    {
        return view('virtual-hosts.show', compact('virtualHost'));
    }

    public function create()
    {
        return view('virtual-hosts.create');
    }

    public function store(
        StoreVirtualHostRequest $request,
        HostsFileService $hosts,
        MkcertService $mkcert,
        VhostManagerService $manager,
        ProjectScaffoldService $scaffold,
        ActivityLogService $logger,
    ) {
        $data = $request->validated();
        $serverName = $data['server_name'];
        $template = $data['template'] ?? null;
        $githubUrl = $data['github_url'] ?? null;

        $scaffolded = false;
        $hostEntryAdded = false;
        $certGenerated = false;

        try {
            if ($template || $githubUrl) {
                $result = $scaffold->scaffold($template, $data['document_root'], $githubUrl);
                if ($result['success']) {
                    $scaffolded = true;
                }
            }

            $vhost = VirtualHost::create($data);

            $hosts->addEntry($serverName);
            $hostEntryAdded = true;

            if (($data['ssl_enabled'] ?? true) && !$mkcert->certExists($serverName)) {
                $result = $mkcert->generate($serverName);
                if (!$result['success']) {
                    throw new RuntimeException('Erro ao gerar certificado SSL: ' . ($result['output'] ?? 'erro desconhecido'));
                }
                $certGenerated = true;
            }

            $result = $manager->applyApacheConfig();

            $logger->log('created', "Virtual host {$serverName} criado", $vhost);

            $msg = "Virtual host {$serverName} criado com sucesso!";
            if ($scaffolded) {
                $msg .= " Projeto scaffolded no diretório.";
            }

            return redirect()->route('virtual-hosts.index')
                ->with($result['type'], "{$msg}|{$result['message']}");
        } catch (\Throwable $e) {
            if (isset($vhost)) {
                $vhost->delete();
            }
            if ($certGenerated) {
                $mkcert->delete($serverName);
            }
            if ($hostEntryAdded) {
                try {
                    $hosts->removeEntry($serverName);
                } catch (\Throwable) {
                }
            }
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function edit(VirtualHost $virtualHost)
    {
        return view('virtual-hosts.edit', compact('virtualHost'));
    }

    public function update(
        StoreVirtualHostRequest $request,
        VirtualHost $virtualHost,
        HostsFileService $hosts,
        MkcertService $mkcert,
        VhostManagerService $manager,
        ActivityLogService $logger,
    ) {
        $oldName = $virtualHost->server_name;
        $data = $request->validated();
        $newName = $data['server_name'];
        $sslEnabled = $data['ssl_enabled'] ?? true;

        try {
            if ($oldName !== $newName) {
                $hosts->removeEntry($oldName);
                $hosts->addEntry($newName);

                if ($mkcert->certExists($oldName)) {
                    $mkcert->delete($oldName);
                }
            }

            if ($sslEnabled && !$mkcert->certExists($newName)) {
                $mkcert->generate($newName);
            }

            $virtualHost->update($data);

            $result = $manager->applyApacheConfig();

            $logger->log('updated', "Virtual host {$newName} atualizado", $virtualHost);

            return redirect()->route('virtual-hosts.index')
                ->with($result['type'], "Virtual host {$newName} atualizado com sucesso!|{$result['message']}");
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function destroy(
        VirtualHost $virtualHost,
        HostsFileService $hosts,
        MkcertService $mkcert,
        VhostManagerService $manager,
        ActivityLogService $logger,
    ) {
        $name = $virtualHost->server_name;

        try {
            $hosts->removeEntry($name);
            $mkcert->delete($name);

            $virtualHost->delete();

            $result = $manager->applyApacheConfig();

            $logger->log('deleted', "Virtual host {$name} excluído", $virtualHost);

            return redirect()->route('virtual-hosts.index')
                ->with($result['type'], "Virtual host {$name} excluído com sucesso!|{$result['message']}");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function toggleActive(VirtualHost $virtualHost, VhostManagerService $manager, ApacheService $apache, ActivityLogService $logger)
    {
        $wasActive = $virtualHost->active;
        $virtualHost->update(['active' => !$wasActive]);

        $configWritten = false;
        $apacheRestarted = false;

        try {
            $result = $manager->applyApacheConfig();
            $configWritten = true;
            $apacheRestarted = $result['type'] === 'success';
        } catch (\Throwable $e) {
            return redirect()->back()->with('warning', "Vhost alternado no banco, mas erro ao aplicar config: {$e->getMessage()}");
        }

        Cache::forget('apache_running');

        $status = $virtualHost->active ? 'ativado' : 'desativado';
        $logger->log('toggled', "Virtual host {$virtualHost->server_name} {$status}", $virtualHost);

        if (!$configWritten || !$apacheRestarted) {
            $service = config('virtualhosts.apache_service');
            return redirect()->back()
                ->with('warning', "Vhost {$status} no banco, mas o Apache precisa ser reiniciado para aplicar as mudanças.|{$result['message']}<br><br>Execute no PowerShell como Administrador:<br><code>net stop {$service} && net start {$service}</code>");
        }

        return redirect()->back()
            ->with('success', "Virtual host {$virtualHost->server_name} {$status} com sucesso! Apache reiniciado.");
    }

    public function batchToggle(Request $request, VhostManagerService $manager, ApacheService $apache, ActivityLogService $logger)
    {
        $ids = $request->input('ids', []);
        $action = $request->input('action'); // 'activate' or 'deactivate'

        if (empty($ids) || !in_array($action, ['activate', 'deactivate'])) {
            return redirect()->back()->with('error', 'Selecione pelo menos um vhost.');
        }

        $active = $action === 'activate';
        $count = 0;

        DB::beginTransaction();
        try {
            foreach ($ids as $id) {
                $vhost = VirtualHost::find($id);
                if ($vhost && $vhost->active !== $active) {
                    $vhost->update(['active' => $active]);
                    $logger->log('batch_' . $action, "Virtual host {$vhost->server_name} " . ($active ? 'ativado' : 'desativado') . ' (lote)', $vhost);
                    $count++;
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erro ao processar operação em lote: ' . $e->getMessage());
        }

        if ($count === 0) {
            return redirect()->back()->with('warning', 'Nenhum vhost foi alterado (já estavam no estado desejado).');
        }

        try {
            $result = $manager->applyApacheConfig();
        } catch (\Throwable $e) {
            return redirect()->back()->with('warning', "{$count} vhost(s) alterados, mas erro ao aplicar config: {$e->getMessage()}");
        }

        $statusLabel = $active ? 'ativado' : 'desativado';
        return redirect()->back()
            ->with($result['type'], "{$count} vhost(s) {$statusLabel} em lote!|{$result['message']}");
    }

    public function batchDelete(Request $request, VhostManagerService $manager, HostsFileService $hosts, MkcertService $mkcert, ActivityLogService $logger)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return redirect()->back()->with('error', 'Selecione pelo menos um vhost.');
        }

        $count = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($ids as $id) {
                $vhost = VirtualHost::find($id);
                if (!$vhost) continue;

                try {
                    $hosts->removeEntry($vhost->server_name);
                } catch (\Throwable) {
                }

                try {
                    $mkcert->delete($vhost->server_name);
                } catch (\Throwable) {
                }

                $logger->log('batch_delete', "Virtual host {$vhost->server_name} excluído (lote)", $vhost);
                $vhost->delete();
                $count++;
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erro ao excluir em lote: ' . $e->getMessage());
        }

        try {
            $manager->applyApacheConfig();
        } catch (\Throwable) {
        }

        return redirect()->route('virtual-hosts.index')
            ->with('success', "{$count} vhost(s) excluídos em lote!");
    }

    public function exportJson()
    {
        $vhosts = VirtualHost::orderBy('server_name')->get(['server_name', 'document_root', 'ssl_enabled', 'port', 'active', 'template', 'php_version', 'notes', 'github_url']);
        return response()->streamDownload(function () use ($vhosts) {
            echo $vhosts->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }, 'virtual-hosts-backup-' . date('Y-m-d-His') . '.json', ['Content-Type' => 'application/json']);
    }

    public function importJson(Request $request, VhostManagerService $manager, ActivityLogService $logger)
    {
        $request->validate(['backup_file' => 'required|file|mimes:json,txt|max:2048']);

        $content = File::get($request->file('backup_file'));
        $data = json_decode($content, true);

        if (!is_array($data) || empty($data)) {
            return redirect()->back()->with('error', 'Arquivo JSON inválido ou vazio.');
        }

        $imported = 0;
        foreach ($data as $item) {
            if (empty($item['server_name'])) continue;

            try {
                VirtualHost::firstOrCreate(
                    ['server_name' => $item['server_name']],
                    [
                        'document_root' => $item['document_root'] ?? config('virtualhosts.default_document_root'),
                        'ssl_enabled' => $item['ssl_enabled'] ?? true,
                        'port' => $item['port'] ?? 80,
                        'active' => $item['active'] ?? true,
                        'template' => $item['template'] ?? null,
                        'php_version' => $item['php_version'] ?? null,
                        'notes' => $item['notes'] ?? null,
                        'github_url' => $item['github_url'] ?? null,
                    ]
                );
                $logger->log('imported', "Virtual host {$item['server_name']} importado do JSON");
                $imported++;
            } catch (\Throwable) {
            }
        }

        try {
            $manager->applyApacheConfig();
        } catch (\Throwable) {
        }

        return redirect()->route('virtual-hosts.index')
            ->with('success', "{$imported} virtual hosts importados com sucesso!");
    }

    public function exportFullBackup(ActivityLogService $logger)
    {
        $tempDir = storage_path('app/backup-temp-' . date('YmdHis'));
        File::ensureDirectoryExists($tempDir);

        try {
            $vhosts = VirtualHost::all()->toArray();
            File::put("{$tempDir}/vhosts.json", json_encode($vhosts, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            $settings = \App\Models\Setting::all()->toArray();
            File::put("{$tempDir}/settings.json", json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            $mkcertDir = config('virtualhosts.mkcert_dir');
            $certDir = "{$tempDir}/certs";
            File::ensureDirectoryExists($certDir);
            if (File::isDirectory($mkcertDir)) {
                foreach (File::files($mkcertDir) as $file) {
                    $ext = $file->getExtension();
                    if (in_array($ext, ['pem', 'key'])) {
                        File::copy($file->getPathname(), "{$certDir}/{$file->getFilename()}");
                    }
                }
            }

            $hostsFile = config('virtualhosts.hosts_file');
            if (File::exists($hostsFile)) {
                File::copy($hostsFile, "{$tempDir}/hosts.txt");
            }

            $apacheConfig = config('virtualhosts.apache_vhosts_file');
            if (File::exists($apacheConfig)) {
                File::copy($apacheConfig, "{$tempDir}/httpd-vhosts.conf");
            }

            $zipPath = storage_path('app/backup-' . date('Y-m-d-His') . '.zip');
            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
                throw new RuntimeException('Erro ao criar arquivo ZIP.');
            }

            $files = File::allFiles($tempDir);
            foreach ($files as $file) {
                $relative = substr($file->getPathname(), strlen($tempDir) + 1);
                $zip->addFile($file->getPathname(), $relative);
            }
            $zip->close();

            File::deleteDirectory($tempDir);

            $logger->log('backup', 'Backup completo exportado');

            return response()->download($zipPath)->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            File::deleteDirectory($tempDir);
            return redirect()->back()->with('error', 'Erro ao exportar backup: ' . $e->getMessage());
        }
    }

    public function importFullBackup(Request $request, ActivityLogService $logger)
    {
        $request->validate(['backup_file' => 'required|file|mimes:zip|max:51200']);

        $tempDir = storage_path('app/backup-restore-' . date('YmdHis'));
        File::ensureDirectoryExists($tempDir);

        try {
            $zip = new ZipArchive();
            if ($zip->open($request->file('backup_file')) !== true) {
                throw new RuntimeException('Erro ao abrir arquivo ZIP.');
            }
            $zip->extractTo($tempDir);
            $zip->close();

            $imported = 0;
            $vhostsFile = "{$tempDir}/vhosts.json";
            if (File::exists($vhostsFile)) {
                $data = json_decode(File::get($vhostsFile), true);
                if (is_array($data)) {
                    foreach ($data as $item) {
                        if (empty($item['server_name'])) continue;
                        try {
                            VirtualHost::updateOrCreate(
                                ['server_name' => $item['server_name']],
                                [
                                    'document_root' => $item['document_root'] ?? config('virtualhosts.default_document_root'),
                                    'ssl_enabled' => $item['ssl_enabled'] ?? true,
                                    'port' => $item['port'] ?? 80,
                                    'active' => $item['active'] ?? true,
                                    'template' => $item['template'] ?? null,
                                    'php_version' => $item['php_version'] ?? null,
                                    'notes' => $item['notes'] ?? null,
                                    'github_url' => $item['github_url'] ?? null,
                                ]
                            );
                            $imported++;
                        } catch (\Throwable) {
                        }
                    }
                }
            }

            $settingsFile = "{$tempDir}/settings.json";
            if (File::exists($settingsFile)) {
                $data = json_decode(File::get($settingsFile), true);
                if (is_array($data)) {
                    foreach ($data as $item) {
                        if (empty($item['key'])) continue;
                        try {
                            \App\Models\Setting::updateOrCreate(
                                ['key' => $item['key']],
                                ['value' => $item['value'] ?? '']
                            );
                        } catch (\Throwable) {
                        }
                    }
                }
            }

            $certsDir = "{$tempDir}/certs";
            if (File::isDirectory($certsDir)) {
                $mkcertDir = config('virtualhosts.mkcert_dir');
                if (!File::isDirectory($mkcertDir)) {
                    File::ensureDirectoryExists($mkcertDir);
                }
                foreach (File::files($certsDir) as $file) {
                    try {
                        File::copy($file->getPathname(), "{$mkcertDir}/{$file->getFilename()}");
                    } catch (\Throwable) {
                    }
                }
            }

            $hostsFile = config('virtualhosts.hosts_file');
            $restoredHosts = "{$tempDir}/hosts.txt";
            if (File::exists($restoredHosts) && File::exists($hostsFile)) {
                try {
                    $existingHosts = File::get($hostsFile);
                    $backupHosts = File::get($restoredHosts);
                    $existingLines = explode("\n", $existingHosts);
                    $backupLines = explode("\n", $backupHosts);
                    foreach ($backupLines as $line) {
                        $line = trim($line);
                        if (empty($line) || str_starts_with($line, '#')) continue;
                        if (!in_array($line, $existingLines)) {
                            $existingHosts .= "\n" . $line;
                        }
                    }
                    File::put($hostsFile, $existingHosts);
                } catch (\Throwable) {
                }
            }

            File::deleteDirectory($tempDir);

            $logger->log('backup_restore', "Backup restaurado: {$imported} vhosts");

            return redirect()->route('virtual-hosts.index')
                ->with('success', "Backup restaurado com sucesso! {$imported} vhosts importados.");
        } catch (\Throwable $e) {
            File::deleteDirectory($tempDir);
            return redirect()->back()->with('error', 'Erro ao restaurar backup: ' . $e->getMessage());
        }
    }

    public function sync(VhostManagerService $manager, ActivityLogService $logger)
    {
        $count = $manager->syncFromApache();

        $logger->log('synced', "Sincronizados {$count} vhosts do Apache");

        return redirect()->route('virtual-hosts.index')
            ->with('success', "{$count} virtual hosts importados do Apache com sucesso!");
    }

    public function restartApache(ApacheService $apache, VhostManagerService $manager, ActivityLogService $logger)
    {
        $service = config('virtualhosts.apache_service');

        $allVhosts = VirtualHost::all(['server_name', 'document_root', 'ssl_enabled', 'port', 'active', 'php_version'])->toArray();

        try {
            $apache->writeConfig($allVhosts);
        } catch (\Throwable $e) {
            return redirect()->route('virtual-hosts.index')
                ->with('error', 'Erro ao reescrever config do Apache: ' . $e->getMessage());
        }

        $test = $apache->testConfig();
        if (!$test['success']) {
            Cache::forget('apache_running');
            return redirect()->route('virtual-hosts.index')
                ->with('error', 'Erro de sintaxe no Apache: ' . $test['output']);
        }

        $result = $apache->restart();

        Cache::forget('apache_running');

        $logger->log('restarted', 'Apache reiniciado');

        if ($result['success']) {
            return redirect()->route('virtual-hosts.index')
                ->with('success', 'Apache reiniciado com sucesso!');
        }

        return redirect()->route('virtual-hosts.index')
            ->with('error', $result['output']);
    }

    public function regenerateCert(VirtualHost $virtualHost, VhostManagerService $manager, ActivityLogService $logger)
    {
        $name = $virtualHost->server_name;
        $result = $manager->regenerateCert($virtualHost);

        if ($result['success']) {
            $logger->log('cert_regenerated', "Certificado SSL para {$name} regenerado", $virtualHost);
            return redirect()->route('virtual-hosts.index')
                ->with('success', "Certificado SSL para {$name} regenerado com sucesso!");
        }

        return redirect()->route('virtual-hosts.index')
            ->with('error', 'Erro ao gerar certificado: ' . $result['output']);
    }
}
