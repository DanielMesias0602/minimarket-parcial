<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\ProductoController;
use App\Controllers\VentaController;
use App\Exceptions\AppException;
use App\Repositories\ProductoRepository;
use App\Repositories\VentaRepository;
use App\Services\ProductoService;
use App\Services\VentaService;

require_once dirname(__DIR__) . '/app/Config/Env.php';
require_once dirname(__DIR__) . '/app/Config/Database.php';
require_once dirname(__DIR__) . '/app/Models/Producto.php';
require_once dirname(__DIR__) . '/app/Models/Venta.php';
require_once dirname(__DIR__) . '/app/Repositories/ProductoRepository.php';
require_once dirname(__DIR__) . '/app/Repositories/VentaRepository.php';
require_once dirname(__DIR__) . '/app/Exceptions/AppException.php';
require_once dirname(__DIR__) . '/app/Exceptions/ValidationException.php';
require_once dirname(__DIR__) . '/app/Exceptions/DuplicateProductCodeException.php';
require_once dirname(__DIR__) . '/app/Exceptions/ProductNotFoundException.php';
require_once dirname(__DIR__) . '/app/Exceptions/InsufficientStockException.php';
require_once dirname(__DIR__) . '/app/Services/ProductoService.php';
require_once dirname(__DIR__) . '/app/Services/VentaService.php';
require_once dirname(__DIR__) . '/app/Controllers/ProductoController.php';
require_once dirname(__DIR__) . '/app/Controllers/VentaController.php';

try {
    $pdo = Database::conectar();
    $productoRepository = new ProductoRepository($pdo);
    $ventaRepository = new VentaRepository($pdo);
    $productoService = new ProductoService($productoRepository);
    $ventaService = new VentaService($pdo, $productoRepository, $ventaRepository);
    $productoController = new ProductoController($productoService);
    $ventaController = new VentaController($ventaService, $productoService);

    $ruta = $_GET['ruta'] ?? 'productos';
    $metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if (!is_string($ruta) || !is_string($metodo)) {
        http_response_code(400);
        throw new AppException('La solicitud no es válida.');
    }

    $rutas = [
        'productos' => ['GET', [$productoController, 'listar']],
        'producto-crear' => ['GET', [$productoController, 'mostrarFormulario']],
        'producto-guardar' => ['POST', [$productoController, 'procesarRegistro']],
        'venta-crear' => ['GET', [$ventaController, 'mostrarFormulario']],
        'venta-guardar' => ['POST', [$ventaController, 'procesarVenta']],
        'ventas' => ['GET', [$ventaController, 'listarDelDia']],
    ];

    if (!isset($rutas[$ruta])) {
        http_response_code(404);
        echo 'Página no encontrada.';
        exit;
    }

    [$metodoPermitido, $accion] = $rutas[$ruta];

    if ($metodo !== $metodoPermitido) {
        header('Allow: ' . $metodoPermitido);
        http_response_code(405);
        echo 'Método no permitido.';
        exit;
    }

    $accion();
} catch (Throwable $error) {
    error_log(sprintf(
        'Error no controlado en la aplicación: %s: %s',
        $error::class,
        $error->getMessage()
    ));

    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');
    }

    echo '<!DOCTYPE html><html lang="es"><meta charset="UTF-8">'
        . '<title>Error</title><body><h1>No se pudo completar la solicitud.</h1>'
        . '<p>Intenta nuevamente. Si el problema continúa, contacta al administrador.</p>'
        . '</body></html>';
}
