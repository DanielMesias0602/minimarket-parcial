<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Representa un producto y sus valores almacenados en la base de datos.
 */
final class Producto
{
    /**
     * Crea una representación de producto sin aplicar reglas de negocio.
     *
     * @param string $codigo Código único del producto.
     * @param string $nombre Nombre descriptivo del producto.
     * @param string $categoria Categoría a la que pertenece el producto.
     * @param string $precioVenta Precio decimal conservado como texto para evitar redondeo binario.
     * @param int $stockActual Unidades disponibles del producto.
     * @param int $stockMinimo Umbral de stock para mostrar la alerta.
     * @param int|null $idProducto Identificador persistido; null mientras el producto sea nuevo.
     */
    public function __construct(
        public string $codigo,
        public string $nombre,
        public string $categoria,
        public string $precioVenta,
        public int $stockActual,
        public int $stockMinimo,
        public ?int $idProducto = null
    ) {
    }
}
