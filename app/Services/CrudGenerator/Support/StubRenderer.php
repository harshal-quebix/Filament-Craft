<?php

namespace App\Services\CrudGenerator\Support;

use Illuminate\Support\Facades\File;

class StubRenderer
{
    private static array $cache = [];
    private string $content = '';

    public function load(string $stubName): self
    {
        if (! isset(self::$cache[$stubName])) {
            $path = config('crud-generator.paths.stubs', app_path('Services/CrudGenerator/Stubs')) . "/{$stubName}";
            self::$cache[$stubName] = File::get($path);
        }

        $this->content = self::$cache[$stubName];
        return $this;
    }

    public function replace(array $replacements): string
    {
        $content = $this->content;

        foreach ($replacements as $key => $value) {
            $content = str_replace(["{{ {$key} }}", "{{{$key}}}"], $value, $content);
        }

        return $content;
    }

    public static function clearCache(): void
    {
        self::$cache = [];
    }
}
