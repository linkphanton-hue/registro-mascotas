<?php
/**
 * Controlador de cierre seguro de sesión.
 *
 * Limpia el arreglo $_SESSION en memoria, caduca la cookie PHPSESSID en el
 * navegador y destruye el registro de sesión en el servidor antes de redirigir.
 *
 * @package RegistroMascotas
 */

require_once __DIR__ . '/includes/sesion.php';

$_SESSION = [];
session_unset();

/* Invalida la cookie de sesión en el navegador manteniendo los parámetros originales de seguridad */
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

