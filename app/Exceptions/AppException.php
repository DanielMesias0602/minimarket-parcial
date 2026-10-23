<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Excepción base para errores controlados de la aplicación.
 */
class AppException extends RuntimeException
{
    /**
     * Crea una excepción de aplicación con un mensaje seguro para su manejo.
     *
     * @param string $mensaje Descripción del error controlado.
     * @param int $codigo Código opcional de la excepción.
     * @param \Throwable|null $anterior Excepción original, si existe.
     */
    public function __construct(string $mensaje, int $codigo = 0, ?\Throwable $anterior = null)
    {
        parent::__construct($mensaje, $codigo, $anterior);
    }
}
