<?php

namespace App\Services;

class PhpConfigService
{
    private IniFileService $ini;

    private array $directiveMeta = [];

    public function __construct()
    {
        $path = config('virtualhosts.php_ini_file');

        if (! $path || ! file_exists($path)) {
            $loaded = php_ini_loaded_file();
            $path = $loaded ?: 'C:/php/php.ini';
        }

        $this->ini = new IniFileService(str_replace('\\', '/', $path));
        $this->directiveMeta = $this->buildDirectiveMeta();
    }

    public function getIniFile(): IniFileService
    {
        return $this->ini;
    }

    public function getFilePath(): string
    {
        return $this->ini->getFilePath();
    }

    public function getDirectives(): array
    {
        if (! $this->ini->fileExists()) {
            return $this->directiveMeta;
        }

        $lines = $this->ini->readLines();
        $result = [];

        foreach ($this->directiveMeta as $key => $meta) {
            $value = $this->ini->getValue($lines, $key);
            $enabled = $value !== null ? $this->ini->isEnabled($lines, $key) : true;

            $result[$key] = array_merge($meta, [
                'value' => $value ?? $meta['default'],
                'enabled' => $enabled,
            ]);
        }

        return $result;
    }

    public function updateDirectives(array $data): void
    {
        $lines = $this->ini->readLines();

        foreach ($data as $key => $item) {
            if (! isset($this->directiveMeta[$key])) {
                continue;
            }

            $value = $item['value'] ?? null;
            $enabled = $item['enabled'] ?? null;

            if ($value !== null && $value === '') {
                $value = null;
            }

            $this->ini->setValue($lines, $key, $value, $enabled === null ? null : (bool) $enabled);
        }

        $this->ini->writeLines($lines);
    }

    public function getExtensions(): array
    {
        if (! $this->ini->fileExists()) {
            return [];
        }

        $lines = $this->ini->readLines();
        $matches = $this->ini->findAllMatching($lines, 'extension');

        $extensions = [];
        foreach ($matches as $m) {
            $name = $m['value'];

            if ($name === 'php_opcache' || str_starts_with($name, 'php_')) {
                $display = $name;
            } else {
                $display = $name;
            }

            $extensions[$name] = [
                'name' => $name,
                'enabled' => $m['enabled'],
                'line' => $m['line'],
            ];
        }

        ksort($extensions);

        return $extensions;
    }

    public function toggleExtension(string $extension, bool $enable): void
    {
        $lines = $this->ini->readLines();
        $matches = $this->ini->findAllMatching($lines, 'extension');

        foreach ($matches as $m) {
            if ($m['value'] === $extension) {
                $this->ini->toggle($lines, 'extension', $enable);
                $this->ini->writeLines($lines);

                return;
            }
        }

        if ($enable) {
            $lines[] = 'extension='.$extension;
            $this->ini->writeLines($lines);
        }
    }

    public function getSections(): array
    {
        $sections = [];

        foreach ($this->directiveMeta as $key => $meta) {
            $section = $meta['section'];
            if (! isset($sections[$section])) {
                $sections[$section] = [];
            }
            $sections[$section][] = $key;
        }

        return $sections;
    }

