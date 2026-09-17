<?php

namespace Tests\Integration;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class PaymentConcurrencyTest extends TestCase
{
    use DatabaseMigrations;

    public function test_concurrent_payments_cannot_overpay_an_invoice(): void
    {
        $user = User::factory()->sales()->create();

        $customer = Customer::factory()->create();

        $invoice = Invoice::factory()->create([
            'customer_id' => $customer->id,
            'created_by' => $user->id,
            'subtotal' => '1000.00',
            'discount_amount' => '0.00',
            'tax_rate' => '0.00',
            'tax_amount' => '0.00',
            'total_amount' => '1000.00',
            'status' => 'completed',
            'payment_status' => 'unpaid',
        ]);

        $signalDirectory = storage_path(
            'framework/testing/payment-concurrency'
        );

        if (is_dir($signalDirectory)) {
            $this->removeDirectoryContents($signalDirectory);
        } else {
            mkdir($signalDirectory, 0777, true);
        }

        $workerScript = base_path(
            'tests/Integration/PaymentConcurrencyWorker.php'
        );

        $holder = $this->startWorker(
            script: $workerScript,
            arguments: [
                'holder',
                (string) $invoice->id,
                (string) $user->id,
                '700.00',
                'PAY-CONCURRENT-HOLDER-001',
                $signalDirectory,
            ],
        );

        try {
            $this->waitForSignal(
                $signalDirectory,
                'holder_payment_created',
                5
            );

            $service = $this->startWorker(
                script: $workerScript,
                arguments: [
                    'service',
                    (string) $invoice->id,
                    (string) $user->id,
                    '500.00',
                    'PAY-CONCURRENT-SERVICE-001',
                    $signalDirectory,
                ],
            );

            try {
                $this->waitForSignal(
                    $signalDirectory,
                    'service_started',
                    5
                );

                usleep(500000);

                $this->assertFileDoesNotExist(
                    $signalDirectory
                    .DIRECTORY_SEPARATOR
                    .'service_finished'
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
                    'service_finished',
                    10
                );

                $this->assertFileExists(
                    $signalDirectory
                    .DIRECTORY_SEPARATOR
                    .'service_rejected'
                );

                $this->assertWorkerFinishedSuccessfully($service);
            } finally {
                $this->terminateWorker($service);
            }

            $this->waitForSignal(
                $signalDirectory,
                'holder_finished',
                10
            );

            $this->assertWorkerFinishedSuccessfully($holder);

            $payments = Payment::query()
                ->where('invoice_id', $invoice->id)
                ->orderBy('id')
                ->get();

            $this->assertCount(1, $payments);

            $this->assertSame(
                '700.00',
                (string) $payments->first()->amount
            );

            $this->assertSame(
                'partially_paid',
                $invoice->fresh()->payment_status
            );

            $paidTotal = $payments->sum(
                fn (Payment $payment): float => (float) $payment->amount
            );

            $this->assertSame(
                '700.00',
                number_format($paidTotal, 2, '.', '')
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
                'Unable to start payment concurrency worker.'
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
        $path = $directory.DIRECTORY_SEPARATOR.$signal;
        $deadline = microtime(true) + $timeoutSeconds;

        while (! file_exists($path)) {
            if (microtime(true) >= $deadline) {
                $this->fail(
                    "Timed out waiting for payment concurrency signal: {$signal}"
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
            'Payment concurrency worker did not finish within 10 seconds.'
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
        string $directory,
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
