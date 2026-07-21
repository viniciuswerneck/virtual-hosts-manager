<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use App\Services\MysqlService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class DatabaseController extends Controller
{
    public function index(MysqlService $mysql)
    {
        if (! $mysql->isAvailable()) {
            return view('databases.index', [
                'databases' => [],
                'running' => false,
                'available' => false,
            ]);
        }

        $running = $mysql->isRunning();
        $databases = $running ? $mysql->listDatabases() : [];

        $sizes = [];
        if ($running) {
            foreach ($databases as $db) {
                $sizes[$db] = $mysql->getDatabaseSize($db);
            }
        }

        return view('databases.index', compact('databases', 'running', 'sizes'));
    }

    public function store(Request $request, MysqlService $mysql, ActivityLogService $logger)
    {
        $request->validate([
            'name' => 'required|regex:/^[a-zA-Z0-9_]+$/',
        ]);

        $result = $mysql->createDatabase($request->input('name'));

        if ($result['success']) {
            $logger->log('created', __('Banco de dados :name criado', ['name' => $request->input('name')]));

            return redirect()->route('databases.index')->with('success', __('Banco de dados :name criado com sucesso!', ['name' => $request->input('name')]));
        }

        return redirect()->back()->with('error', __('Erro ao criar banco: ').$result['output']);
    }

    public function destroy(string $database, MysqlService $mysql, ActivityLogService $logger)
    {
        $result = $mysql->dropDatabase($database);

        if ($result['success']) {
            $logger->log('deleted', __('Banco de dados :name deletado', ['name' => $database]));

            return redirect()->route('databases.index')->with('success', __('Banco de dados :name deletado.', ['name' => $database]));
        }

        return redirect()->back()->with('error', __('Erro ao deletar banco: ').$result['output']);
    }

    public function tables(string $database, MysqlService $mysql)
    {
        $tables = $mysql->listTables($database);
        $counts = [];
        foreach ($tables as $table) {
            $counts[$table] = $mysql->getTableRowCount($database, $table);
        }

        return view('databases.tables', compact('database', 'tables', 'counts'));
    }

    public function tableStructure(string $database, string $table, MysqlService $mysql)
    {
        $columns = $mysql->describeTable($database, $table);
        $rowCount = $mysql->getTableRowCount($database, $table);

        return view('databases.table-structure', compact('database', 'table', 'columns', 'rowCount'));
    }

    public function tableData(Request $request, string $database, string $table, MysqlService $mysql)
    {
        $page = (int) $request->input('page', 1);
        $data = $mysql->getTableData($database, $table, $page);

        return view('databases.table-data', compact('database', 'table') + $data);
    }

    public function truncateTable(string $database, string $table, MysqlService $mysql, ActivityLogService $logger)
    {
        $result = $mysql->truncateTable($database, $table);

        if ($result['success']) {
            $logger->log('updated', __('Tabela :table do banco :db truncada', ['table' => $table, 'db' => $database]));

            return redirect()->route('databases.table-data', ['database' => $database, 'table' => $table])
                ->with('success', __('Tabela :table truncada.', ['table' => $table]));
        }

        return redirect()->back()->with('error', __('Erro ao truncar tabela: ').$result['output']);
    }

    public function dropTable(string $database, string $table, MysqlService $mysql, ActivityLogService $logger)
    {
        $result = $mysql->dropTable($database, $table);

        if ($result['success']) {
            $logger->log('deleted', __('Tabela :table do banco :db deletada', ['table' => $table, 'db' => $database]));

            return redirect()->route('databases.tables', ['database' => $database])
                ->with('success', __('Tabela :table deletada.', ['table' => $table]));
        }

        return redirect()->back()->with('error', __('Erro ao deletar tabela: ').$result['output']);
    }

    public function queryForm(MysqlService $mysql)
    {
        $databases = $mysql->isRunning() ? $mysql->listDatabases() : [];

        return view('databases.query', compact('databases'));
    }

    public function query(Request $request, MysqlService $mysql)
    {
        $request->validate([
            'database' => 'required',
            'sql' => 'required|string|max:10000',
        ]);

        $result = $mysql->executeQuery($request->input('database'), $request->input('sql'));
        $databases = $mysql->isRunning() ? $mysql->listDatabases() : [];

        return view('databases.query', compact('databases') + $result);
    }

    public function exportDatabase(string $database, MysqlService $mysql)
    {
        $result = $mysql->exportDatabaseFull($database);

        if (! $result['success']) {
            return redirect()->back()->with('error', __('Erro ao exportar: ').$result['output']);
        }

        return Response::make($result['output'], 200, [
            'Content-Type' => 'text/sql',
            'Content-Disposition' => "attachment; filename=\"{$database}.sql\"",
        ]);
    }

    public function exportTable(string $database, string $table, MysqlService $mysql)
    {
        $result = $mysql->exportTable($database, $table);

        if (! $result['success']) {
            return redirect()->back()->with('error', __('Erro ao exportar: ').$result['output']);
        }

        return Response::make($result['output'], 200, [
            'Content-Type' => 'text/sql',
            'Content-Disposition' => "attachment; filename=\"{$database}.{$table}.sql\"",
        ]);
    }

    public function importForm(string $database, MysqlService $mysql)
    {
        return view('databases.import', compact('database'));
    }

    public function import(Request $request, string $database, MysqlService $mysql, ActivityLogService $logger)
    {
        $request->validate([
            'sql_file' => 'required|file|mimes:sql,txt',
        ]);

        $sql = file_get_contents($request->file('sql_file')->getRealPath());

        $result = $mysql->importSql($database, $sql);

        if ($result['success']) {
            $logger->log('imported', __('SQL importado no banco :db', ['db' => $database]));

            return redirect()->route('databases.tables', ['database' => $database])
                ->with('success', __('SQL importado com sucesso!'));
        }

        return redirect()->back()->with('error', __('Erro ao importar SQL: ').$result['output']);
    }

    public function serviceStatus(MysqlService $mysql)
    {
        return response()->json([
            'running' => $mysql->isRunning(),
            'available' => $mysql->isAvailable(),
        ]);
    }

    public function serviceStart(MysqlService $mysql, ActivityLogService $logger)
    {
        $result = $mysql->start();

        if ($result['success']) {
            $logger->log('restarted', __('Serviço MySQL iniciado'));

            return redirect()->back()->with('success', __('MySQL iniciado com sucesso.'));
        }

        return redirect()->back()->with('error', __('Erro ao iniciar MySQL: ').$result['output']);
    }

    public function serviceStop(MysqlService $mysql, ActivityLogService $logger)
    {
        $result = $mysql->stop();

        if ($result['success']) {
            $logger->log('restarted', __('Serviço MySQL parado'));

            return redirect()->back()->with('success', __('MySQL parado com sucesso.'));
        }

        return redirect()->back()->with('error', __('Erro ao parar MySQL: ').$result['output']);
    }

    public function serviceRestart(MysqlService $mysql, ActivityLogService $logger)
    {
        $result = $mysql->restart();

        if ($result['success']) {
            $logger->log('restarted', __('Serviço MySQL reiniciado'));

            return redirect()->back()->with('success', __('MySQL reiniciado com sucesso.'));
        }

        return redirect()->back()->with('error', __('Erro ao reiniciar MySQL: ').$result['output']);
    }
}
