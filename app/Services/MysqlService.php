<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\File;

class MysqlService
{
    private string $host;

    private int $port;

    private string $username;

    private string $password;

    private string $service;

    private string $mysqlBin;

    private string $mysqldumpBin;

    public function __construct()
    {
        $this->host = config('virtualhosts.mysql_host', '127.0.0.1');
        $this->port = (int) config('virtualhosts.mysql_port', 3306);
        $this->username = config('virtualhosts.mysql_username', 'root');
        $this->password = config('virtualhosts.mysql_password', '');
        $this->service = config('virtualhosts.mysql_service', 'MySQL95');
        $this->mysqlBin = config('virtualhosts.mysql_bin', 'mysql');
        $this->mysqldumpBin = config('virtualhosts.mysqldump_bin', 'mysqldump');
    }

    private function buildAuth(): string
    {
        $auth = "-u{$this->username}";
        if ($this->password !== '') {
            $auth .= ' -p"'.$this->password.'"';
        }

        return $auth;
    }

    private function runMysql(string $args, ?string $stdin = null): array
    {
        $bin = trim($this->mysqlBin, '"');
        $cmd = sprintf('"%s" -h%s -P%d %s %s 2>&1', $bin, $this->host, $this->port, $this->buildAuth(), $args);

        if ($stdin !== null) {
            $tmpFile = tempnam(sys_get_temp_dir(), 'sql_');
            File::put($tmpFile, $stdin);
            $cmd = sprintf('"%s" -h%s -P%d %s %s < "%s" 2>&1', $bin, $this->host, $this->port, $this->buildAuth(), $args, $tmpFile);
        }

        $output = shell_exec($cmd);

        if ($stdin !== null && file_exists($tmpFile)) {
            @unlink($tmpFile);
        }

        $success = $output !== null && ! str_contains($output, 'ERROR');
        $exitCode = $success ? 0 : 1;

        return ['success' => $success, 'output' => trim($output ?? ''), 'exit_code' => $exitCode];
    }

    private function runMysqldump(string $args): array
    {
        $bin = trim($this->mysqldumpBin, '"');
        $cmd = sprintf('"%s" -h%s -P%d %s %s 2>&1', $bin, $this->host, $this->port, $this->buildAuth(), $args);

        $output = shell_exec($cmd);
        $success = $output !== null && ! str_contains($output, 'ERROR');

        return ['success' => $success, 'output' => trim($output ?? ''), 'exit_code' => $success ? 0 : 1];
    }

    public function isAvailable(): bool
    {
        $result = $this->runMysql('--version');

        return $result['success'] && str_contains($result['output'], 'mysql');
    }

    public function listDatabases(): array
    {
        $result = $this->runMysql('-Bse "SHOW DATABASES;"');

        if (! $result['success']) {
            return [];
        }

        $databases = [];
        foreach (explode("\n", trim($result['output'])) as $line) {
            $line = trim($line);
            if ($line !== '' && ! in_array($line, ['information_schema', 'performance_schema', 'mysql', 'sys'])) {
                $databases[] = $line;
            }
        }

        return $databases;
    }

    public function createDatabase(string $name): array
    {
        $safeName = preg_replace('/[^a-zA-Z0-9_]/', '', $name);

        return $this->runMysql(sprintf('-e "CREATE DATABASE `%s`;"', $safeName));
    }

    public function dropDatabase(string $name): array
    {
        $safeName = preg_replace('/[^a-zA-Z0-9_]/', '', $name);

        return $this->runMysql(sprintf('-e "DROP DATABASE `%s`;"', $safeName));
    }

    public function getDatabaseSize(string $name): ?string
    {
        $safeName = preg_replace('/[^a-zA-Z0-9_]/', '', $name);
        $result = $this->runMysql(sprintf('-Bse "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb FROM information_schema.tables WHERE table_schema = \'%s\';"', $safeName));

        if ($result['success'] && trim($result['output']) !== '') {
            $size = (float) trim($result['output']);

            return $size >= 1 ? number_format($size, 2).' MB' : number_format($size * 1024, 0).' KB';
        }

        return null;
    }

