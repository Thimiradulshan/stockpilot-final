<?php

namespace Tests\Integration;

use App\Enums\StockMovementType;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class MySqlDatabaseTest extends TestCase
{
    use DatabaseMigrations;

    public function test_stock_service_preserves_updates_when_a_product_row_is_locked(): void
    {
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'quantity' => '10.000',
        ]);

        $workerUser = User::factory()->stock()->create();

        $signalDirectory = storage_path('framework/testing/stock-concurrency');

        if (is_dir($signalDirectory)) {
            $this->removeDirectoryContents($signalDirectory);
        } else {
            mkdir($signalDirectory, 0777, true);
        }

        $workerScript = base_path(
            'tests/Integration/StockConcurrencyWorker.php'
        );

        $workerA = $this->startWorker(
            script: $workerScript,
            arguments: [
                'lock',
                (string) $product->id,
                (string) $workerUser->id,
                $signalDirectory,
            ],
        );

        try {
            $this->waitForSignal(
                $signalDirectory,
                'locked',
                5
            );

            $workerB = $this->startWorker(
                script: $workerScript,
                arguments: [
                    'service',
                    (string) $product->id,
                    (string) $workerUser->id,
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
                    $signalDirectory.DIRECTORY_SEPARATOR.'service_finished'
                );

                file_put_contents(
                    $signalDirectory.DIRECTORY_SEPARATOR.'release',
                    'release',
                    LOCK_EX
                );

                $this->waitForSignal(
                    $signalDirectory,
                    'service_finished',
                    10
                );

                $this->assertWorkerFinishedSuccessfully($workerB);
            } finally {
                $this->terminateWorker($workerB);
            }

            $this->waitForSignal(
                $signalDirectory,
                'lock_finished',
                10
            );

            $this->assertWorkerFinishedSuccessfully($workerA);

            $product->refresh();

            $this->assertSame(
                '22.000',
                (string) $product->quantity
            );

            $movements = StockMovement::query()
                ->where('product_id', $product->id)
                ->orderBy('id')
                ->get();

            $this->assertCount(2, $movements);

            $this->assertSame(
                StockMovementType::ADJUSTMENT,
                $movements[0]->movement_type
            );

            $this->assertSame(
                '10.000',
                (string) $movements[0]->quantity_before
            );

            $this->assertSame(
                '15.000',
                (string) $movements[0]->quantity_after
            );

            $this->assertSame(
                '15.000',
                (string) $movements[1]->quantity_before
            );

            $this->assertSame(
                '22.000',
                (string) $movements[1]->quantity_after
            );
        } finally {
            $this->terminateWorker($workerA);
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
            $this->fail('Unable to start MySQL concurrency worker.');
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
                    "Timed out waiting for concurrency signal: {$signal}"
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

        $this->fail('Concurrency worker did not finish within 10 seconds.');
    }

    private function terminateWorker(array $worker): void
    {
        if (! isset($worker['process']) || ! is_resource($worker['process'])) {
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

    private function removeDirectoryContents(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        foreach (scandir($directory) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $path = $directory.DIRECTORY_SEPARATOR.$entry;

            if (is_file($path)) {
                unlink($path);
            }
        }
    }
}
