<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\DuplicateProductCodeException;
use App\Exceptions\ValidationException;
use App\Models\Producto;
use App\Repositories\ProductoRepository;
use PDOException;

/**
 * Aplica las reglas de negocio del registro y consulta de productos.
 */
final class ProductoService
{
    /**
     * Recibe el repositorio que ejecuta las consultas de productos.
     *
     * @param ProductoRepository $productos Repositorio de productos.
     */
    public function __construct(
        private ProductoRepository $productos
    ) {
    }

    /**
     * Valida y registra un producto.
     *
     * @param array<string, mixed> $datos Datos recibidos para el producto.
     * @return int Identificador generado para el producto.
     *
     * @throws ValidationException Si faltan campos o sus formatos no son válidos.
     * @throws DuplicateProductCodeException Si el código ya está registrado.
     */
    public function registrarProducto(array $datos): int
    {
        $codigo = $this->obtenerTexto($datos, 'codigo');
        $nombre = $this->obtenerTexto($datos, 'nombre');
        $categoria = $this->obtenerTexto($datos, 'categoria');
        $precio = $this->obtenerPrecio($datos['precio_venta'] ?? null);
        $stockActual = $this->obtenerEnteroNoNegativo($datos['stock_actual'] ?? null, 'stock actual');
        $stockMinimo = $this->obtenerEnteroNoNegativo($datos['stock_minimo'] ?? null, 'stock mínimo');

        if (strlen($codigo) > 20 || strlen($nombre) > 100 || strlen($categoria) > 50) {
            throw new ValidationException('Uno o más campos exceden la longitud permitida.');
        }

        if ($this->productos->existeCodigo($codigo)) {
            throw new DuplicateProductCodeException('El código de producto ya existe.');
        }

        $producto = new Producto(
            codigo: $codigo,
            nombre: $nombre,
            categoria: $categoria,
            precioVenta: $precio,
            stockActual: $stockActual,
            stockMinimo: $stockMinimo
        );

        try {
            return $this->productos->guardar($producto);
        } catch (PDOException $error) {
            if ($this->esDuplicidadDeCodigo($error)) {
                throw new DuplicateProductCodeException('El código de producto ya existe.', 0, $error);
            }

            throw $error;
        }
    }

    /**
     * Devuelve todos los productos registrados.
     *
     * @return list<Producto> Productos registrados; puede ser una lista vacía.
     */
    public function listarProductos(): array
    {
        return $this->productos->listarTodos();
    }

    /**
     * Obtiene un campo obligatorio como texto no vacío.
     *
     * @param array<string, mixed> $datos Datos de entrada.
     * @param string $campo Nombre de la clave requerida.
     * @return string Texto sin espacios externos.
     */
    private function obtenerTexto(array $datos, string $campo): string
    {
        $valor = $datos[$campo] ?? null;

        if (!is_string($valor) || trim($valor) === '') {
            throw new ValidationException(sprintf('El campo %s es obligatorio.', $campo));
        }

        return trim($valor);
    }

    /**
     * Valida un precio decimal no negativo y compatible con DECIMAL(10,2).
     *
     * @param mixed $valor Valor recibido del formulario.
     * @return string Precio normalizado con dos decimales.
     */
    private function obtenerPrecio(mixed $valor): string
    {
        if (!is_string($valor) && !is_int($valor)) {
            throw new ValidationException('El precio ingresado no es válido.');
        }

        $precio = trim((string) $valor);

        if (preg_match('/^-\d+(?:\.\d{1,2})?$/', $precio) === 1) {
            throw new ValidationException('El precio no puede ser negativo.');
        }

        if (preg_match('/^(\d{1,8})(?:\.(\d{1,2}))?$/', $precio, $partes) !== 1) {
            throw new ValidationException('El precio ingresado no es válido.');
        }

        $entero = ltrim($partes[1], '0');
        $fraccion = $partes[2] ?? '';

        return ($entero === '' ? '0' : $entero) . '.' . str_pad($fraccion, 2, '0');
    }

    /**
     * Valida que un valor sea un entero no negativo.
     *
     * @param mixed $valor Valor recibido del formulario.
     * @param string $campo Nombre del dato usado en el mensaje de error.
     * @return int Valor entero validado.
     */
    private function obtenerEnteroNoNegativo(mixed $valor, string $campo): int
    {
        if ((!is_string($valor) && !is_int($valor))
            || preg_match('/^\d+$/', (string) $valor) !== 1
            || filter_var($valor, FILTER_VALIDATE_INT) === false) {
            throw new ValidationException(sprintf('El %s debe ser un entero no negativo.', $campo));
        }

        return (int) $valor;
    }

    /**
     * Identifica una violación MySQL de la restricción única del código.
     *
     * @param PDOException $error Error PDO producido al insertar.
     * @return bool true si es una clave duplicada de MySQL.
     */
    private function esDuplicidadDeCodigo(PDOException $error): bool
    {
        return $error->getCode() === '23000'
            && isset($error->errorInfo[1])
            && (int) $error->errorInfo[1] === 1062;
    }
}
