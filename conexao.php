<?php
/**
 * Conexão centralizada com o banco INTERCLASSE_SESI via PDO.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'INTERCLASSE_SESI');
define('DB_USER', 'root');
define('DB_PASS', '');

function conectar(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            die('Erro ao conectar ao banco de dados. Verifique se o MySQL está em execução no XAMPP.');
        }
    }

    return $pdo;
}
