<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVirtualHostRequest;
use App\Models\VirtualHost;
use App\Services\ApacheService;
use App\Services\HostsFileService;
use App\Services\MkcertService;
use Illuminate\Http\Request;
use RuntimeException;

class VirtualHostController extends Controller
{
    public function index(ApacheService $apache)
    {
        $vhosts = VirtualHost::orderBy('server_name')->paginate(15);
        $apacheVhosts = $apache->parseExisting();
        $apacheNames = array_column($apacheVhosts, 'server_name');

        return view('virtual-hosts.index', compact('vhosts', 'apacheNames'));
    }

    public function show(VirtualHost $virtualHost)
    {
        return view('virtual-hosts.show', compact('virtualHost'));
    }

    public function create()
    {
        return view('virtual-hosts.create');
    }

    public function store(StoreVirtualHostRequest $request, ApacheService $apache, HostsFileService $hosts, MkcertService $mkcert)
    {
        $data = $request->validated();
        $serverName = $data['server_name'];

        try {
            $vhost = VirtualHost::create($data);

            $hosts->addEntry($serverName);

            if (($data['ssl_enabled'] ?? true) && !$mkcert->certExists($serverName)) {
                $mkcert->generate($serverName);
            }

            $result = $this->applyApacheConfig($apache);

            return redirect()->route('virtual-hosts.index')
                ->with($result['type'], "Virtual host {$serverName} criado com sucesso! {$result['message']}");
        } catch (RuntimeException $e) {
            if (isset($vhost)) {
                $vhost->delete();
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

    public function update(StoreVirtualHostRequest $request, VirtualHost $virtualHost, ApacheService $apache, HostsFileService $hosts, MkcertService $mkcert)
    {
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

            $result = $this->applyApacheConfig($apache);

            return redirect()->route('virtual-hosts.index')
                ->with($result['type'], "Virtual host {$newName} atualizado com sucesso!<br>{$result['message']}");
        } catch (RuntimeException $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function destroy(VirtualHost $virtualHost, ApacheService $apache, HostsFileService $hosts, MkcertService $mkcert)
    {
        $name = $virtualHost->server_name;

        try {
            $hosts->removeEntry($name);
            $mkcert->delete($name);

            $virtualHost->delete();

            $result = $this->applyApacheConfig($apache);

            return redirect()->route('virtual-hosts.index')
                ->with($result['type'], "Virtual host {$name} excluído com sucesso!<br>{$result['message']}");
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    private function applyApacheConfig(ApacheService $apache): array
    {
        $allVhosts = VirtualHost::all()->toArray();
        $apache->writeConfig($allVhosts);

        $test = $apache->testConfig();
        if (!$test['success']) {
            throw new RuntimeException('Erro na configuração do Apache: ' . $test['output']);
        }

        $restart = $apache->restart();
        if ($restart['success']) {
            return ['type' => 'success', 'message' => 'Apache reiniciado automaticamente.'];
        }

        $msg = $restart['output'];
        if (str_contains($msg, 'Acesso negado') || str_contains($msg, 'Access denied')) {
            return [
                'type' => 'warning',
                'message' => 'Apache precisa ser reiniciado manualmente como Administrador. No PowerShell Admin: net stop ' . config('virtualhosts.apache_service') . ' && net start ' . config('virtualhosts.apache_service'),
            ];
        }

        return ['type' => 'warning', 'message' => 'Aviso: ' . $msg];
    }

    public function sync(ApacheService $apache)
    {
        $apacheVhosts = $apache->parseExisting();

        foreach ($apacheVhosts as $v) {
            VirtualHost::firstOrCreate(
                ['server_name' => $v['server_name']],
                [
                    'document_root' => $v['document_root'],
                    'ssl_enabled' => $v['ssl_enabled'],
                    'port' => $v['port'],
                ]
            );
        }

        return redirect()->route('virtual-hosts.index')
            ->with('success', count($apacheVhosts) . ' virtual hosts importados do Apache com sucesso!');
    }

    public function restartApache(ApacheService $apache)
    {
        $result = $apache->restart();

        if ($result['success']) {
            return redirect()->route('virtual-hosts.index')
                ->with('success', 'Apache reiniciado com sucesso!');
        }

        $output = $result['output'];
        if (str_contains($output, 'Acesso negado') || str_contains($output, 'Access denied')) {
            return redirect()->route('virtual-hosts.index')
                ->with('error', 'Permissão negada para reiniciar o Apache. Execute manualmente no PowerShell como Administrador: net stop ' . config('virtualhosts.apache_service') . ' && net start ' . config('virtualhosts.apache_service'));
        }

        return redirect()->route('virtual-hosts.index')
            ->with('error', 'Erro ao reiniciar Apache: ' . $output);
    }

    public function regenerateCert(VirtualHost $virtualHost, MkcertService $mkcert)
    {
        $name = $virtualHost->server_name;

        $mkcert->delete($name);
        $result = $mkcert->generate($name);

        if ($result['success']) {
            return redirect()->route('virtual-hosts.index')
                ->with('success', "Certificado SSL para {$name} regenerado com sucesso!");
        }

        return redirect()->route('virtual-hosts.index')
            ->with('error', 'Erro ao gerar certificado: ' . $result['output']);
    }
}
