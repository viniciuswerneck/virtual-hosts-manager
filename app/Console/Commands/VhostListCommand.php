<?php

namespace App\Console\Commands;

use App\Models\VirtualHost;
use Illuminate\Console\Command;

class VhostListCommand extends Command
{
    protected $signature = 'vhost:list {--search= : Filtrar por nome ou diretório}';
    protected $description = 'Lista todos os virtual hosts cadastrados';

    public function handle(): int
    {
        $query = VirtualHost::orderBy('server_name');

        if ($search = $this->option('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('server_name', 'like', "%{$search}%")
                  ->orWhere('document_root', 'like', "%{$search}%");
            });
        }

        $vhosts = $query->get();

        if ($vhosts->isEmpty()) {
            $this->info('Nenhum virtual host cadastrado.');
            return Command::SUCCESS;
        }

        $headers = ['Nome', 'Diretório', 'Porta', 'SSL', 'Ativo', 'Template'];
        $rows = $vhosts->map(fn($v) => [
            $v->server_name,
            $v->document_root,
            $v->port,
            $v->ssl_enabled ? 'Sim' : 'Não',
            $v->active ? 'Sim' : 'Não',
            $v->template ?? '-',
        ])->toArray();

        $this->table($headers, $rows);
        $this->newLine();
        $this->info("Total: {$vhosts->count()} vhost(s)");

        return Command::SUCCESS;
    }
}
