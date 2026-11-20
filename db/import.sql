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

CREATE TABLE IF NOT EXISTS citas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mascota_id INT NOT NULL,
    usuario_id INT NOT NULL,
    fecha_hora DATETIME NULL DEFAULT NULL,
    motivo VARCHAR(500) NOT NULL,
    estado ENUM('Pendiente', 'Confirmada', 'Rechazada', 'Cambio solicitado') NOT NULL DEFAULT 'Pendiente',
    fecha_preferida DATE NULL DEFAULT NULL,
    hora_desde TIME NULL DEFAULT NULL,
    hora_hasta TIME NULL DEFAULT NULL,
    motivo_cambio VARCHAR(500) NULL DEFAULT NULL,
    estado_cambio ENUM('Pendiente', 'Aprobado', 'Rechazado') NULL DEFAULT NULL,
    version INT NOT NULL DEFAULT 0,
    solicitud_token CHAR(64) NOT NULL UNIQUE,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_citas_fecha (fecha_hora),
    INDEX idx_citas_mascota (mascota_id, fecha_hora),
    INDEX idx_citas_usuario (usuario_id),
    CONSTRAINT fk_citas_mascotas FOREIGN KEY (mascota_id) REFERENCES mascotas (id),
    CONSTRAINT fk_citas_usuarios FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
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

-- Esta fila permite serializar las confirmaciones de la agenda en MySQL.
CREATE TABLE IF NOT EXISTS agenda_citas (
    id TINYINT UNSIGNED PRIMARY KEY
) ENGINE=InnoDB;
INSERT INTO agenda_citas (id) VALUES (1) ON DUPLICATE KEY UPDATE id = VALUES(id);

CREATE TABLE IF NOT EXISTS notificaciones_citas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cita_id INT NOT NULL,
    usuario_id INT NOT NULL,
    version_cita INT NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    mensaje TEXT NOT NULL,
    destinatario VARCHAR(254) NOT NULL,
    remitente VARCHAR(254) NOT NULL,
    leida TINYINT(1) NOT NULL DEFAULT 0,
    estado_correo ENUM('Pendiente', 'Enviando', 'Enviado', 'Error', 'Simulado') NOT NULL DEFAULT 'Pendiente',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    enviado_en DATETIME NULL DEFAULT NULL,
    UNIQUE KEY uq_notificacion_cita_version (cita_id, version_cita),
    INDEX idx_notificaciones_usuario (usuario_id, leida),
    CONSTRAINT fk_notificaciones_citas FOREIGN KEY (cita_id) REFERENCES citas(id) ON DELETE CASCADE,
    CONSTRAINT fk_notificaciones_usuarios FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
