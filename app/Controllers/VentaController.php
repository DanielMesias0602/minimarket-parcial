<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\AppException;
use App\Exceptions\ValidationException;
use App\Services\ProductoService;
use App\Services\VentaService;
use Throwable;

/**
 * Atiende las solicitudes HTTP para registrar ventas y listar las del día.
 */
final class VentaController
{
    /**
     * Recibe los servicios requeridos para el formulario y las operaciones de venta.
     *
     * @param VentaService $ventas Servicio con las reglas de negocio de ventas.
     * @param ProductoService $productos Servicio para cargar productos seleccionables.
     */
    public function __construct(
        private VentaService $ventas,
        private ProductoService $productos
    ) {
    }

    /**
     * Carga el formulario de venta con productos y un token CSRF.
     *
     * @return void
     */
    public function mostrarFormulario(): void
    {
        $this->iniciarSesion();
        $csrfToken = $this->obtenerTokenCsrf();
        $error = null;
        $datos = [];
        $productos = $this->cargarProductosFormulario($error);

        require dirname(__DIR__, 2) . '/views/ventas/create.php';
    }

    /**
     * Valida CSRF y procesa la venta solicitada.
     *
     * @return void
     */
    public function procesarVenta(): void
    {
        $this->iniciarSesion();

        $token = $_POST['_csrf_token'] ?? null;
        $tokenSesion = $_SESSION['_csrf_token'] ?? null;

        if (!is_string($token) || !is_string($tokenSesion) || !hash_equals($tokenSesion, $token)) {
            http_response_code(403);
            $csrfToken = $this->obtenerTokenCsrf();
            $error = 'La solicitud expiró o no es válida. Vuelve a intentarlo.';
            $datos = [];
            $productos = $this->cargarProductosFormulario($error);

            require dirname(__DIR__, 2) . '/views/ventas/create.php';
            return;
        }

        try {
            $idProducto = $this->obtenerEnteroPost('id_producto', 'El producto seleccionado no es válido.');
            $cantidad = $this->obtenerEnteroPost(
                'cantidad',
                'La cantidad debe ser mayor a cero.',
                permitirCero: true
            );
            $venta = $this->ventas->registrarVenta($idProducto, $cantidad);
            $_SESSION['_flash_success'] = sprintf(
                'Venta #%d registrada correctamente. Total: S/ %s.',
                $venta->idVenta,
                $venta->total
            );

            header('Location: ?ruta=ventas');
            exit;
        } catch (AppException $errorNegocio) {
            $csrfToken = $this->obtenerTokenCsrf();
            $error = $errorNegocio->getMessage();
            $datos = $_POST;
            $productos = $this->cargarProductosFormulario($error);

            require dirname(__DIR__, 2) . '/views/ventas/create.php';
        } catch (Throwable $errorTecnico) {
            error_log(sprintf(
                'Error al registrar venta: %s: %s',
                $errorTecnico::class,
                $errorTecnico->getMessage()
            ));

            http_response_code(500);
            $csrfToken = $this->obtenerTokenCsrf();
            $error = 'No se pudo registrar la venta. Intenta nuevamente.';
            $datos = $_POST;
            $productos = $this->cargarProductosFormulario($error);

            require dirname(__DIR__, 2) . '/views/ventas/create.php';
        }
    }

    /**
     * Carga las ventas de hoy y presenta el total acumulado.
     *
     * @return void
     */
    public function listarDelDia(): void
    {
        $this->iniciarSesion();
        $csrfToken = $this->obtenerTokenCsrf();
        $error = null;

        try {
            $resultado = $this->ventas->listarVentasDelDia();
            $ventas = $resultado['ventas'];
            $total = $resultado['total'];
        } catch (Throwable $errorTecnico) {
            error_log(sprintf(
                'Error al listar ventas del día: %s: %s',
                $errorTecnico::class,
                $errorTecnico->getMessage()
            ));

            http_response_code(500);
            $ventas = [];
            $total = '0.00';
            $error = 'No se pudo cargar el listado de ventas.';
        }

        $mensajeExito = $_SESSION['_flash_success'] ?? null;
        unset($_SESSION['_flash_success']);

        require dirname(__DIR__, 2) . '/views/ventas/index.php';
    }

    /**
     * Lee un entero del POST y rechaza valores mal formados.
     *
     * @param string $campo Nombre de la clave POST.
     * @param string $mensaje Mensaje controlado ante un valor inválido.
     * @param bool $permitirCero Indica si el valor cero se deja validar en el servicio.
     * @return int Entero extraído del formulario.
     *
     * @throws ValidationException Si el campo falta o no es un entero válido.
     */
    private function obtenerEnteroPost(string $campo, string $mensaje, bool $permitirCero = false): int
    {
        $valor = $_POST[$campo] ?? null;

        if (!is_string($valor) || preg_match('/^-?\d+$/', $valor) !== 1) {
            throw new ValidationException($mensaje);
        }

        $entero = filter_var($valor, FILTER_VALIDATE_INT);

        if ($entero === false) {
            throw new ValidationException($mensaje);
        }

        if (!$permitirCero && $entero <= 0) {
            throw new ValidationException($mensaje);
        }

        return $entero;
    }

    /**
     * Carga los productos del formulario y maneja fallos técnicos sin exponerlos.
     *
     * @param string|null $error Mensaje que la vista debe mostrar si ocurre un fallo.
     * @return list<\App\Models\Producto> Productos seleccionables, o lista vacía ante un error.
     */
    private function cargarProductosFormulario(?string &$error): array
    {
        try {
            return $this->productos->listarProductos();
        } catch (Throwable $errorTecnico) {
            error_log(sprintf(
                'Error al cargar productos para la venta: %s: %s',
                $errorTecnico::class,
                $errorTecnico->getMessage()
            ));

            http_response_code(500);
            $error = 'No se pudieron cargar los productos para la venta.';

            return [];
        }
    }

    /**
     * Inicia la sesión solo si todavía no está activa.
     *
     * @return void
     */
    private function iniciarSesion(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    /**
     * Obtiene el token CSRF de la sesión o crea uno criptográficamente seguro.
     *
     * @return string Token CSRF para incluir en el formulario.
     */
    private function obtenerTokenCsrf(): string
    {
        $token = $_SESSION['_csrf_token'] ?? null;

        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            $_SESSION['_csrf_token'] = $token;
        }

        return $token;
    }
}
