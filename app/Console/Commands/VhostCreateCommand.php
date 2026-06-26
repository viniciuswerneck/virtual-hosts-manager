<?php

namespace App\Console\Commands;

use App\Models\VirtualHost;
use App\Services\HostsFileService;
use App\Services\MkcertService;
use App\Services\ProjectScaffoldService;
use App\Services\VhostManagerService;
use Illuminate\Console\Command;

class VhostCreateCommand extends Command
{
    protected $signature = 'vhost:create
        {name? : Nome do servidor (ex: meusite.local)}
        {--dir= : Diretório raiz do projeto}
        {--port=80 : Porta do virtual host}
        {--ssl : Habilitar SSL}
        {--no-ssl : Desabilitar SSL}
        {--template= : Template do projeto (laravel, wordpress, html, php)}
        {--github= : URL do repositório GitHub para clonar}';

    protected $description = 'Cria um novo virtual host';

    public function handle(
        HostsFileService $hosts,
        MkcertService $mkcert,
        VhostManagerService $manager,
        ProjectScaffoldService $scaffold,
    ): int {
        $name = $this->argument('name');
        if (!$name) {
            $name = $this->ask('Nome do servidor (ex: meusite.local)');
        }

        $dir = $this->option('dir');
        if (!$dir) {
            $dir = $this->ask('Diretório raiz', config('virtualhosts.default_document_root') . $name);
        }

        $port = (int) $this->option('port');
        $sslEnabled = true;
        if ($this->option('no-ssl')) {
            $sslEnabled = false;
        } elseif (!$this->option('ssl')) {
            $sslEnabled = $this->confirm('Habilitar SSL (HTTPS)?', true);
        }

        $template = $this->option('template');
        if (!$template && !$this->option('github')) {
            $template = $this->choice('Template do projeto (opcional)', ['nenhum', 'laravel', 'wordpress', 'html', 'php'], 'nenhum');
            if ($template === 'nenhum') {
                $template = null;
            }
        }

        $githubUrl = $this->option('github');

        if (VirtualHost::where('server_name', $name)->exists()) {
            $this->error("O virtual host '{$name}' já existe!");
            return Command::FAILURE;
        }

        try {
            if ($template || $githubUrl) {
                $this->task("Criando scaffold do projeto em {$dir}", function () use ($scaffold, $template, $dir, $githubUrl) {
                    $result = $scaffold->scaffold($template ?? 'html', $dir, $githubUrl);
                    if (!$result['success']) {
                        throw new \RuntimeException($result['message']);
                    }
                    return true;
                });
            }

            $vhost = VirtualHost::create([
                'server_name' => $name,
                'document_root' => $dir,
                'port' => $port,
                'ssl_enabled' => $sslEnabled,
                'template' => $template,
                'github_url' => $githubUrl,
                'active' => true,
            ]);

            $this->task("Adicionando entrada no arquivo hosts", function () use ($hosts, $name) {
                $hosts->addEntry($name);
            });

            if ($sslEnabled) {
                $this->task("Gerando certificado SSL", function () use ($mkcert, $name) {
                    $result = $mkcert->generate($name);
                    if (!$result['success']) {
                        throw new \RuntimeException('Erro ao gerar SSL: ' . ($result['output'] ?? ''));
                    }
                });
            }

            $this->task("Aplicando config no Apache", function () use ($manager) {
                $manager->applyApacheConfig();
            });

            $this->newLine();
            $this->info("✓ Virtual host '{$name}' criado com sucesso!");
            $this->line("  Diretório: {$dir}");
            $this->line("  URL: " . ($sslEnabled ? 'https' : 'http') . "://{$name}");

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Erro: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
