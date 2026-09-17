<?php

namespace Tests\Integration;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class PaymentIdempotencyTest extends TestCase
{
    use DatabaseMigrations;

    public function test_duplicate_idempotency_key_cannot_create_second_payment(): void
    {
        $user = User::factory()->sales()->create();

        $customer = Customer::factory()->create();

        $invoice = Invoice::factory()->create([
            'customer_id' => $customer->id,
            'created_by' => $user->id,
            'total_amount' => '1000.00',
        ]);

        $idempotencyKey = 'PAYMENT-DUPLICATE-TEST-001';

        Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'received_by' => $user->id,
            'idempotency_key' => $idempotencyKey,
        ]);

        $this->expectException(
            UniqueConstraintViolationException::class
        );

        Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'received_by' => $user->id,
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    public function test_concurrent_duplicate_payment_is_rejected_by_database_unique_constraint(): void
    {
        $user = User::factory()->sales()->create();

        $customer = Customer::factory()->create();

        $invoice = Invoice::factory()->create([
            'customer_id' => $customer->id,
            'created_by' => $user->id,
            'total_amount' => '1000.00',
        ]);

        $idempotencyKey = 'PAYMENT-CONCURRENT-TEST-001';

        $signalDirectory = storage_path(
            'framework/testing/payment-idempotency'
        );

        if (is_dir($signalDirectory)) {
            $this->removeDirectoryContents($signalDirectory);
        } else {
            mkdir($signalDirectory, 0777, true);
        }

        $workerScript = base_path(
            'tests/Integration/PaymentIdempotencyWorker.php'
        );

        $holder = $this->startWorker(
            script: $workerScript,
            arguments: [
                'holder',
                (string) $invoice->id,
                (string) $user->id,
                $idempotencyKey,
                $signalDirectory,
            ],
        );

        try {
            $this->waitForSignal(
                $signalDirectory,
                'holder_inserted',
                5
            );

            $duplicate = $this->startWorker(
                script: $workerScript,
                arguments: [
                    'duplicate',
                    (string) $invoice->id,
                    (string) $user->id,
                    $idempotencyKey,
                    $signalDirectory,
                ],
            );

            try {
                $this->waitForSignal(
                    $signalDirectory,
                    'duplicate_started',
                    5
                );

                usleep(500000);

                $this->assertFileDoesNotExist(
                    $signalDirectory
                    .DIRECTORY_SEPARATOR
                    .'duplicate_finished'
                );

                file_put_contents(
                    $signalDirectory
                    .DIRECTORY_SEPARATOR
                    .'release',
                    'release',
                    LOCK_EX
                );

                $this->waitForSignal(
                    $signalDirectory,
                    'duplicate_finished',
                    10
                );

                $this->assertWorkerFinishedSuccessfully($duplicate);

                $this->assertFileExists(
                    $signalDirectory
                    .DIRECTORY_SEPARATOR
                    .'duplicate_rejected'
                );
            } finally {
                $this->terminateWorker($duplicate);
            }

            $this->waitForSignal(
                $signalDirectory,
                'holder_finished',
                10
            );

            $this->assertWorkerFinishedSuccessfully($holder);

            $this->assertSame(
                1,
                Payment::query()
                    ->where('idempotency_key', $idempotencyKey)
                    ->count()
            );

            $payment = Payment::query()
                ->where('idempotency_key', $idempotencyKey)
                ->firstOrFail();

            $this->assertSame(
                $invoice->id,
                $payment->invoice_id
            );

            $this->assertSame(
                '100.00',
                (string) $payment->amount
            );

            $this->assertSame(
                $user->id,
                $payment->received_by
            );
        } finally {
            $this->terminateWorker($holder);
            $this->removeDirectoryContents($signalDirectory);
        }
    }

    private function startWorker(
        string $script,
        array $arguments,
    ): mixed {
        $command = escapeshellarg(PHP_BINARY)
            .' '.escapeshellarg($script);

        foreach ($arguments as $argument) {
            $command .= ' '.escapeshellarg($argument);
        }

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open(
            $command,
            $descriptors,
            $pipes,
            base_path(),
        );

        if (! is_resource($process)) {
            $this->fail(
                'Unable to start payment idempotency worker.'
            );
        }

        fclose($pipes[0]);

        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        return [
            'process' => $process,
            'stdout' => $pipes[1],
            'stderr' => $pipes[2],
        ];
    }

    private function waitForSignal(
        string $directory,
        string $signal,
        int $timeoutSeconds,
    ): void {
        $path = $directory
            .DIRECTORY_SEPARATOR
            .$signal;

        $deadline = microtime(true) + $timeoutSeconds;

        while (! file_exists($path)) {
            if (microtime(true) >= $deadline) {
                $this->fail(
                    "Timed out waiting for payment idempotency signal: {$signal}"
                );
            }

            usleep(50000);
        }
    }

    private function assertWorkerFinishedSuccessfully(
        array $worker,
    ): void {
        $deadline = microtime(true) + 10;

        do {
            $status = proc_get_status($worker['process']);

            if (! $status['running']) {
                $exitCode = $status['exitcode'];

                $stdout = stream_get_contents($worker['stdout']);
                $stderr = stream_get_contents($worker['stderr']);

                fclose($worker['stdout']);
                fclose($worker['stderr']);

                $this->assertSame(
                    0,
                    $exitCode,
                    trim(
                        "Worker failed.\nSTDOUT:\n{$stdout}\nSTDERR:\n{$stderr}"
                    )
                );

                proc_close($worker['process']);

                return;
            }

            usleep(50000);
        } while (microtime(true) < $deadline);

        $this->fail(
            'Payment idempotency worker did not finish within 10 seconds.'
        );
    }

    private function terminateWorker(array $worker): void
    {
        if (
            ! isset($worker['process'])
            || ! is_resource($worker['process'])
        ) {
            return;
        }

        $status = proc_get_status($worker['process']);

        if ($status['running']) {
            proc_terminate($worker['process']);
        }

        if (is_resource($worker['stdout'])) {
            fclose($worker['stdout']);
        }

        if (is_resource($worker['stderr'])) {
            fclose($worker['stderr']);
        }

        proc_close($worker['process']);
    }

    private function removeDirectoryContents(
        string $directory
    ): void {
        if (! is_dir($directory)) {
            return;
        }

        foreach (scandir($directory) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $path = $directory
                .DIRECTORY_SEPARATOR
                .$entry;

            if (is_file($path)) {
                unlink($path);
            }
        }
    }
}
