<?php

namespace App\Console\Commands;

use App\Models\VirtualHost;
use App\Services\VhostManagerService;
use Illuminate\Console\Command;

class VhostToggleCommand extends Command
{
    protected $signature = 'vhost:toggle {name? : Nome do servidor para ativar/desativar}';
    protected $description = 'Ativa ou desativa um virtual host';

    public function handle(VhostManagerService $manager): int
    {
        $name = $this->argument('name');
        if (!$name) {
            $name = $this->ask('Nome do servidor');
        }

        $vhost = VirtualHost::where('server_name', $name)->first();

        if (!$vhost) {
            $this->error("Virtual host '{$name}' não encontrado.");
            return Command::FAILURE;
        }

        $vhost->update(['active' => !$vhost->active]);

        try {
            $manager->applyApacheConfig();
        } catch (\Throwable $e) {
            $this->warn("Aviso: " . $e->getMessage());
        }

        $status = $vhost->active ? 'ativado' : 'desativado';
        $this->info("✓ Virtual host '{$name}' {$status} com sucesso!");

        return Command::SUCCESS;
    }
}
