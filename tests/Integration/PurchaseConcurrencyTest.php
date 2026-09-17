<?php

namespace Tests\Integration;

use App\Enums\StockMovementType;
use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class PurchaseConcurrencyTest extends TestCase
{
    use DatabaseMigrations;

    public function test_concurrent_purchases_using_the_same_product_are_serialized_safely(): void
    {
        $user = User::factory()->stock()->create();
        $supplier = Supplier::factory()->create();
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Concurrent Purchase Product',
            'cost_price' => '10.00',
            'selling_price' => '15.00',
            'quantity' => '10.000',
            'reorder_level' => '0.000',
            'status' => 'active',
        ]);

        $signalDirectory = storage_path(
            'framework/testing/purchase-concurrency'
        );

        $this->prepareSignalDirectory($signalDirectory);

        $workerScript = base_path(
            'tests/Integration/PurchaseConcurrencyWorker.php'
        );

        $itemsA = base64_encode(
            json_encode([
                [
                    'product_id' => $product->id,
                    'quantity' => '7.000',
                    'unit_cost' => '10.00',
                    'discount_amount' => '0.00',
                    'tax_amount' => '0.00',
                ],
            ], JSON_THROW_ON_ERROR)
        );

        $itemsB = base64_encode(
            json_encode([
                [
                    'product_id' => $product->id,
                    'quantity' => '5.000',
                    'unit_cost' => '10.00',
                    'discount_amount' => '0.00',
                    'tax_amount' => '0.00',
                ],
            ], JSON_THROW_ON_ERROR)
        );

        $workerA = $this->startWorker(
            script: $workerScript,
            arguments: [
                'ready',
                (string) $supplier->id,
                (string) $user->id,
                'PUR-CONCURRENT-001',
                $itemsA,
                $signalDirectory,
            ],
        );

        $workerB = $this->startWorker(
            script: $workerScript,
            arguments: [
                'ready',
                (string) $supplier->id,
                (string) $user->id,
                'PUR-CONCURRENT-002',
                $itemsB,
                $signalDirectory,
            ],
        );

        try {
            $this->waitForSignal(
                $signalDirectory,
                'worker_PUR-CONCURRENT-001_ready',
                10,
                $workerA,
                'PUR-CONCURRENT-001'
            );

            $this->waitForSignal(
                $signalDirectory,
                'worker_PUR-CONCURRENT-002_ready',
                10,
                $workerB,
                'PUR-CONCURRENT-002'
            );

            file_put_contents(
                $signalDirectory
                .DIRECTORY_SEPARATOR
                .'release_PUR-CONCURRENT-001',
                'release',
                LOCK_EX
            );

            file_put_contents(
                $signalDirectory
                .DIRECTORY_SEPARATOR
                .'release_PUR-CONCURRENT-002',
                'release',
                LOCK_EX
            );

            $this->waitForSignal(
                $signalDirectory,
                'worker_PUR-CONCURRENT-001_finished',
                15,
                $workerA,
                'PUR-CONCURRENT-001'
            );

            $this->waitForSignal(
                $signalDirectory,
                'worker_PUR-CONCURRENT-002_finished',
                15,
                $workerB,
                'PUR-CONCURRENT-002'
            );

            $this->assertWorkerFinishedSuccessfully($workerA);
            $this->assertWorkerFinishedSuccessfully($workerB);

            $product->refresh();

            $this->assertSame(
                '22.000',
                (string) $product->quantity
            );

            $purchases = Purchase::query()
                ->whereIn('purchase_number', [
                    'PUR-CONCURRENT-001',
                    'PUR-CONCURRENT-002',
                ])
                ->orderBy('id')
                ->get();

            $this->assertCount(
                2,
                $purchases
            );

            $this->assertSame(
                '120.00',
                number_format(
                    (float) $purchases->sum(
                        fn (Purchase $purchase): string => (string) $purchase->total_amount
                    ),
                    2,
                    '.',
                    ''
                )
            );

            $items = PurchaseItem::query()
                ->whereIn(
                    'purchase_id',
                    $purchases->pluck('id')
                )
                ->orderBy('id')
                ->get();

            $this->assertCount(
                2,
                $items
            );

            $movements = StockMovement::query()
                ->where('product_id', $product->id)
                ->where(
                    'movement_type',
                    StockMovementType::PURCHASE->value
                )
                ->orderBy('id')
                ->get();

            $this->assertCount(
                2,
                $movements
            );

            $this->assertSame(
                '10.000',
                (string) $movements[0]->quantity_before
            );

            $this->assertSame(
                '17.000',
                (string) $movements[0]->quantity_after
            );

            $this->assertSame(
                '17.000',
                (string) $movements[1]->quantity_before
            );

            $this->assertSame(
                '22.000',
                (string) $movements[1]->quantity_after
            );

            $this->assertSame(
                'purchase',
                $movements[0]->reference_type
            );

            $this->assertSame(
                'purchase',
                $movements[1]->reference_type
            );
        } finally {
            $this->terminateWorker($workerA);
            $this->terminateWorker($workerB);
            $this->removeDirectoryContents($signalDirectory);
        }
    }

    public function test_purchase_lock_order_is_consistent_when_items_are_supplied_in_opposite_order(): void
    {
        $user = User::factory()->stock()->create();
        $supplier = Supplier::factory()->create();
        $category = Category::factory()->create();

        $productA = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Concurrent Product A',
            'cost_price' => '10.00',
            'selling_price' => '15.00',
            'quantity' => '10.000',
            'reorder_level' => '0.000',
            'status' => 'active',
        ]);

        $productB = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Concurrent Product B',
            'cost_price' => '20.00',
            'selling_price' => '30.00',
            'quantity' => '20.000',
            'reorder_level' => '0.000',
            'status' => 'active',
        ]);

        $signalDirectory = storage_path(
            'framework/testing/purchase-lock-order'
        );

        $this->prepareSignalDirectory($signalDirectory);

        $workerScript = base_path(
            'tests/Integration/PurchaseConcurrencyWorker.php'
        );

        $itemsAscending = base64_encode(
            json_encode([
                [
                    'product_id' => $productA->id,
                    'quantity' => '2.000',
                    'unit_cost' => '10.00',
                    'discount_amount' => '0.00',
                    'tax_amount' => '0.00',
                ],
                [
                    'product_id' => $productB->id,
                    'quantity' => '3.000',
                    'unit_cost' => '20.00',
                    'discount_amount' => '0.00',
                    'tax_amount' => '0.00',
                ],
            ], JSON_THROW_ON_ERROR)
        );

        $itemsDescending = base64_encode(
            json_encode([
                [
                    'product_id' => $productB->id,
                    'quantity' => '4.000',
                    'unit_cost' => '20.00',
                    'discount_amount' => '0.00',
                    'tax_amount' => '0.00',
                ],
                [
                    'product_id' => $productA->id,
                    'quantity' => '1.000',
                    'unit_cost' => '10.00',
                    'discount_amount' => '0.00',
                    'tax_amount' => '0.00',
                ],
            ], JSON_THROW_ON_ERROR)
        );

        $workerA = $this->startWorker(
            script: $workerScript,
            arguments: [
                'ready',
                (string) $supplier->id,
                (string) $user->id,
                'PUR-LOCKORDER-001',
                $itemsAscending,
                $signalDirectory,
            ],
        );

        $workerB = $this->startWorker(
            script: $workerScript,
            arguments: [
                'ready',
                (string) $supplier->id,
                (string) $user->id,
                'PUR-LOCKORDER-002',
                $itemsDescending,
                $signalDirectory,
            ],
        );

        try {
            $this->waitForSignal(
                $signalDirectory,
                'worker_PUR-LOCKORDER-001_ready',
                10,
                $workerA,
                'PUR-LOCKORDER-001'
            );

            $this->waitForSignal(
                $signalDirectory,
                'worker_PUR-LOCKORDER-002_ready',
                10,
                $workerB,
                'PUR-LOCKORDER-002'
            );

            file_put_contents(
                $signalDirectory
                .DIRECTORY_SEPARATOR
                .'release_PUR-LOCKORDER-001',
                'release',
                LOCK_EX
            );

            file_put_contents(
                $signalDirectory
                .DIRECTORY_SEPARATOR
                .'release_PUR-LOCKORDER-002',
                'release',
                LOCK_EX
            );

            $this->waitForSignal(
                $signalDirectory,
                'worker_PUR-LOCKORDER-001_finished',
                15,
                $workerA,
                'PUR-LOCKORDER-001'
            );

            $this->waitForSignal(
                $signalDirectory,
                'worker_PUR-LOCKORDER-002_finished',
                15,
                $workerB,
                'PUR-LOCKORDER-002'
            );

            $this->assertWorkerFinishedSuccessfully($workerA);
            $this->assertWorkerFinishedSuccessfully($workerB);

            $productA->refresh();
            $productB->refresh();

            $this->assertSame(
                '13.000',
                (string) $productA->quantity
            );

            $this->assertSame(
                '27.000',
                (string) $productB->quantity
            );

            $purchases = Purchase::query()
                ->whereIn('purchase_number', [
                    'PUR-LOCKORDER-001',
                    'PUR-LOCKORDER-002',
                ])
                ->get();

            $this->assertCount(
                2,
                $purchases
            );

            $this->assertSame(
                4,
                PurchaseItem::query()
                    ->whereIn(
                        'purchase_id',
                        $purchases->pluck('id')
                    )
                    ->count()
            );
        } finally {
            $this->terminateWorker($workerA);
            $this->terminateWorker($workerB);
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
                'Unable to start purchase concurrency worker.'
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
        array $worker,
        string $workerName,
    ): void {
        $path = $directory.DIRECTORY_SEPARATOR.$signal;
        $deadline = microtime(true) + $timeoutSeconds;

        while (! file_exists($path)) {
            $status = proc_get_status($worker['process']);

            if (! $status['running']) {
                $stdout = stream_get_contents($worker['stdout']);
                $stderr = stream_get_contents($worker['stderr']);

                $this->fail(
                    trim(
                        "Worker {$workerName} exited before signal "
                        ."'{$signal}'.\n"
                        ."Exit code: {$status['exitcode']}\n"
                        ."STDOUT:\n{$stdout}\n"
                        ."STDERR:\n{$stderr}"
                    )
                );
            }

            if (microtime(true) >= $deadline) {
                $this->fail(
                    'Timed out waiting for purchase concurrency signal: '
                    .$signal
                );
            }

            usleep(50000);
        }
    }

    private function assertWorkerFinishedSuccessfully(
        array $worker,
    ): void {
        $deadline = microtime(true) + 15;

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
            'Purchase concurrency worker did not finish within 15 seconds.'
        );
    }

    private function prepareSignalDirectory(
        string $directory,
    ): void {
        if (is_dir($directory)) {
            $this->removeDirectoryContents($directory);

            return;
        }

        mkdir($directory, 0777, true);
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
