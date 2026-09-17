<?php

use App\Models\Payment;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

[$role, $invoiceId, $userId, $idempotencyKey, $signalDirectory] = array_pad(
    array_slice($argv, 1),
    5,
    null
);

if (
    ! is_string($role)
    || ! is_string($invoiceId)
    || ! is_string($userId)
    || ! is_string($idempotencyKey)
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

$createPayment = function () use (
    $invoiceId,
    $userId,
    $idempotencyKey
): Payment {
    return Payment::query()->create([
        'invoice_id' => (int) $invoiceId,
        'payment_date' => now(),
        'amount' => '100.00',
        'payment_method' => 'cash',
        'reference' => null,
        'idempotency_key' => $idempotencyKey,
        'notes' => 'Concurrency idempotency test',
        'received_by' => (int) $userId,
    ]);
};

try {
    if ($role === 'holder') {
        DB::beginTransaction();

        $createPayment();

        $signal('holder_inserted');

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

        DB::commit();

        $signal('holder_finished');

        exit(0);
    }

    if ($role === 'duplicate') {
        $signal('duplicate_started');

        try {
            $createPayment();

            throw new RuntimeException(
                'Duplicate payment was unexpectedly inserted.'
            );
        } catch (UniqueConstraintViolationException) {
            $signal('duplicate_rejected');

            $signal('duplicate_finished');

            exit(0);
        }
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
        if (DB::transactionLevel() > 0) {
            DB::rollBack();
        }
    } catch (Throwable) {

    }

    exit(1);
}
