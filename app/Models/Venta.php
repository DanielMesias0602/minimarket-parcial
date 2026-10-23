<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Representa una venta de un producto y sus valores persistidos.
 */
final class Venta
{
    /**
     * Crea una representación de venta sin aplicar reglas de negocio.
     *
     * @param int $idProducto Identificador del producto vendido.
     * @param int $cantidad Cantidad de unidades registradas en la venta.
     * @param string $precioUnitario Precio decimal unitario al momento de la venta.
     * @param string $total Importe decimal total de la venta.
     * @param int|null $idVenta Identificador persistido; null mientras la venta sea nueva.
     * @param string|null $fechaHora Fecha y hora asignadas al persistir; null mientras la venta sea nueva.
     */
    public function __construct(
        public int $idProducto,
        public int $cantidad,
        public string $precioUnitario,
        public string $total,
        public ?int $idVenta = null,
        public ?string $fechaHora = null
    ) {
    }
}
