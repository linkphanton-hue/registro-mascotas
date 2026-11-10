<?php
/**
 * Cierre seguro de sesión de usuario.
 * Limpia las variables de sesión, invalida la cookie y destruye la sesión en el servidor.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

session_destroy();

header('Location: login.php');
exit;

