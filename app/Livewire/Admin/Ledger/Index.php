<?php

namespace App\Livewire\Admin\Ledger;

use App\Enums\StockMovementType;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Index extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $from = '';

    #[Url(history: true)]
    public string $to = '';

    #[Url(history: true)]
    public string $type = '';

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public bool $print = false;

    public function mount(): void
    {
        $this->from = $this->normalizeDateInput(
            $this->from,
            Carbon::now()->startOfMonth()->toDateString(),
        );

        $this->to = $this->normalizeDateInput(
            $this->to,
            Carbon::now()->toDateString(),
        );

        if (
            $this->type !== ''
            && ! in_array(
                $this->type,
                array_column(StockMovementType::cases(), 'value'),
                true,
            )
        ) {
            $this->type = '';
        }
    }


private function normalizeDateInput(string $value, string $fallback): string
    {
        $value = trim($value);

        if ($value === '') {
            return $fallback;
        }

        try {
            $date = Carbon::createFromFormat('!Y-m-d', $value);
        } catch (\Throwable) {
            return $fallback;
        }

        if (! $date instanceof Carbon || $date->format('Y-m-d') !== $value) {
            return $fallback;
        }

        return $date->format('Y-m-d');
    }


private function dateRange(): array
    {
        $from = Carbon::parse(
            $this->normalizeDateInput(
                $this->from,
                Carbon::now()->startOfMonth()->toDateString(),
            ),
        )->startOfDay();

        $to = Carbon::parse(
            $this->normalizeDateInput(
                $this->to,
                Carbon::now()->toDateString(),
            ),
        )->endOfDay();

        if ($from->isAfter($to)) {
            return [$to->startOfDay(), $from->endOfDay()];
        }

        return [$from, $to];
    }

    #[Title('Stock Ledger - StockPilot')]
    public function render(): View
    {
        $user = auth()->user();

        if (! $user) {
            abort(403);
        }

        if (! ($user->isAdmin() || $user->isStockUser())) {
            abort(403);
        }

        [$from, $to] = $this->dateRange();

        $movements = StockMovement::query()
            ->with(['product:id,sku,name', 'createdBy:id,name'])
            ->where('created_at', '>=', $from)
            ->where('created_at', '<=', $to)
            ->when(
                $this->type !== '',
                fn ($query) => $query->where('movement_type', $this->type),
            )
            ->when(
                $this->search !== '',
                fn ($query) => $query->whereHas(
                    'product',
                    fn ($q) => $q
                        ->where('name', 'like', "%{$this->search}%")
                        ->orWhere('sku', 'like', "%{$this->search}%"),
                ),
            )
            ->latest('id')
            ->paginate(15);

        $unitsIn = (int) $movements->getCollection()
            ->where('quantity', '>', 0)
            ->sum('quantity');

        $unitsOut = (int) abs(
            $movements->getCollection()
                ->where('quantity', '<', 0)
                ->sum('quantity'),
        );

        $rows = $this->rows($movements);

        $data = [
            'kpis' => [
                'movements' => $movements->total(),
                'units_in' => $unitsIn,
                'units_out' => $unitsOut,
                'net' => $unitsIn - $unitsOut,
            ],
            'rows' => $rows,
        ];

        $viewData = [
            'data' => $data,
        ];

        if ($this->print) {
            return view('livewire.admin.ledger.print', $viewData)
                ->layout('layouts.print', [
                    'title' => __('Stock Ledger'),
                ]);
        }

        return view('livewire.admin.ledger.index', $viewData);
    }


public function exportCsv(): StreamedResponse
    {
        $user = auth()->user();

        if (! $user || ! ($user->isAdmin() || $user->isStockUser())) {
            abort(403);
        }

        [$from, $to] = $this->dateRange();

        $movements = StockMovement::query()
            ->with(['product:id,sku,name', 'createdBy:id,name'])
            ->where('created_at', '>=', $from)
            ->where('created_at', '<=', $to)
            ->when(
                $this->type !== '',
                fn ($query) => $query->where('movement_type', $this->type),
            )
            ->when(
                $this->search !== '',
                fn ($query) => $query->whereHas(
                    'product',
                    fn ($q) => $q
                        ->where('name', 'like', "%{$this->search}%")
                        ->orWhere('sku', 'like', "%{$this->search}%"),
                ),
            )
            ->latest('id')
            ->get();

        $rows = $this->rows($movements);

        $filename = sprintf(
            'stock-ledger-%s-to-%s.csv',
            $from->toDateString(),
            $to->toDateString(),
        );

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, [
                __('Period'),
                __('SKU'),
                __('Product'),
                __('Type'),
                __('In'),
                __('Out'),
                __('Balance'),
                __('Operator'),
                __('Date'),
            ]);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row['period'],
                    $row['sku'],
                    $row['product'],
                    $row['type'],
                    number_format($row['in'], 2),
                    number_format($row['out'], 2),
                    number_format($row['balance'], 2),
                    $row['operator'],
                    $row['date'],
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }


private function rows(iterable $movements): array
    {
        $result = [];

        foreach ($movements as $movement) {

$product = $movement->product;
            $creator = $movement->createdBy;

            $rawType = $movement->getAttribute('movement_type');

            $type = $rawType instanceof StockMovementType
                ? $rawType->value
                : (string) $rawType;

            $result[] = [
                'period' => (string) $movement->created_at->toDateString(),
                'sku' => (string) ($product === null ? '' : (string) $product->sku),
                'product' => (string) ($product === null ? '' : (string) $product->name),
                'type' => $type,
                'in' => (float) max(0, (float) $movement->quantity),
                'out' => (float) max(0, -1 * (float) $movement->quantity),
                'balance' => (float) $movement->quantity_after,
                'operator' => (string) ($creator === null ? '' : (string) $creator->name),
                'date' => (string) $movement->created_at->toDateTimeString('minute'),
            ];
        }

        return $result;
    }
}
