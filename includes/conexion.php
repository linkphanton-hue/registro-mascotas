<?php
/**
 * Módulo de conexión a MySQL mediante la capa de abstracción PDO.
 *
 * Inicializa la instancia global $pdo configurada con consultas preparadas nativas,
 * modo de retorno asociativo y manejo silencioso de excepciones para proteger
 * la información sensible del servidor ante eventuales caídas del motor SQL.
 *
 * @package RegistroMascotas\Includes
 * @var PDO|null    $pdo           Instancia activa de PDO o null si falla la conexión.
 * @var string|null $errorConexion Mensaje controlado para mostrar en la interfaz de usuario.
 */

$pdo = null;
$errorConexion = null;

/*
 * Comprueba la existencia física de includes/config.php antes de cargarlo.
 * Si no existe, registra el incidente en el log del servidor sin detener abruptamente la vista.
 */
$rutaConfig = __DIR__ . '/config.php';

if (!file_exists($rutaConfig)) {
    error_log('Archivo de configuración includes/config.php no encontrado.');
    $errorConexion = 'No se pudo cargar la configuración de la base de datos.';
} else {
    require_once $rutaConfig;

    /*
     * Construye el Data Source Name (DSN) forzando utf8mb4 para soportar
     * tildes, eñes y caracteres especiales sin riesgo de truncamiento.
     */
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        DB_HOST,
        DB_NAME,
        DB_CHARSET
    );

    /*
     * Configuración de seguridad y comportamiento de PDO:
     * - ERRMODE_EXCEPTION: lanza PDOException ante cualquier error SQL para capturarlo en bloques try/catch.
     * - FETCH_ASSOC: devuelve cada fila como arreglo asociativo indexado por nombre de columna.
     * - EMULATE_PREPARES => false: delega la compilación de consultas al motor MySQL, separando
     *   estrictamente la instrucción SQL de los datos para neutralizar ataques de inyección SQL.
     */
    $opciones = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    /*
     * Intenta abrir la conexión con MySQL. Si ocurre un fallo, el detalle técnico se envía
     * al log privado (error_log) y al usuario solo se le expone un mensaje genérico seguro.
     */
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $opciones);
    } catch (PDOException $e) {
        error_log('Error PDO al conectar con la base de datos: ' . $e->getMessage());
        $errorConexion = 'No fue posible establecer conexión con la base de datos en este momento.';
    }
}
