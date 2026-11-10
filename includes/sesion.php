<?php
/**
 * Control estricto de acceso por sesión y rol.
 * Uso desde admin/ o cliente/: require_once __DIR__ . '/../includes/sesion.php'; requerirRol('admin');
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Limpia y destruye la sesión actual antes de redirigir al formulario de acceso.
 */
function destruirSesionYRedirigir(string $rutaLogin = '../login.php'): void
{
    $_SESSION = [];
    session_unset();

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
 * Verifica que exista una sesión válida y activa (y con el rol requerido si se indica).
 * Si la sesión no es válida o no cumple el rol, detiene la ejecución y redirige a login.php.
 */
function requerirRol(?string $rol = null, string $rutaLogin = '../login.php'): void
{
    $usuarioId = filter_var($_SESSION['usuario_id'] ?? null, FILTER_VALIDATE_INT);
    $rolActual = $_SESSION['usuario_rol'] ?? null;

    $tieneSesion = ($usuarioId !== false && $usuarioId !== null && $usuarioId > 0 && is_string($rolActual) && $rolActual !== '');

    if (!$tieneSesion) {
        destruirSesionYRedirigir($rutaLogin);
    }

    if ($rol !== null && $rolActual !== $rol) {
        header('Location: ' . $rutaLogin);
        exit;
    }

    global $pdo, $errorConexion;
    require_once __DIR__ . '/conexion.php';

    if ($pdo instanceof PDO) {
        try {
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

            $_SESSION['usuario_id']     = (int) $usuarioActivo['id'];
            $_SESSION['usuario_nombre'] = (string) $usuarioActivo['nombre'];
            $_SESSION['usuario_correo'] = (string) $usuarioActivo['correo'];
            $_SESSION['usuario_rol']    = (string) $usuarioActivo['rol'];
        } catch (PDOException $e) {
            error_log('Error al verificar sesión activa en includes/sesion.php: ' . $e->getMessage());
        }
    }
}
