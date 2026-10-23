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
$error = is_string($error ?? null) ? $error : null;
$mensajeExito = is_string($mensajeExito ?? null) ? $mensajeExito : null;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos | Minimarket La Esquina</title>
    <style>
        .stock-bajo {
            background-color: #ffe5e5;
            color: #8b0000;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <header>
        <h1>Minimarket La Esquina</h1>
        <nav aria-label="Navegación principal">
            <a href="?ruta=productos">Productos</a>
            <a href="?ruta=producto-crear">Registrar producto</a>
            <a href="?ruta=ventas">Ventas del día</a>
            <a href="?ruta=venta-crear">Registrar venta</a>
        </nav>
    </header>

    <main>
        <h2>Productos registrados</h2>

        <?php if ($error !== null): ?>
            <p role="alert"><?= $escapar($error) ?></p>
        <?php endif; ?>

        <?php if ($mensajeExito !== null): ?>
            <p role="status"><?= $escapar($mensajeExito) ?></p>
        <?php endif; ?>

        <?php if ($productos === [] && $error === null): ?>
            <p>No hay productos registrados.</p>
        <?php elseif ($productos !== []): ?>
            <table>
                <thead>
                    <tr>
                        <th scope="col">Código</th>
                        <th scope="col">Nombre</th>
                        <th scope="col">Categoría</th>
                        <th scope="col">Precio de venta</th>
                        <th scope="col">Stock actual</th>
                        <th scope="col">Stock mínimo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $producto): ?>
                        <?php if (!$producto instanceof Producto): ?>
                            <?php continue; ?>
                        <?php endif; ?>
                        <?php $stockBajo = $producto->stockActual < $producto->stockMinimo; ?>
                        <tr class="<?= $stockBajo ? 'stock-bajo' : '' ?>">
                            <td><?= $escapar($producto->codigo) ?></td>
                            <td>
                                <?= $escapar($producto->nombre) ?>
                                <?php if ($stockBajo): ?>
                                    <span aria-label="Alerta: stock bajo">⚠</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $escapar($producto->categoria) ?></td>
                            <td>S/ <?= $escapar($producto->precioVenta) ?></td>
                            <td><?= $escapar($producto->stockActual) ?></td>
                            <td><?= $escapar($producto->stockMinimo) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
</body>
</html>
