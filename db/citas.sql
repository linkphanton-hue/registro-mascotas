-- Ejecutar en la base de datos configurada para registro-mascotas.
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
