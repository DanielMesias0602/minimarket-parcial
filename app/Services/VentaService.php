<?php

declare(strict_types=1);

namespace App\Services;

use App\Config\Database;
use App\Exceptions\ProductNotFoundException;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\ValidationException;
use App\Models\Venta;
use App\Repositories\ProductoRepository;
use App\Repositories\VentaRepository;
use PDO;
use RuntimeException;
use Throwable;

/**
 * Aplica las reglas de negocio de las ventas y coordina el descuento de stock.
 */
final class VentaService
{
    /**
     * Recibe repositorios que deben compartir la conexión usada en la transacción.
     *
     * @param PDO $pdo Conexión PDO común para ambas operaciones de escritura.
     * @param ProductoRepository $productos Repositorio para consultar y descontar stock.
     * @param VentaRepository $ventas Repositorio para guardar y consultar ventas.
     */
    public function __construct(
        private PDO $pdo,
        private ProductoRepository $productos,
        private VentaRepository $ventas
    ) {
    }

    /**
     * Registra la venta y descuenta el stock dentro de una única transacción.
     *
     * @param int $idProducto Identificador del producto vendido.
     * @param int $cantidad Unidades que se venderán.
     * @return Venta Venta persistida con su identificador y fecha/hora.
     *
     * @throws ValidationException Si el identificador o la cantidad no son válidos.
     * @throws ProductNotFoundException Si el producto no existe.
     * @throws InsufficientStockException Si el stock disponible no alcanza.
     */
    public function registrarVenta(int $idProducto, int $cantidad): Venta
    {
        if ($idProducto <= 0) {
            throw new ValidationException('El producto seleccionado no es válido.');
        }

        if ($cantidad <= 0) {
            throw new ValidationException('La cantidad debe ser mayor a cero.');
        }

        if ($this->pdo->inTransaction()) {
            throw new RuntimeException('No se puede iniciar una venta dentro de otra transacción.');
        }

        $this->pdo->beginTransaction();

        try {
            $producto = $this->productos->obtenerPorIdParaActualizar($idProducto);

            if ($producto === null) {
                throw new ProductNotFoundException('El producto seleccionado no existe.');
            }

            if ($cantidad > $producto->stockActual) {
                throw new InsufficientStockException(
                    sprintf('Stock insuficiente para el producto %s.', $producto->nombre)
                );
            }

            $total = $this->calcularTotal($producto->precioVenta, $cantidad);
            $venta = new Venta(
                idProducto: $idProducto,
                cantidad: $cantidad,
                precioUnitario: $producto->precioVenta,
                total: $total
            );
            $idVenta = $this->ventas->guardar($venta);
            $stockRestante = $producto->stockActual - $cantidad;

            if (!$this->productos->actualizarStock($idProducto, $stockRestante)) {
                throw new ProductNotFoundException('El producto seleccionado ya no existe.');
            }

            $this->pdo->commit();

            return new Venta(
                idProducto: $idProducto,
                cantidad: $cantidad,
                precioUnitario: $producto->precioVenta,
                total: $total,
                idVenta: $idVenta,
                fechaHora: null
            );
        } catch (Throwable $error) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $error;
        }
    }

    /**
     * Obtiene las ventas de hoy y el importe acumulado.
     *
     * @return array{ventas: list<Venta>, total: string} Ventas y total decimal del día.
     */
    public function listarVentasDelDia(): array
    {
        $fecha = date('Y-m-d');

        return [
            'ventas' => $this->ventas->listarPorFecha($fecha),
            'total' => $this->ventas->totalDelDia($fecha),
        ];
    }

    /**
     * Multiplica un precio decimal por la cantidad sin usar aritmética float.
     *
     * @param string $precio Precio unitario con hasta dos decimales.
     * @param int $cantidad Cantidad positiva de unidades.
     * @return string Total con exactamente dos decimales.
     */
    private function calcularTotal(string $precio, int $cantidad): string
    {
        if (preg_match('/^(\d{1,8})\.(\d{2})$/', $precio, $partes) !== 1) {
            throw new RuntimeException('El precio almacenado tiene un formato decimal no válido.');
        }

        $centavos = ((int) $partes[1] * 100) + (int) $partes[2];
        $maximoCentavos = 9_999_999_999;

        if ($centavos > intdiv($maximoCentavos, $cantidad)) {
            throw new ValidationException('El total de la venta excede el límite permitido.');
        }

        $totalCentavos = $centavos * $cantidad;
        $unidades = intdiv($totalCentavos, 100);
        $fraccion = $totalCentavos % 100;

        return $unidades . '.' . str_pad((string) $fraccion, 2, '0', STR_PAD_LEFT);
    }
}
