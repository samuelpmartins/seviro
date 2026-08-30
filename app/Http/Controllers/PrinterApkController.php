<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PrinterApkController extends Controller
{
    protected function apkBasePath(): string
    {
        return 'printer-apks';
    }

    protected function listFiles(string $folder): array
    {
        $disk = Storage::disk('public');
        $directory = $this->apkBasePath() . '/' . $folder;

        if (!$disk->exists($directory)) {
            return [];
        }

        return collect($disk->files($directory))
            ->map(fn($file) => basename($file))
            ->filter(fn(string $filename) => preg_match('/\.apk$/i', $filename) === 1)
            ->sortDesc()
            ->values()
            ->all();
    }

    protected function extractVersion(string $filename): string
    {
        $name = preg_replace('/\.apk$/i', '', basename($filename));

        if ($name === '') {
            return 'desconhecida';
        }

        $parts = preg_split('/[._-]/', $name);
        $version = collect($parts)
            ->filter(fn($part) => preg_match('/^v?\d+(?:\.\d+)*$/', $part) === 1)
            ->last();

        return $version ?: $name;
    }

    public function latest(): JsonResponse
    {
        $files = $this->listFiles('new');

        if (empty($files)) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhum APK disponível na pasta atual.',
            ], 404);
        }

        $file = $files[0];

        return response()->json([
            'success' => true,
            'version' => $this->extractVersion($file),
            'file' => $file,
            'url' => route('printer-apks.download', ['folder' => 'new', 'file' => $file]),
        ]);
    }

    public function versions(): JsonResponse
    {
        $newFiles = $this->listFiles('new');
        $oldFiles = $this->listFiles('olds');

        $newVersions = collect($newFiles)->map(function (string $file) {
            return [
                'folder' => 'new',
                'name' => $file,
                'version' => $this->extractVersion($file),
                'url' => route('printer-apks.download', ['folder' => 'new', 'file' => $file]),
            ];
        })->values()->all();

        $oldVersions = collect($oldFiles)->map(function (string $file) {
            return [
                'folder' => 'olds',
                'name' => $file,
                'version' => $this->extractVersion($file),
                'url' => route('printer-apks.download', ['folder' => 'olds', 'file' => $file]),
            ];
        })->values()->all();

        return response()->json([
            'success' => true,
            'latest' => !empty($newFiles) ? $newVersions[0] : null,
            'versions' => array_merge($newVersions, $oldVersions),
        ]);
    }

    public function download(string $folder, string $file): BinaryFileResponse
    {
        $allowedFolders = ['new', 'olds'];
        abort_if(!in_array($folder, $allowedFolders, true), 404);

        $safeFilename = basename($file);
        $path = Storage::disk('public')->path($this->apkBasePath() . '/' . $folder . '/' . $safeFilename);

        abort_if(!is_file($path), 404);

        return response()->download($path, $safeFilename, [
            'Content-Type' => 'application/vnd.android.package-archive',
        ]);
    }
}
