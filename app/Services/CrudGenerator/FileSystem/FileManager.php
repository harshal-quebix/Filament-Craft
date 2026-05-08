<?php

namespace App\Services\CrudGenerator\FileSystem;

use Illuminate\Support\Facades\File;

class FileManager
{
    private array $backups = [];

    public function write(string $path, string $content): void
    {
        $this->backup($path);
        $this->ensureDirectory(dirname($path));
        File::put($path, $content);
    }

    public function ensureWritable(string $path): void
    {
        if (File::exists($path) && !File::isWritable($path)) {
            chmod($path, 0644);
        }
    }

    public function rollback(): void
    {
        foreach ($this->backups as $path => $backup) {
            if ($backup === null) {
                File::delete($path);
            } else {
                File::put($path, $backup);
            }
        }
        
        $this->backups = [];
    }

    private function backup(string $path): void
    {
        if (File::exists($path)) {
            $this->backups[$path] = File::get($path);
        } else {
            $this->backups[$path] = null;
        }
    }

    private function ensureDirectory(string $directory): void
    {
        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
    }
}
