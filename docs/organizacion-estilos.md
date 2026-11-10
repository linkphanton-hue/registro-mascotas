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

---

## Actualización del 9 de noviembre de 2026: Sistema de diseño, Modo Oscuro, Identidad por Módulos y Componentes Interactivos

En la sesión del 9 de noviembre de 2026 se incorporó la hoja de estilos transversal `assets/css/tema.css` (cargada al final de cada documento mediante `filemtime()`) y el script `assets/js/interfaz.js`, sin modificar las clases ni la estructura visual previa.

### 1. Nuevos archivos de estilos y scripts incorporados hoy

| Archivo | Propósito |
|---|---|
| `assets/css/tema.css` | Variables globales (`:root` y `html[data-theme="dark"]`), paleta de identidad por módulo (`data-modulo`), microinteracciones hover, notificaciones flotantes (`.toast`), estados vacíos ilustrados (`.estado-vacio-ilustrado`) y estilos de la sección `contacto.php`. |
| `assets/js/interfaz.js` | Conmutador de Modo Claro/Oscuro con persistencia en `localStorage`, generador de notificaciones Toast (por atributos `data-toast-mensaje` y `MutationObserver` sobre avisos de formulario) y confirmación de baja en dos pasos (`data-confirmar`). |
| `assets/js/formulario-mascota.js` | Validación en navegador para `admin/formulario-mascota.php`: nombre obligatorio, especie válida, raza obligatoria (`#raza`), edad en años (`0–100`) y meses (`0–11`), y selección de cliente dueño. |

### 2. Variables CSS y Modo Oscuro (`:root` y `html[data-theme="dark"]`)

El sistema de temas se define en `assets/css/tema.css` mediante propiedades personalizadas de CSS:

- **Tema claro (`:root`):**
  - `--bg-pagina`: `#f3f1fb`
  - `--bg-superficie`: `#ffffff`
  - `--bg-superficie-alt`: `#f8f6fe`
  - `--texto-principal`: `#221b3f`
  - `--texto-secundario`: `#564f72`
  - `--borde-suave`: `#e3def6`
  - `--sombra-tarjeta`: `0 12px 26px rgba(45, 31, 96, 0.07)`
- **Tema oscuro (`html[data-theme="dark"]`):**
  - Evita el negro puro (`#000000`) y utiliza tonos grisáceos azulados oscuros que armonizan con los acentos morados y azules del proyecto:
  - `--bg-pagina`: `#1e1e2f`
  - `--bg-superficie`: `#252538`
  - `--bg-superficie-alt`: `#2d2d44`
  - `--bg-lateral`: `#181826`
  - `--texto-principal`: `#f1effa`
  - `--texto-secundario`: `#b8b2d1`
  - `--borde-suave`: `#383854`
  - `--sombra-tarjeta`: `0 14px 30px rgba(8, 8, 18, 0.42)`
- **Persistencia y transición:**
  - Un script en línea en el `<head>` lee `localStorage.getItem('tema_preferido')` antes del primer pintado para asignar `data-theme="dark"` en `<html>` sin parpadeo inicial.
  - Se aplica `transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;` en `body` y contenedores principales para suavizar el cambio de tema.

### 3. Paleta de colores e identidad por módulo (`data-modulo`)

Cada vista de panel define el atributo `data-modulo` en `<body>` para diferenciar visualmente el módulo activo mediante un borde superior de `4px solid var(--acento-modulo)` en tarjetas (`.tarjeta-resumen`, `.acceso-rapido`, `.tarjeta-mascota`, `.bloque-panel`, `.bloque-formulario`) y color de acento en etiquetas (`.etiqueta-seccion`) y enlaces activos:

| Módulo (`body[data-modulo]`) | Pantallas donde aplica | Color de acento (`--acento-modulo`) | Fondo suave (`--acento-modulo-suave`) |
|---|---|---|---|
| `panel` | `admin/panel.php`, `cliente/panel.php`, `cliente/perfil.php`, `contacto.php` | Violeta (`#7355ed` claro / `#947bf6` oscuro) | `rgba(115, 85, 237, 0.12)` |
| `mascotas` | `admin/mascotas.php`, `admin/formulario-mascota.php`, `cliente/mis-mascotas.php` | Verde agua (`#0d9488` claro / `#2dd4bf` oscuro) | `rgba(13, 148, 136, 0.13)` |
| `clientes` | `admin/clientes.php`, `admin/formulario-cliente.php` | Naranja cálido (`#ea580c` claro / `#fb923c` oscuro) | `rgba(234, 88, 12, 0.13)` |

### 4. Microinteracciones y efectos Hover

Las tarjetas informativas e interactivas (`.tarjeta-resumen`, `.acceso-rapido`, `.tarjeta-mascota`, `.tarjeta-destacada`, `.tarjeta-servicio`, `.tarjeta-institucional`, `.tarjeta-contacto-info`) incorporan elevación suave al pasar el cursor:
- **Estado base:** `transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease, border-color 0.3s ease;`
- **Estado `:hover`:** `transform: translateY(-5px);` acompañado de `--sombra-tarjeta-hover` (`0 18px 34px rgba(45, 31, 96, 0.14)` en claro y `0 20px 38px rgba(5, 5, 14, 0.6)` en oscuro).

### 5. Estructura de las Notificaciones Flotantes (Toasts) y Estados Vacíos

- **Contenedor y tarjetas Toast (`.contenedor-toasts` y `.toast`):**
  - Se inyecta dinámicamente un `<div class="contenedor-toasts" aria-live="polite" aria-atomic="true"></div>` fijo en la esquina inferior derecha (`position: fixed; right: 24px; bottom: 24px; z-index: 9999`).
  - Cada notificación `.toast` muestra un indicador circular (`.toast-icono`), el texto del mensaje y un borde lateral de `4px` según el estado: verde esmeralda (`#10b981`) para `.toast-exito` y rojo coral (`#ef4444`) para `.toast-error`.
  - Se anima mediante `.toast.visible` (`opacity: 1; transform: translateY(0)`) y se retira automáticamente tras `3200 ms`.
  - Puede dispararse desde PHP mediante los atributos `data-toast-mensaje` y `data-toast-tipo` en `<body>` (usado en `login.php`, `registro.php` y redirecciones con `?eliminado=1`) o automáticamente cuando un contenedor `.mensaje-formulario` cambia su contenido gracias a `MutationObserver` en `assets/js/interfaz.js`.
- **Confirmación de borrado en dos pasos (`button[data-confirmar]`):**
  - En lugar de utilizar `confirm()` o `alert()` nativos, el primer clic sobre el botón de eliminar añade la clase `.confirmando-baja`, muestra el texto `"¿Confirmar?"` y lanza un Toast preventivo; el segundo clic dentro de los 3.5 segundos envía el formulario `POST` de baja lógica.
- **Estados vacíos ilustrados (`.estado-vacio-ilustrado`):**
  - Cuando una tabla o listado carece de registros o coincidencias de filtro, se presenta un contenedor centrado con borde discontinuo, icono SVG en `.icono-vacio`, título `.titulo-vacio` y descripción `.texto-vacio`.
