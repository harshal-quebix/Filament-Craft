<?php

namespace App\Services\CrudGenerator\FileSystem;

use Illuminate\Support\Facades\File;

class FileManager
{
    public function write(string $path, string $content): void
    {
        $this->ensureDirectory(dirname($path));
        $this->ensureWritable($path);
        File::put($path, $content);
    }

    public function ensureWritable(string $path): void
    {
        if (File::exists($path) && ! File::isWritable($path)) {
            chmod($path, 0644);
        }
    }

    public function deleteIfExists(string $path): bool
    {
        if (! File::exists($path)) {
            return true;
        }

        return File::delete($path);
    }

    public function deleteDirectoryIfExists(string $dirPath): bool
    {
        if (! File::exists($dirPath)) {
            return true;
        }

        return File::deleteDirectory($dirPath);
    }

    private function ensureDirectory(string $directory): void
    {
        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
    }
}
