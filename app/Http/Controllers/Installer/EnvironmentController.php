<?php

namespace App\Http\Controllers\Installer;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RachidLaasri\LaravelInstaller\Controllers\EnvironmentController as BaseEnvironmentController;
use RachidLaasri\LaravelInstaller\Events\EnvironmentSaved;

class EnvironmentController extends BaseEnvironmentController
{
    /**
     * Override saveWizard to use a real database-connection check.
     * The parent only calls getPdo() which can succeed without authenticating.
     */
    public function saveWizard(Request $request, Redirector $redirect)
    {
        $rules = config('installer.environment.form.rules');
        $messages = [
            'environment_custom.required_if' => trans('installer_messages.environment.wizard.form.name_required'),
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return $redirect->route('LaravelInstaller::environmentWizard')->withInput()->withErrors($validator->errors());
        }

        if (! $this->checkDatabaseConnection($request)) {
            return $redirect->route('LaravelInstaller::environmentWizard')->withInput()->withErrors([
                'database_connection' => trans('installer_messages.environment.wizard.form.db_connection_failed'),
            ]);
        }

        $results = $this->EnvironmentManager->saveFileWizard($request);

        event(new EnvironmentSaved($request));

        return $redirect->route('LaravelInstaller::database')
                        ->with(['results' => $results]);
    }

    /**
     * Validate database connection with user credentials by running a real query.
     */
    private function checkDatabaseConnection(Request $request)
    {
        $connection = $request->input('database_connection');
        $settings = config("database.connections.$connection");

        config([
            'database' => [
                'default' => $connection,
                'connections' => [
                    $connection => array_merge($settings, [
                        'driver'   => $connection,
                        'host'     => $request->input('database_hostname'),
                        'port'     => $request->input('database_port'),
                        'database' => $request->input('database_name'),
                        'username' => $request->input('database_username'),
                        'password' => $request->input('database_password'),
                    ]),
                ],
            ],
        ]);

        DB::purge();

        try {
            // Actually execute a query to force real authentication
            DB::connection()->select('SELECT 1');

            return true;
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error('Database connection test failed: ' . $e->getMessage());
            return false;
        }
    }
}
