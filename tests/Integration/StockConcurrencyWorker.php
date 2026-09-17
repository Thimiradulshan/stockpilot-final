<?php

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

[$role, $productId, $userId, $signalDirectory] = array_pad(
    array_slice($argv, 1),
    4,
    null
);

if (
    ! is_string($role)
    || ! is_string($productId)
    || ! is_string($userId)
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
        microtime(true),
        LOCK_EX
    );
};

try {
    if ($role === 'lock') {
        $pdo = DB::connection('mysql')->getPdo();

        $pdo->beginTransaction();

        $statement = $pdo->prepare(
            'SELECT id, quantity
             FROM products
             WHERE id = ?
             FOR UPDATE'
        );

        $statement->execute([(int) $productId]);

        $product = $statement->fetch();

        if ($product === false) {
            throw new RuntimeException('Product was not found.');
        }

        if ((string) $product['quantity'] !== '10.000') {
            throw new RuntimeException(
                'Unexpected initial product quantity: '
                .$product['quantity']
            );
        }

        $update = $pdo->prepare(
            'UPDATE products
             SET quantity = ?
             WHERE id = ?'
        );

        $update->execute([
            '15.000',
            (int) $productId,
        ]);

        $movement = $pdo->prepare(
            'INSERT INTO stock_movements (
                product_id,
                movement_type,
                quantity,
                quantity_before,
                quantity_after,
                created_by,
                created_at,
                updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())'
        );

        $movement->execute([
            (int) $productId,
            StockMovementType::ADJUSTMENT->value,
            '5.000',
            '10.000',
            '15.000',
            (int) $userId,
        ]);

        $signal('locked');

        $releasePath = $signalDirectory
            .DIRECTORY_SEPARATOR
            .'release';

        $deadline = microtime(true) + 10;

        while (! file_exists($releasePath)) {
            if (microtime(true) >= $deadline) {
                throw new RuntimeException(
                    'Timed out waiting for release signal.'
                );
            }

            usleep(50000);
        }

        $pdo->commit();

        $signal('lock_finished');

        exit(0);
    }

    if ($role === 'service') {
        $signal('service_started');

        $product = Product::query()->findOrFail((int) $productId);
        $user = User::query()->findOrFail((int) $userId);

        app(StockService::class)->adjust(
            product: $product,
            signedQuantity: '7.000',
            actor: $user,
            movementType: StockMovementType::ADJUSTMENT,
            reason: 'Concurrent adjustment',
        );

        $signal('service_finished');

        exit(0);
    }

    throw new RuntimeException(
        "Unknown worker role: {$role}"
    );
} catch (Throwable $exception) {
    fwrite(
        STDERR,
        $exception::class.': '.$exception->getMessage().PHP_EOL
    );

    try {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
    } catch (Throwable) {

    }

    exit(1);
}
