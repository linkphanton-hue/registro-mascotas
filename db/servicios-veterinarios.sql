-- Ejecutar en la base existente de registro-mascotas después de instalar las tablas de citas.
CREATE TABLE IF NOT EXISTS servicios_veterinarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO servicios_veterinarios (nombre, descripcion) VALUES
    ('Primera consulta veterinaria', 'Valoración inicial y revisión del historial de tu mascota.'),
    ('Consulta general', 'Revisión de salud y evaluación de síntomas o cambios de comportamiento.'),
    ('Vacunación', 'Revisión y aplicación de las vacunas que correspondan.'),
    ('Desparasitación', 'Evaluación y control de parásitos internos y externos.'),
    ('Control y seguimiento', 'Revisión de la evolución después de un tratamiento o consulta.'),
    ('Análisis de laboratorio', 'Toma de muestras y pruebas según indicación veterinaria.'),
    ('Evaluación prequirúrgica', 'Evaluación de salud previa a un procedimiento quirúrgico.'),
    ('Orientación nutricional', 'Revisión de la alimentación y del peso de tu mascota.')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

ALTER TABLE citas ADD COLUMN IF NOT EXISTS servicio_nombre VARCHAR(100) NULL AFTER motivo;
