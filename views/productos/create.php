<?php

declare(strict_types=1);

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

$datos = is_array($datos ?? null) ? $datos : [];
$error = is_string($error ?? null) ? $error : null;
$csrfToken = is_string($csrfToken ?? null) ? $csrfToken : '';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar producto | Minimarket La Esquina</title>
</head>
<body>
    <header>
        <h1>Minimarket La Esquina</h1>
        <nav aria-label="Navegación principal">
            <a href="?ruta=productos">Productos</a>
            <a href="?ruta=ventas">Ventas del día</a>
            <a href="?ruta=venta-crear">Registrar venta</a>
        </nav>
    </header>

    <main>
        <h2>Registrar producto</h2>

        <?php if ($error !== null): ?>
            <p role="alert"><?= $escapar($error) ?></p>
        <?php endif; ?>

        <form action="?ruta=producto-guardar" method="post">
            <input type="hidden" name="_csrf_token" value="<?= $escapar($csrfToken) ?>">

            <div>
                <label for="codigo">Código</label>
                <input
                    id="codigo"
                    name="codigo"
                    type="text"
                    maxlength="20"
                    required
                    value="<?= $escapar($datos['codigo'] ?? '') ?>"
                >
            </div>

            <div>
                <label for="nombre">Nombre</label>
                <input
                    id="nombre"
                    name="nombre"
                    type="text"
                    maxlength="100"
                    required
                    value="<?= $escapar($datos['nombre'] ?? '') ?>"
                >
            </div>

            <div>
                <label for="categoria">Categoría</label>
                <input
                    id="categoria"
                    name="categoria"
                    type="text"
                    maxlength="50"
                    required
                    value="<?= $escapar($datos['categoria'] ?? '') ?>"
                >
            </div>

            <div>
                <label for="precio_venta">Precio de venta (S/)</label>
                <input
                    id="precio_venta"
                    name="precio_venta"
                    type="number"
                    min="0"
                    step="0.01"
                    required
                    value="<?= $escapar($datos['precio_venta'] ?? '') ?>"
                >
            </div>

            <div>
                <label for="stock_actual">Stock actual</label>
                <input
                    id="stock_actual"
                    name="stock_actual"
                    type="number"
                    min="0"
                    step="1"
                    required
                    value="<?= $escapar($datos['stock_actual'] ?? '') ?>"
                >
            </div>

            <div>
                <label for="stock_minimo">Stock mínimo</label>
                <input
                    id="stock_minimo"
                    name="stock_minimo"
                    type="number"
                    min="0"
                    step="1"
                    required
                    value="<?= $escapar($datos['stock_minimo'] ?? '') ?>"
                >
            </div>

            <button type="submit">Guardar producto</button>
        </form>

        <p><a href="?ruta=productos">Volver al listado de productos</a></p>
    </main>
</body>
</html>
