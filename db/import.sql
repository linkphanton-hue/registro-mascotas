-- Creación de la base de datos
CREATE DATABASE IF NOT EXISTS `registro_mascotas` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `registro_mascotas`;

-- Tabla de usuarios (administradores y clientes) con soporte de borrado lógico
CREATE TABLE IF NOT EXISTS `usuarios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(100) NOT NULL,
    `correo` VARCHAR(254) NOT NULL UNIQUE,
    `contrasena` VARCHAR(255) NOT NULL,
    `rol` ENUM('admin', 'cliente') NOT NULL DEFAULT 'cliente',
    `estado` ENUM('Activo', 'Inactivo') NOT NULL DEFAULT 'Activo',
    `creado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de mascotas asociadas a los clientes con edad en años y meses y borrado lógico
CREATE TABLE IF NOT EXISTS `mascotas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(100) NOT NULL,
    `especie` VARCHAR(50) NOT NULL,
    `raza` VARCHAR(100) NOT NULL DEFAULT 'No especificada',
    `edad_anios` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `edad_meses` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `usuario_id` INT NOT NULL,
    `estado` ENUM('Activo', 'Inactivo') NOT NULL DEFAULT 'Activo',
    `creado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_mascotas_usuarios` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de mensajes enviados desde el formulario público de contacto
CREATE TABLE IF NOT EXISTS `mensajes_contacto` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(100) NOT NULL,
    `correo` VARCHAR(254) NOT NULL,
    `asunto` VARCHAR(150) NOT NULL,
    `mensaje` TEXT NOT NULL,
    `creado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Datos de ejemplo iniciales (password por defecto para pruebas: 12345678)
-- Hash generado con password_hash('12345678', PASSWORD_DEFAULT)
INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `contrasena`, `rol`, `estado`) VALUES
(1, 'Administrador', 'admin@veterinaria.com', '$2y$10$Ra8DTymnTEkr98FerGKl9O6TES7E8DvPM1T.RRS/YGY1uBc5yiRC6', 'admin', 'Activo'),
(2, 'Laura Pérez', 'laura@example.com', '$2y$10$Ra8DTymnTEkr98FerGKl9O6TES7E8DvPM1T.RRS/YGY1uBc5yiRC6', 'cliente', 'Activo')
ON DUPLICATE KEY UPDATE `contrasena` = VALUES(`contrasena`), `estado` = VALUES(`estado`);

INSERT INTO `mascotas` (`id`, `nombre`, `especie`, `raza`, `edad_anios`, `edad_meses`, `usuario_id`, `estado`) VALUES
(1, 'Max', 'perro', 'Golden Retriever', 3, 0, 2, 'Activo'),
(2, 'Luna', 'gato', 'Siamés', 2, 0, 2, 'Activo')
ON DUPLICATE KEY UPDATE `raza` = VALUES(`raza`), `estado` = VALUES(`estado`);
