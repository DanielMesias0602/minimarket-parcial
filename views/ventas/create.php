<?php

declare(strict_types=1);

use App\Models\Producto;

/**
 * Escapa valores dinámicos antes de insertarlos en el HTML.
 *
 * @param mixed $valor Valor que se mostrará en la página o en un atributo.
 * @return string Texto seguro para HTML.
 */
$escapar = static function (mixed $valor): string {
    if (!is_scalar($valor)) {
        return '';
    }

    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};

$productos = is_array($productos ?? null) ? $productos : [];
$datos = is_array($datos ?? null) ? $datos : [];
$error = is_string($error ?? null) ? $error : null;
$csrfToken = is_string($csrfToken ?? null) ? $csrfToken : '';

$idSeleccionado = $datos['id_producto'] ?? '';
$cantidadIngresada = $datos['cantidad'] ?? '';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar venta | Minimarket La Esquina</title>
</head>
<body>
    <header>
        <h1>Minimarket La Esquina</h1>
        <nav aria-label="Navegación principal">
            <a href="?ruta=productos">Productos</a>
            <a href="?ruta=producto-crear">Registrar producto</a>
            <a href="?ruta=ventas">Ventas del día</a>
        </nav>
    </header>

    <main>
        <h2>Registrar venta</h2>

        <?php if ($error !== null): ?>
            <p role="alert"><?= $escapar($error) ?></p>
        <?php endif; ?>

        <?php if ($productos === []): ?>
            <p>No hay productos disponibles para vender.</p>
            <p><a href="?ruta=producto-crear">Registrar un producto</a></p>
        <?php else: ?>
            <form action="?ruta=venta-guardar" method="post">
                <input type="hidden" name="_csrf_token" value="<?= $escapar($csrfToken) ?>">

                <div>
                    <label for="id_producto">Producto</label>
                    <select id="id_producto" name="id_producto" required>
                        <option value="">Selecciona un producto</option>
                        <?php foreach ($productos as $producto): ?>
                            <?php if (!$producto instanceof Producto || $producto->idProducto === null): ?>
                                <?php continue; ?>
                            <?php endif; ?>
                            <?php $idProducto = (string) $producto->idProducto; ?>
                            <option
                                value="<?= $escapar($idProducto) ?>"
                                <?= (string) $idSeleccionado === $idProducto ? 'selected' : '' ?>
                                <?= $producto->stockActual === 0 ? 'disabled' : '' ?>
                            >
                                <?= $escapar($producto->nombre) ?>
                                — Stock: <?= $escapar($producto->stockActual) ?>
                                — S/ <?= $escapar($producto->precioVenta) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="cantidad">Cantidad</label>
                    <input
                        id="cantidad"
                        name="cantidad"
                        type="number"
                        min="1"
                        step="1"
                        required
                        value="<?= $escapar($cantidadIngresada) ?>"
                    >
                </div>

                <button type="submit">Registrar venta</button>
            </form>
        <?php endif; ?>
    </main>
</body>
</html>
