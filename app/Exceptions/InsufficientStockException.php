<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Representa una venta cuya cantidad supera el stock disponible.
 */
final class InsufficientStockException extends AppException
{
}
