<?php

function verificarContrasena(string $ingresada, array $usuario): bool
{
    return (int) $usuario['contrasena_con_hash'] === 1
        ? password_verify($ingresada, $usuario['contrasena'])
        : hash_equals($usuario['contrasena'], $ingresada);
}
