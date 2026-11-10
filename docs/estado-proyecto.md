# Estado del proyecto

Actualizado: 9 de noviembre de 2026.

## Dónde nos quedamos
El sistema cuenta con todas sus pantallas públicas (`index.php`, `contacto.php`), del cliente (`cliente/panel.php`, `cliente/mis-mascotas.php`, `cliente/perfil.php`) y del administrador (`admin/panel.php`, `admin/mascotas.php`, `admin/formulario-mascota.php`, `admin/clientes.php`, `admin/formulario-cliente.php`) conectadas a MySQL mediante PDO y consultas preparadas. El CRUD completo de clientes y mascotas (incluyendo el campo manual de **Raza** y la edad detallada en años y meses), el inicio y cierre de sesión real por rol (`login.php`, `includes/sesion.php`, `logout.php`), el borrado lógico (`estado = 'Activo' / 'Inactivo'`), la base de datos activa (`registro_mascotas`) y la interfaz renovada (Modo Oscuro con `localStorage`, colores de identidad por módulo, notificaciones flotantes Toast y estados vacíos ilustrados) ya se encuentran 100% funcionales.

## Qué puedes probar ahora
| Área | Disponible | Límite actual |
|---|---|---|
| Inicio público (`index.php`) | Presentación, servicios, misión, visión, objetivos, siete imágenes y Modo Oscuro | No hay citas ni video |
| Contacto (`contacto.php`) | Formulario público conectado a MySQL (`mensajes_contacto`) con notificación Toast | No envía correo SMTP externo |
| Login (`login.php`) | Autenticación real contra MySQL (`password_verify`), redirección por rol y aviso Toast | Sin bloqueo por intentos fallidos |
| Registro público (`registro.php`) | Creación real de cuenta de cliente en MySQL (`password_hash`) e inicio de sesión automático | Sin verificación por correo |
| Cierre de sesión (`logout.php`) | Limpieza de `$_SESSION`, expiración de cookie, `session_destroy()` y redirección a `login.php` | Operativo en todos los paneles |
| Recuperación (`recuperar-contrasena.php`) | Campo de correo, enlace de regreso y soporte de Modo Oscuro | No envía enlaces ni correos |
| Cambio de contraseña (`cambiar-contrasena.php`) | Protegido por sesión; verifica contraseña actual (`password_verify`) y actualiza hash en MySQL | Requiere sesión activa |
| Inicio del cliente (`cliente/panel.php`) | Protegido por rol `cliente`; bienvenida, contador real y tarjetas dinámicas con especie, raza y edad detallada | Conectado a BD |
| Mis mascotas (`cliente/mis-mascotas.php`) | Protegido por rol `cliente`; tarjetas dinámicas con especie, raza y edad detallada, filtro y estado vacío ilustrado | Conectado a BD |
| Mi perfil (`cliente/perfil.php`) | Protegido por rol `cliente`; consulta y edición real desde `$_SESSION` con botón original `"Editar mis datos"` | Conectado a BD |
| Inicio del administrador (`admin/panel.php`) | Protegido por rol `admin`; indicadores reales (mascotas, clientes, especies) y tabla dinámica | Conectado a BD |
| Mascotas del administrador (`admin/mascotas.php`) | Protegido por rol `admin`; tabla dinámica (`INNER JOIN`) con columna Raza, filtro por especie, edición y borrado lógico | Conectado a BD |
| Formulario de mascotas (`admin/formulario-mascota.php`) | Protegido por rol `admin`; alta y edición en MySQL con nombre, especie, campo de texto Raza (`:raza`), edad en años/meses y dueño | Conectado a BD |
| Clientes del administrador (`admin/clientes.php`) | Protegido por rol `admin`; tabla dinámica con conteo de mascotas (`LEFT JOIN`), búsqueda y edición | Conectado a BD |
| Formulario de clientes (`admin/formulario-cliente.php`) | Protegido por rol `admin`; alta y edición real en MySQL con validación de correo único y `password_hash` | Conectado a BD |

## Pendientes en orden recomendado
- [x] Construir `admin/formulario-mascota.php`: nombre, especie, edad detallada (años y meses) y dueño desde BD.
- [x] Diseñar y crear MySQL, relaciones, columna `raza`, borrado lógico (`estado`) e `import.sql`.
- [x] Crear conexión PDO en `includes/conexion.php` y aislar credenciales en `includes/config.php` con `.gitignore`.
- [x] Conectar `admin/formulario-mascota.php` (INSERT/UPDATE preparado) y `admin/mascotas.php` (SELECT con JOIN y borrado lógico).
- [x] Conectar `admin/formulario-cliente.php`, `admin/clientes.php` y `admin/panel.php` con consultas preparadas PDO.
- [x] Habilitar edición y borrado lógico (`estado = 'Inactivo'`) de mascotas y edición de clientes.
- [x] Implementar acceso (`login.php`), registro (`registro.php`), cierre (`logout.php`) y sesiones estrictas por rol (`includes/sesion.php`).
- [x] Conectar las pantallas del cliente (`cliente/panel.php`, `cliente/mis-mascotas.php`, `cliente/perfil.php`) y `cambiar-contrasena.php` con MySQL.
- [x] Implementar Modo Oscuro, identidad por módulos, Toasts, estados vacíos ilustrados, `contacto.php` y auditoría de seguridad/QA.
- [ ] Implementar recuperación con enlace temporal y envío de correo si se mantiene esa ampliación.
- [ ] Preparar diagrama ER, capturas, instalación desde cero y documento final para el profesor.

## Fuera del alcance implementado
Citas, video, pagos, vacunas e historial clínico no se han desarrollado. Evaluar ampliaciones después de completar los requisitos del registro de mascotas.

## Cómo retomar el trabajo
1. Iniciar Apache y MySQL en XAMPP (o usar el servidor activo en `http://localhost:8000`).
2. Abrir `http://localhost:8000/index.php` (o `http://localhost/registro-mascotas/index.php`).
3. Iniciar sesión en `login.php` con las cuentas de prueba (`admin@veterinaria.com` o `laura@example.com`, contraseña `12345678`).
4. Revisar la siguiente tarea de esta lista.
5. Consultar `organizacion-estilos.md` antes de modificar CSS.
6. Al terminar, registrar actividad, archivos, pruebas reales y próximo paso en `bitacora.md`.
