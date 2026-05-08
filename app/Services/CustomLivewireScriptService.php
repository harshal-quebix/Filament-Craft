<?php

namespace App\Services;

class CustomLivewireScriptService
{
    /**
     * Render Livewire scripts with custom update URI for localhost
     */
    public function renderScripts(): string
    {
        $livewireScriptUrl = $this->getLivewireScriptUrl();
        $updateUri = $this->getCustomUpdateUri();
        $csrfToken = csrf_token();

        return '<script src="' . $livewireScriptUrl . '" data-csrf="' . $csrfToken . '" data-update-uri="' . $updateUri . '" data-navigate-once="true"></script>';
    }

    /**
     * Get the subfolder path derived from APP_URL
     */
    private function getBasePath(): string
    {
        $appUrl = config('app.url', '');
        $parsed = parse_url($appUrl);
        $path = rtrim($parsed['path'] ?? '', '/');
        return $path;
    }

    /**
     * Get the Livewire script URL using APP_URL base path
     */
    private function getLivewireScriptUrl(): string
    {
        $id = $this->getLivewireAssetId();
        return rtrim(config('app.url'), '/') . "/vendor/livewire/livewire.js?id={$id}";
    }

    /**
     * Get the Livewire update URI using APP_URL base path
     */
    private function getCustomUpdateUri(): string
    {
        $base = $this->getBasePath();
        return $base . '/livewire/update';
    }

    /**
     * Get Livewire asset ID from manifest
     */
    private function getLivewireAssetId(): string
    {
        $manifestPath = public_path('vendor/livewire/manifest.json');

        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
            if (isset($manifest['/livewire.js'])) {
                return $manifest['/livewire.js'];
            }
        }

        return 'f084fdfb';
    }
}
