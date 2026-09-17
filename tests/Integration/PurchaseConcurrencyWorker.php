<?php

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use App\Services\PurchaseService;
use Illuminate\Contracts\Console\Kernel;

[
    $role,
    $supplierId,
    $userId,
    $purchaseNumber,
    $itemsBase64,
    $signalDirectory,
] = array_pad(
    array_slice($argv, 1),
    6,
    null
);

if (
    ! is_string($role)
    || ! is_string($supplierId)
    || ! is_string($userId)
    || ! is_string($purchaseNumber)
    || ! is_string($itemsBase64)
    || ! is_string($signalDirectory)
) {
    fwrite(STDERR, "Invalid worker arguments.\n");
    exit(1);
}

putenv('APP_ENV=testing');
putenv('DB_CONNECTION=mysql');
putenv('DB_HOST=127.0.0.1');
putenv('DB_PORT=3306');
putenv('DB_DATABASE=stockpilot_testing');
putenv('DB_USERNAME=root');
putenv('DB_PASSWORD=');
putenv('CACHE_STORE=array');
putenv('SESSION_DRIVER=array');
putenv('QUEUE_CONNECTION=sync');

require dirname(__DIR__, 2).'/vendor/autoload.php';

$app = require dirname(__DIR__, 2).'/bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

$signal = function (string $name) use ($signalDirectory): void {
    file_put_contents(
        $signalDirectory.DIRECTORY_SEPARATOR.$name,
        (string) microtime(true),
        LOCK_EX
    );
};

try {
    $decoded = base64_decode(
        $itemsBase64,
        true
    );

    if ($decoded === false) {
        throw new RuntimeException(
            'Purchase items payload is not valid Base64.'
        );
    }

    $items = json_decode(
        $decoded,
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    if (! is_array($items) || $items === []) {
        throw new RuntimeException(
            'Purchase items payload must be a non-empty array.'
        );
    }

    $supplier = Supplier::query()->find(
        (int) $supplierId
    );

    if (! $supplier instanceof Supplier) {
        throw new RuntimeException(
            'Supplier was not found.'
        );
    }

    $user = User::query()->find(
        (int) $userId
    );

    if (! $user instanceof User) {
        throw new RuntimeException(
            'User was not found.'
        );
    }

    foreach ($items as $item) {
        if (
            ! is_array($item)
            || ! isset($item['product_id'])
        ) {
            throw new RuntimeException(
                'A worker purchase item is invalid.'
            );
        }

        $product = Product::query()->find(
            (int) $item['product_id']
        );

        if (! $product instanceof Product) {
            throw new RuntimeException(
                'A worker purchase product was not found.'
            );
        }
    }

    if ($role !== 'ready') {
        throw new RuntimeException(
            "Unknown worker role: {$role}"
        );
    }

    $purchaseService = app(PurchaseService::class);

    $readySignal = 'worker_'.$purchaseNumber.'_ready';

    $finishedSignal = 'worker_'.$purchaseNumber.'_finished';

    $releaseSignal = $signalDirectory
        .DIRECTORY_SEPARATOR
        .'release_'.$purchaseNumber;

    $signal($readySignal);

    $deadline = microtime(true) + 15;

    while (! file_exists($releaseSignal)) {
        if (microtime(true) >= $deadline) {
            throw new RuntimeException(
                "Timed out waiting for release signal for {$purchaseNumber}."
            );
        }

        usleep(50000);
    }

    $purchase = $purchaseService->create(
        supplier: $supplier,
        purchaseNumber: $purchaseNumber,
        purchaseDate: '2026-09-10',
        items: $items,
        discountAmount: '0.00',
        taxAmount: '0.00',
        actor: $user,
    );

    if (! $purchase instanceof Purchase) {
        throw new RuntimeException(
            'PurchaseService did not return a Purchase model.'
        );
    }

    $signal($finishedSignal);

    exit(0);
} catch (Throwable $exception) {
    fwrite(
        STDERR,
        $exception::class.': '.$exception->getMessage().PHP_EOL
    );

    exit(1);
}
