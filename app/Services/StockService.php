<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Brick\Math\BigDecimal;
use Brick\Math\Exception\MathException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;

class StockService
{

public function increase(
        Product $product,
        string|int $quantity,
        User $actor,
        StockMovementType $movementType = StockMovementType::PURCHASE,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $reason = null,
        ?string $notes = null,
    ): StockMovement {
        $amount = $this->positiveQuantity($quantity);

        return $this->change(
            product: $product,
            signedQuantity: $amount,
            actor: $actor,
            movementType: $movementType,
            referenceType: $referenceType,
            referenceId: $referenceId,
            reason: $reason,
            notes: $notes,
        );
    }


public function decrease(
        Product $product,
        string|int $quantity,
        User $actor,
        StockMovementType $movementType = StockMovementType::SALE,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $reason = null,
        ?string $notes = null,
    ): StockMovement {
        $amount = $this->positiveQuantity($quantity);

        return $this->change(
            product: $product,
            signedQuantity: $amount->negated(),
            actor: $actor,
            movementType: $movementType,
            referenceType: $referenceType,
            referenceId: $referenceId,
            reason: $reason,
            notes: $notes,
        );
    }


public function adjust(
        Product $product,
        string|int $signedQuantity,
        User $actor,
        StockMovementType $movementType = StockMovementType::ADJUSTMENT,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $reason = null,
        ?string $notes = null,
    ): StockMovement {
        $amount = $this->decimalQuantity($signedQuantity);

        if ($amount->isZero()) {
            throw new InvalidArgumentException(
                'Stock adjustment quantity must not be zero.'
            );
        }

        return $this->change(
            product: $product,
            signedQuantity: $amount,
            actor: $actor,
            movementType: $movementType,
            referenceType: $referenceType,
            referenceId: $referenceId,
            reason: $reason,
            notes: $notes,
        );
    }


protected function change(
        Product $product,
        BigDecimal $signedQuantity,
        User $actor,
        StockMovementType $movementType,
        ?string $referenceType,
        ?int $referenceId,
        ?string $reason,
        ?string $notes,
    ): StockMovement {
        if (! $actor->isActive()) {
            throw new LogicException(
                'Inactive users cannot perform stock operations.'
            );
        }

        return DB::transaction(function () use (
            $product,
            $signedQuantity,
            $actor,
            $movementType,
            $referenceType,
            $referenceId,
            $reason,
            $notes
        ): StockMovement {
            $lockedProduct = Product::query()
                ->lockForUpdate()
                ->whereKey($product->getKey())
                ->first();

            if ($lockedProduct === null) {
                throw new LogicException(
                    'The product no longer exists.'
                );
            }

            if (! $lockedProduct->isActive()) {
                throw new LogicException(
                    'Inactive products cannot be used in stock operations.'
                );
            }

            $quantityBefore = $this->decimalQuantity(
                (string) $lockedProduct->quantity
            );

            $quantityAfter = $quantityBefore->plus($signedQuantity);

            if ($quantityAfter->isNegative()) {
                throw new LogicException(
                    'Stock quantity cannot become negative.'
                );
            }

            $quantityAfter = $quantityAfter->toScale(3);

            $lockedProduct->quantity = $quantityAfter->toFloat();
            $lockedProduct->save();

            return $this->persistMovement(
                product: $lockedProduct,
                movementType: $movementType,
                quantity: $signedQuantity,
                quantityBefore: $quantityBefore,
                quantityAfter: $quantityAfter,
                actor: $actor,
                referenceType: $referenceType,
                referenceId: $referenceId,
                reason: $reason,
                notes: $notes,
            );
        });
    }


protected function persistMovement(
        Product $product,
        StockMovementType $movementType,
        BigDecimal $quantity,
        BigDecimal $quantityBefore,
        BigDecimal $quantityAfter,
        User $actor,
        ?string $referenceType,
        ?int $referenceId,
        ?string $reason,
        ?string $notes,
    ): StockMovement {
        return StockMovement::query()->create([
            'product_id' => $product->getKey(),
            'movement_type' => $movementType,
            'quantity' => (string) $quantity->toScale(3),
            'quantity_before' => (string) $quantityBefore->toScale(3),
            'quantity_after' => (string) $quantityAfter->toScale(3),
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'reason' => $reason,
            'notes' => $notes,
            'created_by' => $actor->getKey(),
        ]);
    }


private function positiveQuantity(string|int $quantity): BigDecimal
    {
        $amount = $this->decimalQuantity($quantity);

        if ($amount->isNegative() || $amount->isZero()) {
            throw new InvalidArgumentException(
                'Stock quantity must be greater than zero.'
            );
        }

        return $amount;
    }


private function decimalQuantity(string|int $quantity): BigDecimal
    {
        if (is_int($quantity)) {
            return BigDecimal::of($quantity);
        }

        $value = trim($quantity);

        if ($value === '') {
            throw new InvalidArgumentException(
                'Stock quantity is required.'
            );
        }

        if (! preg_match('/^-?\d+(?:\.\d+)?$/', $value)) {
            throw new InvalidArgumentException(
                'Stock quantity must be a valid decimal number.'
            );
        }

        if (str_contains($value, '.')) {
            $decimalPart = substr(
                $value,
                strpos($value, '.') + 1
            );

            if (strlen($decimalPart) > 3) {
                throw new InvalidArgumentException(
                    'Stock quantity may contain at most three decimal places.'
                );
            }
        }

        return BigDecimal::of($value);
    }
}
