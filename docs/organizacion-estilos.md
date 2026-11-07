# Organización de estilos y pantallas

Los CSS se cargan con versión automática. Los compartidos van primero y los específicos después.
Cada archivo tiene separadores que indican la pantalla o el componente al que pertenece cada sección.

| Archivo CSS | Uso |
|---|---|
| `panel.css` | Base compartida por todos los paneles de cliente y administrador. Cargar primero. |
| `cliente-mascotas.css` | Exclusivo de cliente/panel.php y cliente/mis-mascotas.php. Cargar después de panel.css. |
| `cliente-perfil.css` | Exclusivo de cliente/perfil.php. Cargar después de panel.css. |
| `admin.css` | Base compartida de administración. Cargar después de panel.css y antes del CSS de cada pantalla. |
| `admin-panel.css` | Exclusivo de admin/panel.php. |
| `admin-clientes.css` | Exclusivo de admin/clientes.php. |
| `admin-mascotas.css` | Exclusivo de admin/mascotas.php. |
| `admin-formularios.css` | Compartido por formularios administrativos. Actualmente formulario-cliente.php; reutilizable para mascotas. |
| `estilos.css` | Base compartida de login.php, registro.php, recuperar-contrasena.php y cambiar-contrasena.php. |
| `cambiar-contrasena.css` | Exclusivo de cambiar-contrasena.php. Cargar después de estilos.css. |
| `inicio.css` | Exclusivo de index.php. Contiene navegación, presentación, tarjetas e imágenes del sitio público. |

## Cómo modificar una pantalla

- Menú lateral, estructura o filtros compartidos: `panel.css`.
- Inicio del administrador: `admin-panel.css`.
- Listado de clientes del administrador: `admin-clientes.css`.
- Listado de mascotas del administrador: `admin-mascotas.css`.
- Formulario de clientes y futuros formularios administrativos: `admin-formularios.css`.
- Tarjetas de mascotas del cliente: `cliente-mascotas.css`.
- Perfil del cliente: `cliente-perfil.css`.
- Cambio de contraseña: `cambiar-contrasena.css` para sus opciones y mensajes; `estilos.css` para su tarjeta y fondo comunes.
- No se crean archivos vacíos para login, registro o recuperación: actualmente comparten el mismo diseño de `estilos.css`.

## JavaScript

Los scripts se cargan con `defer` para ejecutarse después de construir el HTML. Los filtros solo buscan entre las filas cargadas. `principal.js` dibuja huellas, no autentica usuarios.

## Pendientes de desarrollo

- `admin/formulario-mascota.php` está reservado para la próxima práctica.
- Login, registro y recuperación conservan botones de demostración sin procesamiento.
- Crear, editar y eliminar registros, autenticación, sesiones, permisos y recuperación por enlace temporal están pendientes de la etapa de PHP y MySQL.
- Los componentes de `includes` están reservados; aún no se usan.
- `db/import.sql` se mantiene vacío por indicación del usuario.
