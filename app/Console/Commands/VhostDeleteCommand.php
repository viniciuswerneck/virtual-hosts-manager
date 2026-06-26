<?php

namespace App\Console\Commands;

use App\Models\VirtualHost;
use App\Services\HostsFileService;
use App\Services\MkcertService;
use App\Services\VhostManagerService;
use Illuminate\Console\Command;

class VhostDeleteCommand extends Command
{
    protected $signature = 'vhost:delete {name? : Nome do servidor para remover}';
    protected $description = 'Remove um virtual host';

    public function handle(
        HostsFileService $hosts,
        MkcertService $mkcert,
        VhostManagerService $manager,
    ): int {
        $name = $this->argument('name');
        if (!$name) {
            $name = $this->ask('Nome do servidor para remover');
        }

        $vhost = VirtualHost::where('server_name', $name)->first();

        if (!$vhost) {
            $this->error("Virtual host '{$name}' não encontrado.");
            return Command::FAILURE;
        }

        if (!$this->confirm("Remover '{$name}'? Isso vai deletar o hosts, certificado SSL e config do Apache.", false)) {
            $this->info('Operação cancelada.');
            return Command::SUCCESS;
        }

        try {
            $this->task("Removendo entrada do hosts", function () use ($hosts, $name) {
                $hosts->removeEntry($name);
            });

            $this->task("Removendo certificado SSL", function () use ($mkcert, $name) {
                $mkcert->delete($name);
            });

            $vhost->delete();

            $this->task("Reaplicando config do Apache", function () use ($manager) {
                $manager->applyApacheConfig();
            });

            $this->newLine();
            $this->info("✓ Virtual host '{$name}' removido com sucesso!");

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Erro: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
