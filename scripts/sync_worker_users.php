<?php

/**
 * Sync providers_workers → app_users and set API passwords for worker login.
 *
 * Usage:
 *   php scripts/sync_worker_users.php
 *   WORKER_DEFAULT_PASSWORD=password123 php scripts/sync_worker_users.php
 *   php scripts/sync_worker_users.php --password=password123 --only-missing
 */

use App\Models\ProviderWorker;
use App\Services\Worker\WorkerAuthService;
use Illuminate\Contracts\Console\Kernel;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$password = getenv('WORKER_DEFAULT_PASSWORD') ?: 'password123';
$onlyMissing = in_array('--only-missing', $argv, true);

foreach ($argv as $arg) {
    if (str_starts_with($arg, '--password=')) {
        $password = substr($arg, strlen('--password='));
    }
}

/** @var WorkerAuthService $authService */
$authService = $app->make(WorkerAuthService::class);

$workers = ProviderWorker::query()->orderBy('createdAt')->get();

if ($workers->isEmpty()) {
    echo "No workers found in providers_workers.\n";
    exit(0);
}

$synced = 0;
$skipped = 0;
$passwordSet = 0;

foreach ($workers as $worker) {
    $doc = $worker->toDocumentArray();
    $email = $doc['email'] ?? null;

    if (! $email) {
        echo "SKIP {$worker->id}: no email in payload\n";
        $skipped++;
        continue;
    }

    $hasPassword = ! empty($doc['password_hash']);

    if ($onlyMissing && $hasPassword) {
        $user = $authService->syncAppUser($worker);
        echo "SYNC (existing password) {$worker->id} {$email} → app_user {$user->id}\n";
        $synced++;
        continue;
    }

    $hash = Illuminate\Support\Facades\Hash::make($password);
    $worker->mergePayload(['password_hash' => $hash]);
    $worker->save();

    $user = $authService->syncAppUser($worker->fresh(), $password);
    $passwordSet++;

    echo "SYNC {$worker->id} {$email} → app_user {$user->id} (password set)\n";
    $synced++;
}

echo "\nDone: synced={$synced}, passwords_set={$passwordSet}, skipped={$skipped}\n";
echo "Default login password: {$password}\n";
echo "Test: WORKER_TEST_EMAIL=<email> WORKER_TEST_PASSWORD={$password} php scripts/test_worker_apis.php\n";