    public function listTables(string $database): array
    {
        $safeDb = preg_replace('/[^a-zA-Z0-9_]/', '', $database);
        $result = $this->runMysql(sprintf('-Bse "SHOW TABLES FROM `%s`;"', $safeDb));

        if (! $result['success']) {
            return [];
        }

        $tables = [];
        foreach (explode("\n", trim($result['output'])) as $line) {
            $line = trim($line);
            if ($line !== '') {
                $tables[] = $line;
            }
        }

        return $tables;
    }

    public function describeTable(string $database, string $table): array
    {
        $safeDb = preg_replace('/[^a-zA-Z0-9_]/', '', $database);
        $safeTable = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
        $result = $this->runMysql(sprintf('-Bse "DESCRIBE `%s`.`%s`;"', $safeDb, $safeTable));

        if (! $result['success']) {
            return [];
        }

        $columns = [];
        $lines = array_filter(explode("\n", trim($result['output'])));
        foreach ($lines as $line) {
            $parts = explode("\t", $line);
            if (count($parts) >= 6) {
                $columns[] = [
                    'field' => $parts[0],
                    'type' => $parts[1],
                    'null' => $parts[2],
                    'key' => $parts[3],
                    'default' => $parts[4],
                    'extra' => $parts[5],
                ];
            }
        }

        return $columns;
    }

    public function getTableRowCount(string $database, string $table): int
    {
        $safeDb = preg_replace('/[^a-zA-Z0-9_]/', '', $database);
        $safeTable = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
        $result = $this->runMysql(sprintf('-Bse "SELECT COUNT(*) FROM `%s`.`%s`;"', $safeDb, $safeTable));

        if ($result['success']) {
            return (int) trim($result['output']);
        }

        return 0;
    }

    public function getTableData(string $database, string $table, int $page = 1, int $perPage = 50): array
    {
        $safeDb = preg_replace('/[^a-zA-Z0-9_]/', '', $database);
        $safeTable = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
        $offset = ($page - 1) * $perPage;

        $countResult = $this->runMysql(sprintf('-Bse "SELECT COUNT(*) FROM `%s`.`%s`;"', $safeDb, $safeTable));
        $total = $countResult['success'] ? (int) trim($countResult['output']) : 0;

        $result = $this->runMysql(sprintf('-Bse "SELECT * FROM `%s`.`%s` LIMIT %d OFFSET %d;"', $safeDb, $safeTable, $perPage, $offset));

        $rows = [];
        if ($result['success'] && trim($result['output']) !== '') {
            $lines = explode("\n", trim($result['output']));
            foreach ($lines as $line) {
                $rows[] = explode("\t", $line);
            }
        }

        $columns = $this->describeTable($database, $table);
        $headers = array_column($columns, 'field');

        return [
            'headers' => $headers,
            'rows' => $rows,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => (int) ceil($total / $perPage),
        ];
    }

    public function truncateTable(string $database, string $table): array
    {
        $safeDb = preg_replace('/[^a-zA-Z0-9_]/', '', $database);
        $safeTable = preg_replace('/[^a-zA-Z0-9_]/', '', $table);

        return $this->runMysql(sprintf('-e "TRUNCATE TABLE `%s`.`%s`;"', $safeDb, $safeTable));
    }

    public function dropTable(string $database, string $table): array
    {
        $safeDb = preg_replace('/[^a-zA-Z0-9_]/', '', $database);
        $safeTable = preg_replace('/[^a-zA-Z0-9_]/', '', $table);

        return $this->runMysql(sprintf('-e "DROP TABLE `%s`.`%s`;"', $safeDb, $safeTable));
    }

