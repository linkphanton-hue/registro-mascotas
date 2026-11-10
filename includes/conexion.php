<?php
/**
 * Conexión a la base de datos MySQL mediante PDO.
 * Carga las credenciales desde includes/config.php.
 */

$pdo = null;
$errorConexion = null;

$rutaConfig = __DIR__ . '/config.php';

if (!file_exists($rutaConfig)) {
    error_log('Archivo de configuración includes/config.php no encontrado.');
    $errorConexion = 'No se pudo cargar la configuración de la base de datos.';
} else {
    require_once $rutaConfig;

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        DB_HOST,
        DB_NAME,
        DB_CHARSET
    );

    $opciones = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $opciones);
    } catch (PDOException $e) {
        error_log('Error PDO al conectar con la base de datos: ' . $e->getMessage());
        $errorConexion = 'No fue posible establecer conexión con la base de datos en este momento.';
    }
}
