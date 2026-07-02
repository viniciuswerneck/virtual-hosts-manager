<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class FileManagerController extends Controller
{
    public function index(Request $request)
    {
        $path = $request->input('path', config('virtualhosts.default_document_root'));
        $path = str_replace(['\\', '..'], '/', $path);
        $path = rtrim($path, '/');

        if (!File::isDirectory($path)) {
            return redirect()->route('file-manager.index', ['path' => config('virtualhosts.default_document_root')])
                ->with('error', 'Diretório não encontrado.');
        }

        $items = collect(File::directories($path))->map(fn($d) => [
            'name' => basename($d),
            'path' => $d,
            'type' => 'dir',
            'modified' => date('d/m/Y H:i', File::lastModified($d)),
        ])->concat(collect(File::files($path))->map(fn($f) => [
            'name' => $f->getFilename(),
            'path' => $f->getPathname(),
            'type' => 'file',
            'size' => $f->getSize(),
            'human_size' => $this->humanSize($f->getSize()),
            'modified' => date('d/m/Y H:i', $f->getCTime()),
            'extension' => $f->getExtension(),
        ]))->sortBy('name');

        $parent = dirname($path);
        $breadcrumbs = $this->breadcrumbs($path);

        return view('file-manager.index', compact('path', 'items', 'parent', 'breadcrumbs'));
    }

    public function show(Request $request)
    {
        $filePath = $request->input('file');

        if (!$filePath || !File::exists($filePath) || File::isDirectory($filePath)) {
            return redirect()->back()->with('error', 'Arquivo não encontrado.');
        }

        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        $textExtensions = ['txt', 'md', 'php', 'html', 'css', 'js', 'json', 'xml', 'yml', 'yaml', 'env', 'gitignore', 'htaccess', 'conf', 'sql', 'sh', 'bat', 'ini', 'cfg'];
        $isText = in_array(strtolower($ext ?? ''), $textExtensions, true);

        if (!$isText) {
            return redirect()->back()->with('error', 'Visualização de arquivos binários não suportada.');
        }

        $content = File::get($filePath);
        $name = basename($filePath);
        $dir = dirname($filePath);

        return view('file-manager.show', compact('content', 'name', 'filePath', 'dir'));
    }

    private function humanSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < 3) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, $i > 0 ? 1 : 0) . ' ' . $units[$i];
    }

    private function breadcrumbs(string $path): array
    {
        $parts = explode('/', str_replace('\\', '/', $path));
        $crumbs = [];
        $current = '';
        foreach ($parts as $part) {
            if ($part === '') continue;
            $current .= '/' . $part;
            $crumbs[] = ['name' => $part, 'path' => $current];
        }
        return $crumbs;
    }
}
