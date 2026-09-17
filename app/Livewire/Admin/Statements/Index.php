<?php

namespace App\Livewire\Admin\Statements;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    #[Url(history: true)]
    public string $customer = '';

    #[Url(as: 'from', history: true)]
    public string $from = '';

    #[Url(as: 'to', history: true)]
    public string $to = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Invoice::class);

        if ($this->from === '') {
            $this->from = today()->startOfMonth()->toDateString();
        }

        if ($this->to === '') {
            $this->to = today()->toDateString();
        }

        $this->customer = $this->normalizeId($this->customer);
        $this->from = $this->normalizeDateInput($this->from, today()->startOfMonth()->toDateString());
        $this->to = $this->normalizeDateInput($this->to, today()->toDateString());
    }


private function normalizeId(string $value): string
    {
        $value = trim($value);

        if ($value === '' || ! ctype_digit($value)) {
            return '';
        }

        return $value;
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
        $from = $this->normalizeDateInput($this->from, today()->startOfMonth()->toDateString());
        $to = $this->normalizeDateInput($this->to, today()->toDateString());

        if ($from > $to) {
            return [$to, $from];
        }

        return [$from, $to];
    }


public function customers(): Collection
    {
        return Customer::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'phone', 'email']);
    }


public function statementData(): array
    {
        $customerId = $this->normalizeId($this->customer);

        if ($customerId === '') {
            return [
                'selected' => false,
                'customer' => null,
                'sales_total' => 0.0,
                'paid_total' => 0.0,
                'closing_balance' => 0.0,
                'invoice_count' => 0,
                'rows' => [],
            ];
        }

        $customer = Customer::query()
            ->whereKey($customerId)
            ->first();

        if ($customer === null || ! $customer->isActive()) {
            return [
                'selected' => false,
                'customer' => null,
                'sales_total' => 0.0,
                'paid_total' => 0.0,
                'closing_balance' => 0.0,
                'invoice_count' => 0,
                'rows' => [],
            ];
        }

        [$from, $to] = $this->dateRange();

        $invoices = Invoice::query()
            ->where('status', 'completed')
            ->where('customer_id', $customer->getKey())
            ->whereBetween('invoice_date', [$from, $to])
            ->get(['id', 'invoice_number', 'invoice_date', 'total_amount']);

        $payments = Payment::query()
            ->with('invoice:id,invoice_number')
            ->whereHas(
                'invoice',
                fn ($query) => $query
                    ->where('customer_id', $customer->getKey())
                    ->where('status', 'completed'),
            )
            ->whereBetween('payment_date', [
                Carbon::parse($from)->startOfDay(),
                Carbon::parse($to)->endOfDay(),
            ])
            ->get(['id', 'invoice_id', 'payment_date', 'amount']);

        $rows = [];

        foreach ($invoices as $invoice) {
            $rows[] = [
                'sort' => Carbon::parse($invoice->invoice_date)->timestamp,
                'date' => Carbon::parse($invoice->invoice_date)->toDateString(),
                'invoice_number' => (string) $invoice->invoice_number,
                'sales_amount' => (float) $invoice->total_amount,
                'payment_amount' => 0.0,
                'type' => 'invoice',
            ];
        }

        foreach ($payments as $payment) {
            $invoiceNumber = $payment->invoice?->invoice_number;

            $rows[] = [
                'sort' => Carbon::parse($payment->payment_date)->timestamp,
                'date' => Carbon::parse($payment->payment_date)->toDateString(),
                'invoice_number' => (string) ($invoiceNumber ?? '—'),
                'sales_amount' => 0.0,
                'payment_amount' => (float) $payment->amount,
                'type' => 'payment',
            ];
        }

        usort(
            $rows,
            fn (array $left, array $right): int => [$left['sort'], $left['type'] === 'invoice' ? 0 : 1]
                <=> [$right['sort'], $right['type'] === 'invoice' ? 0 : 1],
        );

        $runningBalance = 0.0;
        $salesTotal = 0.0;
        $paidTotal = 0.0;
        $invoiceCount = 0;

        foreach ($rows as &$row) {
            $runningBalance = round($runningBalance + $row['sales_amount'] - $row['payment_amount'], 2);

            $row['outstanding_balance'] = $runningBalance;

            $salesTotal += $row['sales_amount'];
            $paidTotal += $row['payment_amount'];

            if ($row['type'] === 'invoice') {
                $invoiceCount++;
            }
        }

        unset($row);

        return [
            'selected' => true,
            'customer' => $customer,
            'sales_total' => round($salesTotal, 2),
            'paid_total' => round($paidTotal, 2),
            'closing_balance' => $runningBalance,
            'invoice_count' => $invoiceCount,
            'rows' => $rows,
        ];
    }

    #[Title('Customer Statement - StockPilot')]
    public function render(): View
    {
        $data = $this->statementData();

        return view('livewire.admin.statements.index', [
            'data' => $data,
            'customers' => $this->customers(),
        ]);
    }
}
