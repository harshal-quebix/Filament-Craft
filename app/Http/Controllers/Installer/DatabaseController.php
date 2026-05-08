<?php

namespace App\Http\Controllers\Installer;

use RachidLaasri\LaravelInstaller\Controllers\DatabaseController as BaseDatabaseController;
use RachidLaasri\LaravelInstaller\Helpers\DatabaseManager;

class DatabaseController extends BaseDatabaseController
{
    /**
     * @var DatabaseManager
     */
    protected $databaseManager;

    /**
     * @param DatabaseManager $databaseManager
     */
    public function __construct(DatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
    }

    /**
     * Migrate and seed the database, but stop the installation if it fails.
     */
    public function database()
    {
        $response = $this->databaseManager->migrateAndSeed();

        // If migration or seeding failed, do NOT proceed to the final step.
        if (is_array($response) && ($response['status'] ?? '') === 'error') {
            return redirect()
                ->route('LaravelInstaller::environmentWizard')
                ->withErrors([
                    'database_connection' => $response['message'] ?? trans('installer_messages.environment.wizard.form.db_connection_failed'),
                ])
                ->with(['message' => $response]);
        }

        return redirect()
            ->route('LaravelInstaller::final')
            ->with(['message' => $response]);
    }
}
