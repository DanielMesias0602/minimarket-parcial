<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\AppException;
use App\Services\ProductoService;
use Throwable;

/**
 * Atiende las solicitudes HTTP para registrar y listar productos.
 */
final class ProductoController
{
    /**
     * Recibe el servicio que contiene las reglas de negocio de productos.
     *
     * @param ProductoService $productos Servicio de productos.
     */
    public function __construct(
        private ProductoService $productos
    ) {
    }

    /**
     * Carga el formulario de registro con su token CSRF.
     *
     * @return void
     */
    public function mostrarFormulario(): void
    {
        $this->iniciarSesion();
        $csrfToken = $this->obtenerTokenCsrf();
        $error = null;
        $datos = [];

        require dirname(__DIR__, 2) . '/views/productos/create.php';
    }

    /**
     * Valida el token CSRF y procesa el formulario de registro.
     *
     * @return void
     */
    public function procesarRegistro(): void
    {
        $this->iniciarSesion();

        $token = $_POST['_csrf_token'] ?? null;
        $tokenSesion = $_SESSION['_csrf_token'] ?? null;

        if (!is_string($token) || !is_string($tokenSesion) || !hash_equals($tokenSesion, $token)) {
            http_response_code(403);
            $csrfToken = $this->obtenerTokenCsrf();
            $error = 'La solicitud expiró o no es válida. Vuelve a intentarlo.';
            $datos = [];

            require dirname(__DIR__, 2) . '/views/productos/create.php';
            return;
        }

        try {
            $this->productos->registrarProducto($_POST);
            $_SESSION['_flash_success'] = 'Producto registrado correctamente.';
            header('Location: ?ruta=productos');
            exit;
        } catch (AppException $error) {
            $csrfToken = $this->obtenerTokenCsrf();
            $datos = $_POST;
            $mensaje = $error->getMessage();
            $error = $mensaje;

            require dirname(__DIR__, 2) . '/views/productos/create.php';
        } catch (Throwable $errorTecnico) {
            error_log(sprintf(
                'Error al registrar producto: %s: %s',
                $errorTecnico::class,
                $errorTecnico->getMessage()
            ));

            http_response_code(500);
            $csrfToken = $this->obtenerTokenCsrf();
            $datos = $_POST;
            $error = 'No se pudo registrar el producto. Intenta nuevamente.';

            require dirname(__DIR__, 2) . '/views/productos/create.php';
        }
    }

    /**
     * Carga el listado de productos y presenta errores sin exponer detalles técnicos.
     *
     * @return void
     */
    public function listar(): void
    {
        $this->iniciarSesion();
        $csrfToken = $this->obtenerTokenCsrf();
        $error = null;

        try {
            $productos = $this->productos->listarProductos();
        } catch (Throwable $errorTecnico) {
            error_log(sprintf(
                'Error al listar productos: %s: %s',
                $errorTecnico::class,
                $errorTecnico->getMessage()
            ));

            http_response_code(500);
            $productos = [];
            $error = 'No se pudo cargar el listado de productos.';
        }

        $mensajeExito = $_SESSION['_flash_success'] ?? null;
        unset($_SESSION['_flash_success']);

        require dirname(__DIR__, 2) . '/views/productos/index.php';
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
