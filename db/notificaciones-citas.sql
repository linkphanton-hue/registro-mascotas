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
