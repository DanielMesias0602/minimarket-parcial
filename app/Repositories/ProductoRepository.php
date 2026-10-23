<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Producto;
use PDO;

/**
 * Ejecuta consultas SQL relacionadas con productos.
 */
final class ProductoRepository
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
     * Inserta un producto y devuelve el identificador generado por la base de datos.
     *
     * @param Producto $producto Producto que se insertará.
     * @return int Identificador autoincremental del producto insertado.
     */
    public function guardar(Producto $producto): int
    {
        $consulta = $this->pdo->prepare(
            'INSERT INTO producto (codigo, nombre, categoria, precio_venta, stock_actual, stock_minimo)
             VALUES (:codigo, :nombre, :categoria, :precio_venta, :stock_actual, :stock_minimo)'
        );
        $consulta->execute([
            'codigo' => $producto->codigo,
            'nombre' => $producto->nombre,
            'categoria' => $producto->categoria,
            'precio_venta' => $producto->precioVenta,
            'stock_actual' => $producto->stockActual,
            'stock_minimo' => $producto->stockMinimo,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Comprueba si ya existe un producto con el código indicado.
     *
     * @param string $codigo Código de producto que se buscará.
     * @return bool true si el código existe; false si no existe.
     */
    public function existeCodigo(string $codigo): bool
    {
        $consulta = $this->pdo->prepare('SELECT 1 FROM producto WHERE codigo = :codigo LIMIT 1');
        $consulta->execute(['codigo' => $codigo]);

        return $consulta->fetchColumn() !== false;
    }

    /**
     * Devuelve todos los productos ordenados por su identificador.
     *
     * @return list<Producto> Productos encontrados; una lista vacía si no hay registros.
     */
    public function listarTodos(): array
    {
        $consulta = $this->pdo->prepare(
            'SELECT id_producto, codigo, nombre, categoria, precio_venta, stock_actual, stock_minimo
             FROM producto
             ORDER BY id_producto'
        );
        $consulta->execute();
        $filas = $consulta->fetchAll();
        $productos = [];

        foreach ($filas as $fila) {
            $productos[] = $this->crearProducto($fila);
        }

        return $productos;
    }

    /**
     * Busca un producto por identificador; devuelve null si no existe.
     *
     * @param int $id Identificador del producto.
     * @return Producto|null Producto encontrado o null si no hay registro.
     */
    public function obtenerPorId(int $id): ?Producto
    {
        return $this->buscarPorId($id, false);
    }

    /**
     * Busca y bloquea un producto para actualización dentro de una transacción.
     *
     * @param int $id Identificador del producto.
     * @return Producto|null Producto encontrado o null si no hay registro.
     */
    public function obtenerPorIdParaActualizar(int $id): ?Producto
    {
        return $this->buscarPorId($id, true);
    }

    /**
     * Actualiza el stock y detecta si el producto no existe.
     *
     * @param int $idProducto Identificador del producto que se actualizará.
     * @param int $nuevoStock Nuevo valor de stock.
     * @return bool true si el producto existe; false si no existe.
     */
    public function actualizarStock(int $idProducto, int $nuevoStock): bool
    {
        $consulta = $this->pdo->prepare(
            'UPDATE producto SET stock_actual = :stock_actual WHERE id_producto = :id_producto'
        );
        $consulta->execute([
            'stock_actual' => $nuevoStock,
            'id_producto' => $idProducto,
        ]);

        if ($consulta->rowCount() > 0) {
            return true;
        }

        $verificacion = $this->pdo->prepare(
            'SELECT 1 FROM producto WHERE id_producto = :id_producto LIMIT 1'
        );
        $verificacion->execute(['id_producto' => $idProducto]);

        return $verificacion->fetchColumn() !== false;
    }

    /**
     * Busca un producto y opcionalmente lo bloquea para modificarlo.
     *
     * @param int $id Identificador del producto.
     * @param bool $bloquear Indica si se añade el bloqueo SQL FOR UPDATE.
     * @return Producto|null Producto encontrado o null si no hay registro.
     */
    private function buscarPorId(int $id, bool $bloquear): ?Producto
    {
        $sql = $bloquear
            ? 'SELECT id_producto, codigo, nombre, categoria, precio_venta, stock_actual, stock_minimo
               FROM producto WHERE id_producto = :id_producto FOR UPDATE'
            : 'SELECT id_producto, codigo, nombre, categoria, precio_venta, stock_actual, stock_minimo
               FROM producto WHERE id_producto = :id_producto';
        $consulta = $this->pdo->prepare($sql);
        $consulta->execute(['id_producto' => $id]);
        $fila = $consulta->fetch();

        if ($fila === false) {
            return null;
        }

        return $this->crearProducto($fila);
    }

    /**
     * Convierte una fila asociativa de PDO en una entidad Producto.
     *
     * @param array<string, mixed> $fila Fila devuelta por PDO.
     * @return Producto Producto representado por la fila.
     */
    private function crearProducto(array $fila): Producto
    {
        return new Producto(
            codigo: (string) $fila['codigo'],
            nombre: (string) $fila['nombre'],
            categoria: (string) $fila['categoria'],
            precioVenta: (string) $fila['precio_venta'],
            stockActual: (int) $fila['stock_actual'],
            stockMinimo: (int) $fila['stock_minimo'],
            idProducto: (int) $fila['id_producto']
        );
    }
}
