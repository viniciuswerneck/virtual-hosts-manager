<?php

namespace App\Http\Controllers;

use App\Services\ApacheConfigService;
use App\Services\PhpConfigService;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    public function phpIndex(PhpConfigService $php)
    {
        $directives = $php->getDirectives();
        $extensions = $php->getExtensions();
        $sections = $php->getSections();
        $filePath = $php->getFilePath();
        $fileExists = $php->getIniFile()->fileExists();

        return view('config.php', compact('directives', 'extensions', 'sections', 'filePath', 'fileExists'));
    }

    public function phpUpdate(Request $request, PhpConfigService $php)
    {
        $directives = $request->input('directives', []);

        try {
            $php->updateDirectives($directives);

            return redirect()->route('config.php.index')
                ->with('success', 'Configurações do PHP salvas com sucesso!|Reinicie o Apache se alterou diretivas que exigem restart.');
        } catch (\Throwable $e) {
            return redirect()->route('config.php.index')
                ->with('error', $e->getMessage());
        }
    }

    public function phpExtensionToggle(Request $request, PhpConfigService $php)
    {
        $extension = $request->input('extension');
        $enable = $request->input('enable', false);

        if (empty($extension)) {
            return redirect()->route('config.php.index')
                ->with('error', 'Extensão não informada.');
        }

        try {
            $php->toggleExtension($extension, (bool) $enable);

            return redirect()->route('config.php.index')
                ->with('success', 'Extensão '.($enable ? 'ativada' : 'desativada').' com sucesso!');
        } catch (\Throwable $e) {
            return redirect()->route('config.php.index')
                ->with('error', $e->getMessage());
        }
    }

    public function apacheIndex(ApacheConfigService $apache)
    {
        $directives = $apache->getDirectives();
        $modules = $apache->getModules();
        $filePath = $apache->getConfigPath();
        $fileExists = $apache->fileExists();
        $configTest = $apache->testConfig();

        return view('config.apache', compact('directives', 'modules', 'filePath', 'fileExists', 'configTest'));
    }

    public function apacheUpdate(Request $request, ApacheConfigService $apache)
    {
        $directives = $request->input('directives', []);

        try {
            $apache->updateDirectives($directives);

            return redirect()->route('config.apache.index')
                ->with('success', 'Configurações do Apache salvas com sucesso!|Execute o teste de configuração antes de reiniciar.');
        } catch (\Throwable $e) {
            return redirect()->route('config.apache.index')
                ->with('error', $e->getMessage());
        }
    }

    public function apacheToggleModule(Request $request, ApacheConfigService $apache)
    {
        $module = $request->input('module');
        $enable = $request->input('enable', false);

        if (empty($module)) {
            return redirect()->route('config.apache.index')
                ->with('error', 'Módulo não informado.');
        }

        try {
            $apache->toggleModule($module, (bool) $enable);

            return redirect()->route('config.apache.index')
                ->with('success', 'Módulo '.($enable ? 'ativado' : 'desativado').' com sucesso!');
        } catch (\Throwable $e) {
            return redirect()->route('config.apache.index')
                ->with('error', $e->getMessage());
        }
    }

    public function apacheTest(ApacheConfigService $apache)
    {
        $result = $apache->testConfig();

        if ($result['success']) {
            return redirect()->route('config.apache.index')
                ->with('success', 'Teste de configuração: OK|Sintaxe do Apache válida.');
        }

        return redirect()->route('config.apache.index')
            ->with('error', 'Erro na configuração:|'.$result['output']);
    }

    public function apacheRestart(ApacheConfigService $apache)
    {
        $result = $apache->restart();

        if ($result['success']) {
            return redirect()->route('config.apache.index')
                ->with('success', 'Apache reiniciado com sucesso!');
        }

        return redirect()->route('config.apache.index')
            ->with('error', $result['output']);
    }
}