    private function buildDirectiveMeta(): array
    {
        return [
            'memory_limit' => [
                'section' => 'Limites de Recursos',
                'label' => 'Limite de Memória',
                'type' => 'string',
                'default' => '128M',
                'description' => 'Define o limite máximo de memória para um script.',
            ],
            'max_execution_time' => [
                'section' => 'Limites de Recursos',
                'label' => 'Tempo Máx. de Execução',
                'type' => 'integer',
                'default' => '30',
                'description' => 'Tempo máximo em segundos que um script pode executar.',
            ],
            'max_input_time' => [
                'section' => 'Limites de Recursos',
                'label' => 'Tempo Máx. de Input',
                'type' => 'integer',
                'default' => '60',
                'description' => 'Tempo máximo em segundos para processar dados de input.',
            ],
            'upload_max_filesize' => [
                'section' => 'Upload de Arquivos',
                'label' => 'Tamanho Máx. de Upload',
                'type' => 'string',
                'default' => '2M',
                'description' => 'Tamanho máximo permitido para arquivos enviados.',
            ],
            'post_max_size' => [
                'section' => 'Upload de Arquivos',
                'label' => 'Tamanho Máx. de POST',
                'type' => 'string',
                'default' => '8M',
                'description' => 'Tamanho máximo permitido para dados POST.',
            ],
            'max_file_uploads' => [
                'section' => 'Upload de Arquivos',
                'label' => 'Máx. de Uploads Simultâneos',
                'type' => 'integer',
                'default' => '20',
                'description' => 'Número máximo de arquivos enviados simultaneamente.',
            ],
            'upload_tmp_dir' => [
                'section' => 'Upload de Arquivos',
                'label' => 'Diretório Temporário',
                'type' => 'string',
                'default' => '',
                'description' => 'Diretório para arquivos temporários de upload.',
            ],
            'display_errors' => [
                'section' => 'Tratamento de Erros',
                'label' => 'Exibir Erros',
                'type' => 'select',
                'options' => ['On', 'Off', 'stderr', 'stdout'],
                'default' => 'Off',
                'description' => 'Define se erros devem ser exibidos na tela.',
            ],
            'error_reporting' => [
                'section' => 'Tratamento de Erros',
                'label' => 'Nível de Relatório de Erros',
                'type' => 'string',
                'default' => 'E_ALL & ~E_DEPRECATED & ~E_STRICT',
                'description' => 'Define quais níveis de erro são reportados.',
            ],
            'log_errors' => [
                'section' => 'Tratamento de Erros',
                'label' => 'Registrar Erros em Log',
                'type' => 'select',
                'options' => ['On', 'Off'],
                'default' => 'On',
                'description' => 'Registra erros no log do servidor.',
            ],
            'error_log' => [
                'section' => 'Tratamento de Erros',
                'label' => 'Arquivo de Log de Erros',
                'type' => 'string',
                'default' => '',
                'description' => 'Caminho para o arquivo de log de erros do PHP.',
            ],
            'date.timezone' => [
                'section' => 'Data e Local',
                'label' => 'Fuso Horário',
                'type' => 'string',
                'default' => 'America/Sao_Paulo',
                'description' => 'Fuso horário padrão para funções de data.',
            ],
            'session.save_handler' => [
                'section' => 'Sessão',
                'label' => 'Manipulador de Sessão',
                'type' => 'select',
                'options' => ['files', 'redis', 'memcached', 'sqlite', 'mysql'],
                'default' => 'files',
                'description' => 'Manipulador usado para armazenar sessões.',
            ],
            'session.save_path' => [
                'section' => 'Sessão',
                'label' => 'Caminho de Sessão',
                'type' => 'string',
                'default' => '',
                'description' => 'Diretório onde arquivos de sessão são salvos.',
            ],
            'session.gc_maxlifetime' => [
                'section' => 'Sessão',
                'label' => 'Tempo de Vida GC',
                'type' => 'integer',
                'default' => '1440',
                'description' => 'Tempo em segundos para limpeza de sessões expiradas.',
            ],
            'default_charset' => [
                'section' => 'Idioma e Encoding',
                'label' => 'Charset Padrão',
                'type' => 'string',
                'default' => 'UTF-8',
                'description' => 'Charset padrão usado pelo PHP.',
            ],
            'short_open_tag' => [
                'section' => 'Idioma e Encoding',
                'label' => 'Short Open Tag',
                'type' => 'select',
                'options' => ['On', 'Off'],
                'default' => 'On',
                'description' => 'Permite o uso de <? como abertura PHP.',
            ],
            'opcache.enable' => [
                'section' => 'Performance (OPcache)',
                'label' => 'Habilitar OPcache',
                'type' => 'select',
                'options' => ['On', 'Off', '1', '0'],
                'default' => '1',
                'description' => 'Habilita o cache de bytecode OPcache.',
            ],
            'opcache.memory_consumption' => [
                'section' => 'Performance (OPcache)',
                'label' => 'Memória OPcache',
                'type' => 'integer',
                'default' => '128',
                'description' => 'Memória em MB para o OPcache.',
            ],
            'opcache.max_accelerated_files' => [
                'section' => 'Performance (OPcache)',
                'label' => 'Máx. Arquivos Acelerados',
                'type' => 'integer',
                'default' => '10000',
                'description' => 'Número máximo de arquivos no OPcache.',
            ],
        ];
    }
}
