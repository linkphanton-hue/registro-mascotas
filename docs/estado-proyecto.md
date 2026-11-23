# Estado del proyecto

Actualizado el 23 de noviembre de 2026.

## Dónde nos quedamos
El sistema usa PHP y PDO con la base `registro_mascotas` de XAMPP. Los clientes pueden dar de alta sus mascotas, consultar el catálogo y solicitar citas. El administrador gestiona la agenda, la disponibilidad de los servicios y el acceso de los clientes.

## Qué puedes probar ahora
| Área | Disponible | Límite actual |
|---|---|---|
| Inicio público (`index.php`) | Presentación, servicios, video destacado, misión, visión, objetivos y modo oscuro | La agenda está en el panel del cliente |
| Contacto (`contacto.php`) | Formulario público conectado a MySQL (`mensajes_contacto`) con notificación Toast y lectura en `admin/mensajes.php` | No envía correo SMTP automático |
| Login (`login.php`) | Verificación de cuentas activas, compatibilidad con contraseñas antiguas y acceso por rol | Sin bloqueo por intentos fallidos |
| Registro público (`registro.php`) | Creación real de cuenta de cliente en MySQL con contraseña en texto plano para la práctica e inicio de sesión automático | Sin verificación por correo |
| Cierre de sesión (`logout.php`) | Limpieza de `$_SESSION`, expiración de cookie, `session_destroy()` y redirección a `login.php` | Operativo en todos los paneles |
| Recuperación (`recuperar-contrasena.php`) | Campo de correo, enlace de regreso y soporte de Modo Oscuro | No envía enlaces ni correos |
| Cambio de contraseña (`cambiar-contrasena.php`) | Protegido por sesión; verifica la contraseña actual y guarda la nueva en texto plano | Requiere sesión activa |
| Inicio del cliente (`cliente/panel.php`) | Protegido por rol `cliente`; bienvenida, contador real y tarjetas dinámicas con especie, raza y edad detallada | Conectado a BD |
| Mis mascotas (`cliente/mis-mascotas.php`) | Protegido por rol `cliente`; alta de mascotas propias, listado y filtro por especie | Conectado a BD |
| Mi perfil (`cliente/perfil.php`) | Protegido por rol `cliente`; consulta y edición real desde `$_SESSION` con botón original `"Editar mis datos"` | Conectado a BD |
| Inicio del administrador (`admin/panel.php`) | Protegido por rol `admin`; indicadores reales (mascotas, clientes, especies) y tabla dinámica | Conectado a BD |
| Mascotas del administrador (`admin/mascotas.php`) | Protegido por rol `admin`; tabla dinámica (`INNER JOIN`) con columna Raza, filtro por especie, edición y borrado lógico | Conectado a BD |
| Formulario de mascotas (`admin/formulario-mascota.php`) | Protegido por rol `admin`; alta y edición en MySQL con nombre, especie, campo de texto Raza (`:raza`), edad en años/meses y dueño | Conectado a BD |
| Clientes del administrador (`admin/clientes.php`) | Protegido por rol `admin`; listado, búsqueda, filtro por estado, edición y desactivación reversible | Conectado a BD |
| Formulario de clientes (`admin/formulario-cliente.php`) | Protegido por rol `admin`; alta y edición real en MySQL con validación de correo único y contraseña en texto plano para la práctica | Conectado a BD |
| Mensajes de contacto (`admin/mensajes.php`) | Protegido por rol `admin`; bandeja dinámica desde `mensajes_contacto`, búsqueda instantánea y botón de respuesta directa en Gmail | Abre compositor web de Gmail en nueva pestaña |
| Servicios (`cliente/servicios.php` y `admin/servicios.php`) | Catálogo con ocho servicios iniciales y control de disponibilidad por el administrador | Solo los servicios activos se ofrecen en nuevas citas |
| Citas (`cliente/citas.php` y `admin/citas.php`) | Solicitud con mascota, servicio y turno preferido, confirmación y reprogramación | El administrador aprueba el horario |
| Notificaciones de citas | Avisos de confirmación en el portal | Envío por correo sujeto a la configuración local |

## Seguimiento
- [x] Construir `admin/formulario-mascota.php`: nombre, especie, edad detallada (años y meses) y dueño desde BD.
- [x] Diseñar y crear MySQL, relaciones, columna `raza`, borrado lógico (`estado`) e `import.sql`.
- [x] Crear conexión PDO en `includes/conexion.php` y aislar credenciales en `includes/config.php` con `.gitignore`.
- [x] Conectar `admin/formulario-mascota.php` (INSERT/UPDATE preparado) y `admin/mascotas.php` (SELECT con JOIN y borrado lógico).
- [x] Conectar `admin/formulario-cliente.php`, `admin/clientes.php` y `admin/panel.php` con consultas preparadas PDO.
- [x] Habilitar edición y borrado lógico (`estado = 'Inactivo'`) de mascotas y edición de clientes.
- [x] Implementar acceso (`login.php`), registro (`registro.php`), cierre (`logout.php`) y sesiones estrictas por rol (`includes/sesion.php`).
- [x] Conectar las pantallas del cliente (`cliente/panel.php`, `cliente/mis-mascotas.php`, `cliente/perfil.php`) y `cambiar-contrasena.php` con MySQL.
- [x] Implementar Modo Oscuro, identidad por módulos, Toasts, estados vacíos ilustrados, `contacto.php` y auditoría de seguridad/QA.
- [x] Implementar bandeja administrativa de mensajes (`admin/mensajes.php`) con buscador instantáneo y redirección directa al compositor web de Gmail.
- [ ] Implementar recuperación con enlace temporal y envío de correo si se mantiene esa ampliación.
- [x] Integrar el video vertical en el inicio y comprobar su reproducción en escritorio y móvil.
- [x] Implementar el registro de mascotas por el cliente, el catálogo y la selección de servicio en las citas.
- [x] Aplicar en la base local las migraciones de servicios y contraseñas de práctica.
- [x] Verificar la desactivación y reactivación sin perder mascotas ni citas.
- [x] Documentar la instalación desde cero y las relaciones del esquema.
- [ ] Incorporar al material de entrega la imagen del diagrama preparada para la presentación.

## Fuera del alcance implementado
Los pagos, el registro clínico de vacunas y el historial médico quedan fuera del alcance actual.

## Cómo retomar el trabajo
1. Iniciar Apache y MySQL en XAMPP (o usar el servidor activo en `http://localhost:8000`).
2. Abrir `http://localhost:8000/index.php` (o `http://localhost/Proyecto-final/registro-mascotas/index.php`).
3. En una instalación nueva, iniciar sesión en `login.php` con las cuentas de prueba (`admin@veterinaria.com` o `laura@example.com`, contraseña `12345678`).
4. Revisar la siguiente tarea de esta lista.
5. Consultar `organizacion-estilos.md` antes de modificar CSS.
6. Al terminar, registrar actividad, archivos, pruebas reales y próximo paso en `bitacora.md`.
