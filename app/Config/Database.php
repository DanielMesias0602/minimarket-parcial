<?php

declare(strict_types=1);

namespace App\Config;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Crea y reutiliza la conexión PDO configurada desde el archivo .env.
 */
final class Database
{
    /**
     * Conexión compartida para mantener una sola sesión de base de datos.
     */
    private static ?PDO $conexion = null;

    /**
     * Devuelve la conexión PDO y la crea solo cuando aún no existe.
     *
     * @return PDO Conexión activa a MySQL/MariaDB.
     */
    public static function conectar(): PDO
    {
        if (self::$conexion instanceof PDO) {
            return self::$conexion;
        }

        $host = Env::get('DB_HOST');
        $puerto = Env::get('DB_PORT');
        $nombre = Env::get('DB_NAME');
        $usuario = Env::get('DB_USER');
        $contrasena = Env::get('DB_PASS');

        if ($host === null || $host === '' || $nombre === null || $nombre === '') {
            throw new RuntimeException('La configuración DB_HOST y DB_NAME debe estar definida en .env.');
        }

        if ($puerto === null || filter_var(
            $puerto,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 65535]]
        ) === false) {
            throw new RuntimeException('DB_PORT debe ser un puerto válido entre 1 y 65535.');
        }

        if ($usuario === null || $usuario === '') {
            throw new RuntimeException('La configuración DB_USER debe estar definida en .env.');
        }

        if ($contrasena === null) {
            throw new RuntimeException('La configuración DB_PASS debe estar definida en .env; puede estar vacía.');
        }

        if (str_contains($host, ';') || str_contains($host, "\0")
            || str_contains($nombre, ';') || str_contains($nombre, "\0")) {
            throw new RuntimeException('DB_HOST y DB_NAME contienen caracteres no permitidos.');
        }

        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $puerto, $nombre);

        try {
            self::$conexion = new PDO(
                $dsn,
                $usuario,
                $contrasena,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $error) {
            throw new RuntimeException('No se pudo conectar a la base de datos.', 0, $error);
        }

        return self::$conexion;
    }
}
