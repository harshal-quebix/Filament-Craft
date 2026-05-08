<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Artisan;
use RachidLaasri\LaravelInstaller\Helpers\FinalInstallManager;
use Symfony\Component\Console\Output\BufferedOutput;

class PatchedFinalInstallManager extends FinalInstallManager
{
    /**
     * Run final commands, but fix key generation so it works even when
     * a temporary APP_KEY is present in $_ENV from bootstrap.
     */
    public function runFinal()
    {
        $outputLog = new BufferedOutput;

        $this->generateKey($outputLog);
        $this->publishVendorAssets($outputLog);

        return $outputLog->fetch();
    }

    /**
     * Generate / fix application key without relying on the broken key:generate
     * when a temp key is injected in $_ENV.
     */
    private function generateKey(BufferedOutput $outputLog)
    {
        try {
            if (! config('installer.final.key')) {
                return $outputLog;
            }

            $envPath = base_path('.env');
            $content = file_exists($envPath) ? file_get_contents($envPath) : '';

            // If .env has no APP_KEY line, add one
            if (! str_contains($content, 'APP_KEY=')) {
                $content .= "\nAPP_KEY=\n";
            }

            // If APP_KEY is empty in .env, fill it with a real key
            if (preg_match('/^APP_KEY=\s*$/m', $content)) {
                $key = 'base64:'.base64_encode(random_bytes(32));
                $content = preg_replace('/^APP_KEY=\s*$/m', 'APP_KEY='.$key, $content);
                file_put_contents($envPath, $content);
                $outputLog->write('Application key set successfully.', 1);
                return $outputLog;
            }

            // APP_KEY already has a value in .env – just ensure config reflects it
            $outputLog->write('Application key already present.', 1);
        } catch (Exception $e) {
            $outputLog->write('Error setting application key: '.$e->getMessage(), 1);
        }

        return $outputLog;
    }

    /**
     * Publish vendor assets.
     */
    private function publishVendorAssets(BufferedOutput $outputLog)
    {
        try {
            if (config('installer.final.publish')) {
                Artisan::call('vendor:publish', ['--all' => true], $outputLog);
            }
        } catch (Exception $e) {
            $outputLog->write('Vendor publish error: '.$e->getMessage(), 1);
        }

        return $outputLog;
    }
}
