<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;

class DeployController extends Controller
{
    public function run(Request $request): JsonResponse
    {
        $secret = config('app.deploy_secret');

        if (! $secret || $request->header('X-Deploy-Token') !== $secret) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $log = [];

        // 1. Composer install
        $composer = new Process(
            ['composer', 'install', '--no-interaction', '--prefer-dist', '--optimize-autoloader', '--no-dev'],
            base_path(),
            ['COMPOSER_HOME' => sys_get_temp_dir() . '/.composer']
        );
        $composer->setTimeout(300);
        $composer->run();
        $log['composer install'] = $composer->isSuccessful()
            ? trim($composer->getOutput()) ?: 'ok'
            : 'ERRO: ' . trim($composer->getErrorOutput());

        if (! $composer->isSuccessful()) {
            return response()->json(['status' => 'error', 'log' => $log], 500);
        }

        // 2. Artisan commands
        $artisan = [
            'migrate'      => ['--force' => true],
            'config:cache' => [],
            'route:cache'  => [],
            'view:cache'   => [],
            'optimize'     => [],
        ];

        foreach ($artisan as $command => $params) {
            Artisan::call($command, $params);
            $log[$command] = trim(Artisan::output()) ?: 'ok';
        }

        return response()->json(['status' => 'ok', 'log' => $log]);
    }
}
