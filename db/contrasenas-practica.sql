-- Ejecutar en la base existente de la práctica.
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS contrasena_con_hash TINYINT(1) NOT NULL DEFAULT 1 AFTER contrasena;

-- Solo actualiza las cuentas que todavía tienen exactamente la contraseña de ejemplo original.
UPDATE usuarios
SET contrasena = '12345678', contrasena_con_hash = 0
WHERE contrasena_con_hash = 1
  AND ((id = 1 AND correo = 'admin@veterinaria.com') OR (id = 2 AND correo = 'laura@example.com'))
  AND BINARY contrasena = '$2y$10$Ra8DTymnTEkr98FerGKl9O6TES7E8DvPM1T.RRS/YGY1uBc5yiRC6';
