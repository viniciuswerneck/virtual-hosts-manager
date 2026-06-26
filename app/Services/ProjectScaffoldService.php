<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class ProjectScaffoldService
{
    public function scaffold(string $template, string $path, ?string $githubUrl = null): array
    {
        if ($githubUrl) {
            return $this->cloneFromGit($githubUrl, $path);
        }

        return match ($template) {
            'laravel' => $this->scaffoldLaravel($path),
            'wordpress' => $this->scaffoldWordPress($path),
            'html' => $this->scaffoldHtml($path),
            'php' => $this->scaffoldPhp($path),
            default => ['success' => true, 'message' => 'Nenhum template selecionado.'],
        };
    }

    public function cloneFromGit(string $url, string $path): array
    {
        $process = new Process(['git', 'clone', $url, $path]);
        $process->setTimeout(120);
        $process->run();

        if ($process->isSuccessful()) {
            return ['success' => true, 'message' => "Projeto clonado de {$url}"];
        }

        return ['success' => false, 'message' => 'Erro ao clonar repositório: ' . ($process->getErrorOutput() ?: $process->getOutput())];
    }

    private function scaffoldLaravel(string $path): array
    {
        $process = new Process(['composer', 'create-project', 'laravel/laravel', $path]);
        $process->setTimeout(300);
        $process->run();

        if ($process->isSuccessful()) {
            return ['success' => true, 'message' => 'Projeto Laravel criado com sucesso!'];
        }

        return ['success' => false, 'message' => 'Erro ao criar projeto Laravel: ' . ($process->getErrorOutput() ?: $process->getOutput())];
    }

    private function scaffoldWordPress(string $path): array
    {
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        $zipPath = $path . '/wordpress.zip';
        $process = new Process(['powershell', '-Command', "Invoke-WebRequest -Uri 'https://wordpress.org/latest.zip' -OutFile '$zipPath'"]);
        $process->setTimeout(120);
        $process->run();

        if (!$process->isSuccessful()) {
            return ['success' => false, 'message' => 'Erro ao baixar WordPress: ' . ($process->getErrorOutput() ?: $process->getOutput())];
        }

        $extract = new Process(['powershell', '-Command', "Expand-Archive -Path '$zipPath' -DestinationPath '$path' -Force"]);
        $extract->setTimeout(30);
        $extract->run();

        File::delete($zipPath);

        if ($extract->isSuccessful()) {
            $wpFiles = File::glob("{$path}/wordpress/*");
            foreach ($wpFiles as $file) {
                $dest = $path . '/' . basename($file);
                if (File::isDirectory($file)) {
                    File::moveDirectory($file, $dest, true);
                } else {
                    File::move($file, $dest);
                }
            }
            if (File::isDirectory("{$path}/wordpress")) {
                File::deleteDirectory("{$path}/wordpress");
            }
            return ['success' => true, 'message' => 'WordPress baixado e extraído com sucesso!'];
        }

        return ['success' => false, 'message' => 'Erro ao extrair WordPress.'];
    }

    private function scaffoldHtml(string $path): array
    {
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        $indexContent = <<<'HTML'
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Projeto</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="text-center">
        <h1 class="text-4xl font-bold text-gray-800 mb-4">🚀 Projeto Iniciado!</h1>
        <p class="text-gray-600">Seu novo projeto está pronto para começar.</p>
    </div>
</body>
</html>
HTML;

        File::put("{$path}/index.html", $indexContent);
        File::put("{$path}/style.css", "/* Seu CSS aqui */\n");
        File::put("{$path}/script.js", "// Seu JavaScript aqui\n");

        return ['success' => true, 'message' => 'Projeto HTML base criado com sucesso!'];
    }

    private function scaffoldPhp(string $path): array
    {
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        $indexContent = <<<'PHP'
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Projeto PHP</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="max-w-4xl mx-auto py-12 px-4">
        <h1 class="text-4xl font-bold text-gray-800 mb-4">🚀 Projeto PHP</h1>
        <?php
            $info = [
                'PHP Version' => PHP_VERSION,
                'Servidor' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
                'Document Root' => $_SERVER['DOCUMENT_ROOT'] ?? 'N/A',
            ];
        ?>
        <div class="bg-white rounded shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Informações do Servidor</h2>
            <table class="w-full text-sm">
                <?php foreach ($info as $label => $value): ?>
                <tr class="border-b">
                    <td class="py-2 font-medium text-gray-600"><?= $label ?></td>
                    <td class="py-2 text-gray-800"><?= htmlspecialchars($value) ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</body>
</html>
PHP;

        File::put("{$path}/index.php", $indexContent);
        File::put("{$path}/config.php", "<?php\n\n// Configurações do projeto\n");

        return ['success' => true, 'message' => 'Projeto PHP base criado com sucesso!'];
    }
}
