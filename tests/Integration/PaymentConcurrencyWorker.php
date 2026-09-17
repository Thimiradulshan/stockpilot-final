<?php

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

[$role, $invoiceId, $userId, $amount, $idempotencyKey, $signalDirectory] = array_pad(
    array_slice($argv, 1),
    6,
    null
);

if (
    ! is_string($role)
    || ! is_string($invoiceId)
    || ! is_string($userId)
    || ! is_string($amount)
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

try {
    if ($role === 'holder') {
        DB::beginTransaction();

        $statement = DB::connection('mysql')->getPdo()->prepare(
            'SELECT id
             FROM invoices
             WHERE id = ?
             FOR UPDATE'
        );

        $statement->execute([
            (int) $invoiceId,
        ]);

        if ($statement->fetch() === false) {
            throw new RuntimeException(
                'Invoice was not found.'
            );
        }

        $user = User::query()->findOrFail(
            (int) $userId
        );

        $payment = Payment::query()->create([
            'invoice_id' => (int) $invoiceId,
            'payment_date' => now(),
            'amount' => $amount,
            'payment_method' => 'cash',
            'reference' => null,
            'idempotency_key' => $idempotencyKey,
            'notes' => 'Concurrency payment test',
            'received_by' => $user->id,
        ]);

        $signal('holder_payment_created');

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

        $invoice = Invoice::query()->lockForUpdate()->findOrFail(
            (int) $invoiceId
        );

        $invoice->payment_status = 'partially_paid';
        $invoice->save();

        DB::commit();

        $signal('holder_finished');

        exit(0);
    }

    if ($role === 'service') {
        $signal('service_started');

        $invoice = Invoice::query()->findOrFail(
            (int) $invoiceId
        );

        $user = User::query()->findOrFail(
            (int) $userId
        );

        try {
            app(PaymentService::class)->create(
                invoice: $invoice,
                amount: $amount,
                paymentMethod: 'cash',
                idempotencyKey: $idempotencyKey,
                actor: $user,
            );

            throw new RuntimeException(
                'The concurrent payment unexpectedly succeeded.'
            );
        } catch (Throwable $exception) {
            if (
                $exception instanceof LogicException
                && $exception->getMessage()
                    === 'Payment amount exceeds the outstanding invoice balance.'
            ) {
                $signal('service_rejected');
                $signal('service_finished');

                exit(0);
            }

            throw $exception;
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
