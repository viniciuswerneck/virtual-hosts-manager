<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVirtualHostRequest;
use App\Models\VirtualHost;
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
    ) {
        $name = $virtualHost->server_name;

        try {
            $hosts->removeEntry($name);
            $mkcert->delete($name);

            $virtualHost->delete();

            $result = $manager->applyApacheConfig();

            return redirect()->route('virtual-hosts.index')
                ->with($result['type'], "Virtual host {$name} excluído com sucesso!|{$result['message']}");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function toggleActive(VirtualHost $virtualHost, VhostManagerService $manager, ApacheService $apache)
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

        if (!$configWritten || !$apacheRestarted) {
            $service = config('virtualhosts.apache_service');
            return redirect()->back()
                ->with('warning', "Vhost {$status} no banco, mas o Apache precisa ser reiniciado para aplicar as mudanças.|{$result['message']}<br><br>Execute no PowerShell como Administrador:<br><code>net stop {$service} && net start {$service}</code>");
        }

        return redirect()->back()
            ->with('success', "Virtual host {$virtualHost->server_name} {$status} com sucesso! Apache reiniciado.");
    }

    public function exportJson()
    {
        $vhosts = VirtualHost::orderBy('server_name')->get(['server_name', 'document_root', 'ssl_enabled', 'port', 'notes', 'github_url']);
        return response()->streamDownload(function () use ($vhosts) {
            echo $vhosts->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }, 'virtual-hosts-backup-' . date('Y-m-d-His') . '.json', ['Content-Type' => 'application/json']);
    }

    public function importJson(Request $request, VhostManagerService $manager)
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
                        'notes' => $item['notes'] ?? null,
                        'github_url' => $item['github_url'] ?? null,
                    ]
                );
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

    public function sync(VhostManagerService $manager)
    {
        $count = $manager->syncFromApache();

        return redirect()->route('virtual-hosts.index')
            ->with('success', "{$count} virtual hosts importados do Apache com sucesso!");
    }

    public function restartApache(ApacheService $apache, VhostManagerService $manager)
    {
        $service = config('virtualhosts.apache_service');

        $allVhosts = \App\Models\VirtualHost::all(['server_name', 'document_root', 'ssl_enabled', 'port', 'active'])->toArray();

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

        if ($result['success']) {
            return redirect()->route('virtual-hosts.index')
                ->with('success', 'Apache reiniciado com sucesso!');
        }

        return redirect()->route('virtual-hosts.index')
            ->with('error', $result['output']);
    }

    public function regenerateCert(VirtualHost $virtualHost, VhostManagerService $manager)
    {
        $name = $virtualHost->server_name;
        $result = $manager->regenerateCert($virtualHost);

        if ($result['success']) {
            return redirect()->route('virtual-hosts.index')
                ->with('success', "Certificado SSL para {$name} regenerado com sucesso!");
        }

        return redirect()->route('virtual-hosts.index')
            ->with('error', 'Erro ao gerar certificado: ' . $result['output']);
    }
}
