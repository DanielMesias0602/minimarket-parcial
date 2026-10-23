<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Representa el intento de registrar un producto con un código ya existente.
 */
final class DuplicateProductCodeException extends AppException
{
}
