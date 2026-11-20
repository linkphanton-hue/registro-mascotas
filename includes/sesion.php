<?php
/**
 * Middleware de control de sesiones y autorización basada en roles (RBAC).
 *
 * Protege los controladores privados de admin/ y cliente/ verificando tanto la
 * sesión en memoria como la vigencia real de la cuenta ('Activo') en MySQL.
 *
 * @package RegistroMascotas\Includes
 */

/* Inicia el gestor de sesiones de PHP únicamente si no ha sido activado previamente */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Revoca por completo la sesión activa y redirige al formulario de inicio de sesión.
 *
 * Vacía el arreglo $_SESSION, invalida la cookie PHPSESSID en el navegador fijando
 * una fecha de expiración en el pasado (conservando los flags HttpOnly y Secure)
 * y elimina el archivo de sesión en el servidor antes de redirigir con header().
 *
 * @param string $rutaLogin Ruta de redirección hacia la pantalla de acceso.
 * @return void
 */
function destruirSesionYRedirigir(string $rutaLogin = '../login.php'): void
{
    $_SESSION = [];
    session_unset();

    /* Caduca la cookie de sesión en el cliente para impedir reutilización de IDs antiguos */
    if (ini_get('session.use_cookies')) {
        $parametros = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $parametros['path'],
            $parametros['domain'],
            $parametros['secure'],
            $parametros['httponly']
        );
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }

    header('Location: ' . $rutaLogin);
    exit;
}

/**
 * Verifica que el usuario tenga una sesión autenticada, siga 'Activo' en MySQL
 * y posea el rol requerido para ejecutar el controlador actual.
 *
 * Si alguna validación falla, detiene de inmediato la ejecución mediante
 * header('Location: ...') y exit para evitar que el resto del script se procese.
 *
 * @param string|null $rol       Rol exigido ('admin' o 'cliente'), o null si basta estar autenticado.
 * @param string      $rutaLogin Ruta hacia login.php en caso de rechazo de acceso.
 * @return void
 */
function requerirRol(?string $rol = null, string $rutaLogin = '../login.php'): void
{
    /*
     * Paso 1: Validación en memoria.
     * Filtra usuario_id como entero positivo y comprueba que exista un rol asignado en $_SESSION.
     */
    $usuarioId = filter_var($_SESSION['usuario_id'] ?? null, FILTER_VALIDATE_INT);
    $rolActual = $_SESSION['usuario_rol'] ?? null;

    $tieneSesion = ($usuarioId !== false && $usuarioId !== null && $usuarioId > 0 && is_string($rolActual) && $rolActual !== '');

    if (!$tieneSesion) {
        destruirSesionYRedirigir($rutaLogin);
    }

    /*
     * Paso 2: Control de acceso por rol (RBAC).
     * Impide que un cliente acceda a rutas de admin/ o viceversa antes de consultar la BD.
     */
    if ($rol !== null && $rolActual !== $rol) {
        header('Location: ' . $rutaLogin);
        exit;
    }

    /*
     * Paso 3: Verificación en tiempo real contra MySQL.
     * Se declara global $pdo antes de require_once para que la conexión quede disponible
     * en el ámbito global de la página que invocó este middleware.
     */
    global $pdo, $errorConexion;
    require_once __DIR__ . '/conexion.php';

    if ($pdo instanceof PDO) {
        try {
            /* Consulta preparada que confirma que el usuario exista y no haya sido dado de baja lógica */
            $stmtSesion = $pdo->prepare(
                "SELECT id, nombre, correo, rol FROM usuarios WHERE id = :id AND estado = :estado LIMIT 1"
            );
            $stmtSesion->execute([
                ':id'     => $usuarioId,
                ':estado' => 'Activo',
            ]);
            $usuarioActivo = $stmtSesion->fetch();

            if (!$usuarioActivo) {
                destruirSesionYRedirigir($rutaLogin);
            }

            if ($rol !== null && $usuarioActivo['rol'] !== $rol) {
                header('Location: ' . $rutaLogin);
                exit;
            }

            /* Sincroniza $_SESSION con los datos vigentes de la BD por si cambiaron nombre o correo */
            $_SESSION['usuario_id']     = (int) $usuarioActivo['id'];
            $_SESSION['usuario_nombre'] = (string) $usuarioActivo['nombre'];
            $_SESSION['usuario_correo'] = (string) $usuarioActivo['correo'];
            $_SESSION['usuario_rol']    = (string) $usuarioActivo['rol'];
        } catch (PDOException $e) {
            error_log('Error al verificar sesión activa en includes/sesion.php: ' . $e->getMessage());
        }
    }
}