    public function executeQuery(string $database, string $sql): array
    {
        $safeDb = preg_replace('/[^a-zA-Z0-9_]/', '', $database);
        $useResult = $this->runMysql(sprintf('-D `%s`', $safeDb), $sql);

        if (! $useResult['success']) {
            return ['success' => false, 'error' => $useResult['output'], 'rows' => [], 'headers' => [], 'affected' => 0];
        }

        $trimmedSql = trim(strtolower($sql));
        if (str_starts_with($trimmedSql, 'select') || str_starts_with($trimmedSql, 'show') || str_starts_with($trimmedSql, 'describe') || str_starts_with($trimmedSql, 'explain')) {
            $result = $this->runMysql(sprintf('-Bse "%s"', addslashes($sql)), null);
            $safeDb2 = preg_replace('/[^a-zA-Z0-9_]/', '', $database);
            $result = $this->runMysql(sprintf('-D `%s` -Bse "%s"', $safeDb2, addslashes($sql)));

            $rows = [];
            if ($result['success'] && trim($result['output']) !== '') {
                $lines = explode("\n", trim($result['output']));
                foreach ($lines as $line) {
                    $rows[] = explode("\t", $line);
                }
            }

            $headers = [];
            if (str_starts_with($trimmedSql, 'select') && count($rows) > 0) {
                $describeResult = $this->runMysql(sprintf('-Bse "SELECT * FROM (%s) AS tmp LIMIT 0"', addslashes($sql)));
                $safeDb3 = preg_replace('/[^a-zA-Z0-9_]/', '', $database);
                $describeResult = $this->runMysql(sprintf('-D `%s` -e "SELECT * FROM (%s) AS tmp LIMIT 0"', $safeDb3, addslashes($sql)));
                if ($describeResult['success']) {
                    $headers = array_map('trim', explode("\t", trim($describeResult['output'])));
                }
            }

            return ['success' => true, 'rows' => $rows, 'headers' => $headers, 'affected' => count($rows)];
        }

        return ['success' => $useResult['success'], 'error' => $useResult['success'] ? null : $useResult['output'], 'rows' => [], 'headers' => [], 'affected' => 0];
    }

    public function exportDatabase(string $database): array
    {
        $safeDb = preg_replace('/[^a-zA-Z0-9_]/', '', $database);

        return $this->runMysqldump(sprintf('--no-data --routines --triggers `%s`', $safeDb));
    }

    public function exportDatabaseFull(string $database): array
    {
        $safeDb = preg_replace('/[^a-zA-Z0-9_]/', '', $database);

        return $this->runMysqldump(sprintf('`%s`', $safeDb));
    }

    public function exportTable(string $database, string $table): array
    {
        $safeDb = preg_replace('/[^a-zA-Z0-9_]/', '', $database);
        $safeTable = preg_replace('/[^a-zA-Z0-9_]/', '', $table);

        return $this->runMysqldump(sprintf('`%s` `%s`', $safeDb, $safeTable));
    }

    public function importSql(string $database, string $sql): array
    {
        $safeDb = preg_replace('/[^a-zA-Z0-9_]/', '', $database);

        return $this->runMysql(sprintf('-D `%s`', $safeDb), $sql);
    }

    public function isRunning(): bool
    {
        $serviceName = $this->getActiveServiceName();
        if ($serviceName === null) {
            return false;
        }

        $output = [];
        exec(sprintf('sc query "%s" 2>&1', $serviceName), $output);
        $raw = implode("\n", $output);

        return str_contains($raw, 'RUNNING');
    }

    private function getActiveServiceName(): ?string
    {
        $output = [];
        exec('sc query state= all 2>&1', $output);
        $raw = implode("\n", $output);

        if (str_contains($raw, $this->service)) {
            return $this->service;
        }

        if (preg_match('/NOME DO SERVI.C.O:\s*(MySQL\w+)/i', $raw, $m)) {
            return $m[1];
        }

        if (preg_match('/SERVICE_NAME:\s*(MySQL\w+)/i', $raw, $m)) {
            return $m[1];
        }

        return null;
    }

    public function start(): array
    {
        $serviceName = $this->getActiveServiceName() ?? $this->service;
        $output = [];
        $exitCode = 0;
        exec(sprintf('net start "%s" 2>&1', $serviceName), $output, $exitCode);

        return ['success' => $exitCode === 0, 'output' => implode("\n", $output)];
    }

    public function stop(): array
    {
        $serviceName = $this->getActiveServiceName() ?? $this->service;
        $output = [];
        $exitCode = 0;
        exec(sprintf('net stop "%s" 2>&1', $serviceName), $output, $exitCode);

        return ['success' => $exitCode === 0, 'output' => implode("\n", $output)];
    }

    public function restart(): array
    {
        $stopResult = $this->stop();
        if (! $stopResult['success']) {
            return $stopResult;
        }

        sleep(2);

        return $this->start();
    }
}
