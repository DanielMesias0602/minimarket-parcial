<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Venta;
use PDO;

/**
 * Ejecuta consultas SQL relacionadas con ventas.
 */
final class VentaRepository
{
    /**
     * Recibe la conexión compartida por los repositorios de la aplicación.
     *
     * @param PDO $pdo Conexión PDO configurada para lanzar excepciones.
     */
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * Inserta una venta y devuelve el identificador generado.
     *
     * @param Venta $venta Venta que se insertará.
     * @return int Identificador autoincremental de la venta.
     */
    public function guardar(Venta $venta): int
    {
        $consulta = $this->pdo->prepare(
            'INSERT INTO venta (id_producto, cantidad, precio_unitario, total)
             VALUES (:id_producto, :cantidad, :precio_unitario, :total)'
        );
        $consulta->execute([
            'id_producto' => $venta->idProducto,
            'cantidad' => $venta->cantidad,
            'precio_unitario' => $venta->precioUnitario,
            'total' => $venta->total,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Devuelve las ventas cuya fecha coincide con la fecha indicada.
     *
     * @param string $fecha Fecha en formato AAAA-MM-DD.
     * @return list<Venta> Ventas encontradas; una lista vacía si no hay registros.
     */
    public function listarPorFecha(string $fecha): array
    {
        $consulta = $this->pdo->prepare(
            'SELECT id_venta, id_producto, cantidad, precio_unitario, total, fecha_hora
             FROM venta
             WHERE DATE(fecha_hora) = :fecha
             ORDER BY fecha_hora, id_venta'
        );
        $consulta->execute(['fecha' => $fecha]);
        $filas = $consulta->fetchAll();
        $ventas = [];

        foreach ($filas as $fila) {
            $ventas[] = $this->crearVenta($fila);
        }

        return $ventas;
    }

    /**
     * Suma los importes de las ventas registradas para una fecha.
     *
     * @param string $fecha Fecha en formato AAAA-MM-DD.
     * @return string Total decimal del día, o "0.00" cuando no hay ventas.
     */
    public function totalDelDia(string $fecha): string
    {
        $consulta = $this->pdo->prepare(
            'SELECT COALESCE(SUM(total), 0.00)
             FROM venta
             WHERE DATE(fecha_hora) = :fecha'
        );
        $consulta->execute(['fecha' => $fecha]);
        $total = $consulta->fetchColumn();

        return (string) $total;
    }

    /**
     * Convierte una fila asociativa de PDO en una entidad Venta.
     *
     * @param array<string, mixed> $fila Fila devuelta por PDO.
     * @return Venta Venta representada por la fila.
     */
    private function crearVenta(array $fila): Venta
    {
        return new Venta(
            idProducto: (int) $fila['id_producto'],
            cantidad: (int) $fila['cantidad'],
            precioUnitario: (string) $fila['precio_unitario'],
            total: (string) $fila['total'],
            idVenta: (int) $fila['id_venta'],
            fechaHora: (string) $fila['fecha_hora']
        );
    }
}
