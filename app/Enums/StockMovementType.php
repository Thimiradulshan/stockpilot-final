<?php

namespace App\Enums;

enum StockMovementType: string
{
    case PURCHASE = 'purchase';
    case SALE = 'sale';
    case ADJUSTMENT = 'adjustment';
    case RETURN = 'return';
    case CORRECTION = 'correction';
}
