<?php

declare(strict_types=1);

namespace App\Config;

use ErrorException;
use RuntimeException;

/**
 * Lee y conserva en memoria la configuración del archivo .env del proyecto.
 */
final class Env
{
    /**
     * Valores leídos desde .env; null indica que el archivo aún no se ha cargado.
     *
     * @var array<string, string>|null
     */
    private static ?array $variables = null;

    /**
     * Obtiene un valor de configuración sin consultar variables del sistema.
     *
     * @param string $clave Nombre de la variable definida en .env.
     * @return string|null Valor configurado, o null si la clave no existe.
     */
    public static function get(string $clave): ?string
    {
        self::cargar();

        return self::$variables[$clave] ?? null;
    }

    /**
     * Analiza .env una sola vez y falla explícitamente ante errores de lectura.
     *
     * @return void
     */
    private static function cargar(): void
    {
        if (self::$variables !== null) {
            return;
        }

        $ruta = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env';

        if (!is_file($ruta) || !is_readable($ruta)) {
            throw new RuntimeException('No se encontró un archivo .env legible en la raíz del proyecto.');
        }

        set_error_handler(
            static function (int $nivel, string $mensaje, string $archivo, int $linea): void {
                throw new ErrorException($mensaje, 0, $nivel, $archivo, $linea);
            }
        );

        try {
            $variables = parse_ini_file($ruta, false, INI_SCANNER_RAW);
        } catch (ErrorException $error) {
            throw new RuntimeException('No se pudo analizar el archivo .env.', 0, $error);
        } finally {
            restore_error_handler();
        }

        if ($variables === false) {
            throw new RuntimeException('No se pudo analizar el archivo .env.');
        }

        foreach ($variables as $clave => $valor) {
            if (!is_string($valor)) {
                throw new RuntimeException(sprintf('El valor de configuración "%s" debe ser texto.', $clave));
            }
        }

        self::$variables = $variables;
    }
}
