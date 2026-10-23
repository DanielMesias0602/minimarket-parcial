<?php

declare(strict_types=1);

use App\Models\Venta;

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

$ventas = is_array($ventas ?? null) ? $ventas : [];
$total = is_string($total ?? null) ? $total : '0.00';
$error = is_string($error ?? null) ? $error : null;
$mensajeExito = is_string($mensajeExito ?? null) ? $mensajeExito : null;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventas del día | Minimarket La Esquina</title>
</head>
<body>
    <header>
        <h1>Minimarket La Esquina</h1>
        <nav aria-label="Navegación principal">
            <a href="?ruta=productos">Productos</a>
            <a href="?ruta=producto-crear">Registrar producto</a>
            <a href="?ruta=venta-crear">Registrar venta</a>
        </nav>
    </header>

    <main>
        <h2>Ventas de hoy</h2>

        <?php if ($error !== null): ?>
            <p role="alert"><?= $escapar($error) ?></p>
        <?php endif; ?>

        <?php if ($mensajeExito !== null): ?>
            <p role="status"><?= $escapar($mensajeExito) ?></p>
        <?php endif; ?>

        <?php if ($ventas === [] && $error === null): ?>
            <p>No hay ventas registradas hoy.</p>
        <?php elseif ($ventas !== []): ?>
            <table>
                <thead>
                    <tr>
                        <th scope="col">N.º de venta</th>
                        <th scope="col">Fecha y hora</th>
                        <th scope="col">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ventas as $venta): ?>
                        <?php if (!$venta instanceof Venta): ?>
                            <?php continue; ?>
                        <?php endif; ?>
                        <tr>
                            <td><?= $escapar($venta->idVenta) ?></td>
                            <td><?= $escapar($venta->fechaHora) ?></td>
                            <td>S/ <?= $escapar($venta->total) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th scope="row" colspan="2">Total acumulado del día</th>
                        <td>S/ <?= $escapar($total) ?></td>
                    </tr>
                </tfoot>
            </table>
        <?php endif; ?>

        <?php if ($ventas === []): ?>
            <p><strong>Total acumulado del día: S/ <?= $escapar($total) ?></strong></p>
        <?php endif; ?>
    </main>
</body>
</html>
