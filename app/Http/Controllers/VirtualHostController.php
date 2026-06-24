<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVirtualHostRequest;
use App\Models\VirtualHost;
use App\Services\ApacheService;
use App\Services\HostsFileService;
use App\Services\MkcertService;
use App\Services\VhostManagerService;
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

    public function store(
        StoreVirtualHostRequest $request,
        HostsFileService $hosts,
        MkcertService $mkcert,
        VhostManagerService $manager,
    ) {
        $data = $request->validated();
        $serverName = $data['server_name'];

        try {
            $vhost = VirtualHost::create($data);

            $hosts->addEntry($serverName);

            if (($data['ssl_enabled'] ?? true) && !$mkcert->certExists($serverName)) {
                $mkcert->generate($serverName);
            }

            $result = $manager->applyApacheConfig();

            return redirect()->route('virtual-hosts.index')
                ->with($result['type'], "Virtual host {$serverName} criado com sucesso!|{$result['message']}");
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
        } catch (RuntimeException $e) {
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
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function sync(VhostManagerService $manager)
    {
        $count = $manager->syncFromApache();

        return redirect()->route('virtual-hosts.index')
            ->with('success', "{$count} virtual hosts importados do Apache com sucesso!");
    }

    public function restartApache(ApacheService $apache)
    {
        $service = config('virtualhosts.apache_service');
        $result = $apache->restart();

        if ($result['success']) {
            return redirect()->route('virtual-hosts.index')
                ->with('success', 'Apache reiniciado com sucesso!');
        }

        $output = $result['output'];
        if (str_contains($output, 'Acesso negado') || str_contains($output, 'Access denied')) {
            return redirect()->route('virtual-hosts.index')
                ->with('error', "Permissão negada. Execute no PowerShell como Administrador: net stop {$service} && net start {$service}");
        }

        if (str_contains($output, 'AH00141') || str_contains($output, 'random number generator')) {
            return redirect()->route('virtual-hosts.index')
                ->with('warning', "Apache com erro de SSL (AH00141). Tente reiniciar manualmente no PowerShell como Administrador: net stop {$service} && net start {$service}. Se persistir, edite o httpd.conf e comente a linha 'LoadModule ssl_module' se nao precisar de SSL.");
        }

        return redirect()->route('virtual-hosts.index')
            ->with('error', 'Erro ao reiniciar Apache: ' . $output);
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
