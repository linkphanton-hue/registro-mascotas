# 🐾 Cuidado de tus Mascotas — Sistema de Registro y Gestión Veterinaria

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0%20%2F%20MariaDB-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![PDO](https://img.shields.io/badge/Database-PDO%20Prepared%20Statements-F29111?style=for-the-badge&logo=databricks&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-Sem%C3%A1ntico-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-Variables%20%26%20Dark%20Mode-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6%2B%20Vanilla-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![XAMPP Ready](https://img.shields.io/badge/XAMPP-Compatible-FB7A24?style=for-the-badge&logo=xampp&logoColor=white)

---

## 📋 Descripción del Proyecto

**Cuidado de tus Mascotas** es una aplicación web integral diseñada para optimizar los procesos de atención, control de pacientes y programación de citas en clínicas veterinarias. 

El sistema resuelve la dispersión de información y la gestión manual mediante una plataforma centralizada que conecta a los dueños de mascotas (clientes) con el personal veterinario (administradores). Ofrece un registro clínico detallado (especie, raza, edad en años y meses), catálogo de servicios médicos, agenda de turnos en formato de 24 horas, centro de notificaciones dentro de la plataforma y una bandeja de contacto con respuesta rápida.

---

## ✨ Características Principales

### 🔐 Autenticación y Control de Acceso por Roles (RBAC)
- **Roles diferenciados:** Acceso protegido para `admin` y `cliente` con control de sesión estricto en `includes/sesion.php`.
- **Prevención de Session Fixation:** Regeneración de identificadores de sesión (`session_regenerate_id(true)`) tras autenticación exitosa.
- **Persistencia prolongada y Heartbeat:** Recolección de basura configurada a 24 horas (`gc_maxlifetime = 86400`), cookies de sesión vivas mientras el navegador esté abierto y un latido (*heartbeat*) periódico cada 5 minutos en segundo plano para evitar cierres de sesión accidentales mientras el usuario navega.
- **Redirección inteligente:** Si un usuario con sesión activa intenta ingresar a `login.php`, es redirigido automáticamente a su panel correspondiente.
- **Botón de retorno:** Botón `← Volver al sitio` con alto contraste en el formulario de login.

### 🛡️ Panel de Administración (`admin/`)
- **Dashboard analítico:** Tarjetas de métricas dinámicas que contabilizan en tiempo real clientes activos, mascotas registradas, citas pendientes y mensajes de contacto recibidos.
- **Gestión de clientes:** Listado con búsqueda instantánea (sin problemas de acentos gracias a normalización Unicode `NFD`), edición, alta manual y desactivación/reactivación lógica de cuentas.
- **Gestión de mascotas:** Control de pacientes con filtro por especie, edición de datos clínicos, registro exacto de edad (años y meses) y baja lógica sin pérdida de integridad referencial.
- **Catálogo de servicios veterinarios:** Activación y desactivación dinámica de los servicios que se ofertan al cliente (consultas, vacunación, desparasitación, cirugías, etc.).
- **Bandeja de mensajes de contacto:** Interfaz tipo bandeja de entrada inspirada en estándares UI/UX modernos, con vista previa tabular limpia y una **ventana modal dinámica** (`#modal-mensaje`) alimentada por atributos HTML5 `data-*` para leer el cuerpo completo del mensaje sin recargar la página.

### 🐾 Panel del Cliente (`cliente/`)
- **Mi Panel:** Resumen del perfil, accesos rápidos y visualización de mascotas vinculadas.
- **Mis Mascotas:** Registro dinámico de nuevas mascotas (nombre, especie, raza, edad en años y meses) y consulta de expedientes con filtrado instantáneo.
- **Catálogo de Servicios:** Exploración visual de los servicios veterinarios activos con botón directo para solicitar cita con el servicio preseleccionado.
- **Centro de Notificaciones:** Notificaciones internas dentro de la plataforma que alertan en tiempo real cuando una cita ha sido confirmada, modificada o atendida.

### 📅 Sistema Unificado de Citas Veterinarias
- **Arquitectura UI/UX moderna:** Tabla resumen sin inputs amontonados ni scroll horizontal forzado, complementada con un modal dinámico de gestión (`#modal-cita`).
- **Estados de cita claros:** Flujo completo con estados detallados:
  - 🟡 `Pendiente`: Solicitud enviada por el cliente pendiente de revisión.
  - 🟢 `Confirmada`: Cita agendada y aprobada por el administrador.
  - 🔵 `Confirmada (Editada)`: Cita modificada en horario o servicio manteniendo trazabilidad.
  - 🟠 `Cambio solicitado`: Solicitud de reprogramación por parte del cliente.
  - 🔴 `Rechazada`: Solicitud no admitida con motivo especificado.
  - ⚫ `Cancelada`: Cita anulada por el cliente o el administrador.
- **Horario estricto de 24 horas:** Estandarización de agenda en bloques horarios de 24 horas (`08:00 - 09:00`, `09:00 - 10:00`, etc.), eliminando ambigüedades de formato AM/PM.
- **Edición y eliminación completa:** Tanto el administrador como el cliente pueden editar libremente los datos de la cita (mascota, servicio, fecha, horario, motivo) o eliminar citas obsoletas o pasadas para mantener limpio el historial.
- **Notificaciones en plataforma:** Eliminación total de dependencias de correo electrónico externo para citas; la comunicación y avisos se realizan mediante alertas discretas y notificaciones Toast dentro del portal web.

### 🎨 Experiencia Visual y Accesibilidad
- **Modo Oscuro / Modo Claro:** Selector accesible con persistencia en `localStorage`, respetando las preferencias del sistema operativo (`prefers-color-scheme`).
- **Paleta de alto contraste:** Variables CSS cuidadosamente calibradas en `assets/css/tema.css` para ofrecer una lectura cómoda y descansada en ambos modos.
- **Notificaciones Toast no intrusivas:** Mensajes flotantes animados para confirmar altas, ediciones, bajas y cambios de estado.
- **Video de presentación inteligente:** Integrado en la página de inicio, responsivo (encuadre vertical), con reproducción automática silenciosa al entrar en pantalla y pausa automática al salir del viewport, respetando la directiva de accesibilidad `prefers-reduced-motion`.

### 🔒 Seguridad y Estándares de Ingeniería
- **Capa de persistencia con PDO:** Consultas preparadas nativas (`PDO::ATTR_EMULATE_PREPARES => false`) con tipado de parámetros para neutralizar inyecciones SQL.
- **Borrado lógico:** Implementación de banderas de `estado` (`Activo` / `Inactivo`) para preservar el historial clínico y la integridad referencial de claves foráneas.
- **Documentación de código:** 100% de los módulos en PHP y JavaScript documentados con bloques analíticos bajo estándares **PHPDoc** y **JSDoc**.

---

## 📁 Estructura del Proyecto

```text
Registro-mascotas/
├── admin/                    # Panel administrativo
│   ├── citas.php             # Gestión y resolución de citas veterinarias
│   ├── clientes.php          # Listado, búsqueda y control de estado de clientes
│   ├── formulario-cliente.php# Alta y edición de cuentas de clientes
│   ├── formulario-mascota.php# Registro y edición clínica de mascotas
│   ├── mascotas.php          # Catálogo general de mascotas registradas
│   ├── mensajes.php          # Bandeja de mensajes de contacto con modal dinámico
│   ├── panel.php             # Métricas principales y accesos rápidos
│   └── servicios.php         # Activación y desactivación de servicios
├── api/                      # Endpoints asíncronos y APIs internas
│   └── horarios-citas.php    # Consulta de disponibilidad de turnos en 24h
├── assets/                   # Recursos estáticos de la aplicación
│   ├── css/                  # Hojas de estilo modularizadas (tema, citas, paneles)
│   ├── img/                  # Logotipos, íconos y fotografías del sistema
│   ├── js/                   # Controladores Vanilla JS (interfaz, modales, filtros)
│   └── video/                # Video de presentación institucional del home
├── cliente/                  # Portal de autoservicio para dueños de mascotas
│   ├── citas.php             # Solicitud, consulta, edición y eliminación de citas
│   ├── mis-mascotas.php      # Consulta y registro de mascotas del cliente
│   ├── notificaciones.php    # Centro de notificaciones de citas y avisos
│   ├── panel.php             # Panel principal de bienvenida y resumen
│   ├── perfil.php            # Consulta de datos de cuenta y acceso a clave
│   └── servicios.php         # Catálogo interactivo de servicios veterinarios
├── db/                       # Scripts y esquemas de base de datos
│   ├── citas.sql             # Estructura del módulo de citas y agenda
│   ├── contrasenas-practica.sql # Soporte para contraseñas de entorno académico
│   ├── import.sql            # Script maestro DDL y datos iniciales de prueba
│   ├── notificaciones-citas.sql # Tablas de notificaciones internas
│   └── servicios-veterinarios.sql # Catálogo inicial de servicios
├── docs/                     # Documentación técnica y bitácoras del proyecto
│   ├── bitacora.md           # Historial cronológico de desarrollo (firmado por David)
│   ├── estado-proyecto.md    # Matriz de estado de requerimientos y módulos
│   ├── manual.md             # Manual técnico, arquitectura y flujos de usuario
│   ├── manual.pdf            # Versión imprimible del manual de usuario
│   └── organizacion-estilos.md # Guía de tokens de diseño, CSS y modo oscuro
├── includes/                 # Componentes transversales y lógica del backend
│   ├── avisos-citas.php      # Renderizado de alertas y disparadores Toast de citas
│   ├── citas.php             # Lógica compartida de citas (tablas y modales)
│   ├── conexion.php          # Instancia PDO singleton y manejo seguro de errores
│   ├── config.php            # Credenciales de base de datos (ignorado en git)
│   ├── contrasenas.php       # Funciones de verificación y actualización de claves
│   ├── formato.php           # Formateo de fechas, horas y textos
│   ├── horarios-citas.php    # Generación y validación de turnos en formato 24h
│   ├── notificaciones-citas.php # Persistencia de notificaciones en plataforma
│   ├── servicios.php         # Consultas auxiliares para el catálogo de servicios
│   └── sesion.php            # Manejo de sesiones, seguridad RBAC y keep-alive
├── cambiar-contrasena.php    # Formulario público de actualización de credenciales
├── cerrar-sesion.php         # Redirección rápida de cierre de sesión
├── contacto.php              # Formulario público de contacto con horarios 24h
├── index.php                 # Página principal del sitio con servicios y video
├── login.php                 # Pantalla de acceso con botón de retorno y roles
├── logout.php                # Destrucción segura de sesiones
├── recuperar-contrasena.php  # Solicitud de restablecimiento de contraseña
└── registro.php              # Registro público de nuevos clientes
```

---

## 🛠️ Tecnologías y Herramientas

| Componente | Tecnología | Uso en el Sistema |
|---|---|---|
| **Backend** | PHP 8.x | Lógica del servidor, validación de formularios, sesiones RBAC |
| **Persistencia** | MySQL / MariaDB | Almacenamiento relacional con integridad referencial |
| **Capa de Datos** | PDO (PHP Data Objects) | Consultas preparadas nativas contra inyecciones SQL |
| **Frontend** | HTML5 Semántico | Estructura accesible y formularios enriquecidos |
| **Estilos** | CSS3 Vanilla + Custom Properties | Variables de diseño, soporte de temas (Modo Claro/Oscuro), grid y flexbox |
| **Interacción** | JavaScript (ES6+ Vanilla) | Modales dinámicos (`data-*`), buscador instantáneo, filtrado de agenda y Toasts |
| **Entorno Local** | XAMPP | Servidor web Apache y servidor de base de datos MySQL |

---

## 🚀 Instalación y Despliegue Local (con XAMPP)

Sigue estos sencillos pasos para ejecutar el proyecto en tu entorno local:

### 1. Prerrequisitos
- Tener instalado [XAMPP](https://www.apachefriends.org/es/index.html) con **PHP 8.0 o superior** y **MySQL/MariaDB**.
- Navegador web moderno (Google Chrome, Brave, Microsoft Edge o Firefox).

### 2. Clonar o ubicar el repositorio
Descarga o clona el repositorio dentro de la carpeta `htdocs` de XAMPP:

```bash
cd C:\xampp\htdocs
git clone https://github.com/tu-usuario/Registro-mascotas.git
```

> *Alternativa:* Puedes ubicar la carpeta del proyecto en cualquier directorio y levantar el servidor integrado de PHP:
> ```bash
> cd "c:\Antigravity Projects\Registro-mascotas"
> php -S localhost:8000
> ```

### 3. Iniciar servicios en XAMPP
Abre el **Panel de Control de XAMPP** e inicia los módulos:
- **Apache**
- **MySQL**

### 4. Importar la Base de Datos
1. Abre tu navegador y dirígete a **phpMyAdmin**: [http://localhost/phpmyadmin](http://localhost/phpmyadmin).
2. Crea una nueva base de datos llamada `registro_mascotas` con cotejamiento `utf8mb4_unicode_ci` (opcional, el script la crea automáticamente).
3. Ve a la pestaña **Importar**, selecciona el archivo maestro [`db/import.sql`](file:///c:/Antigravity%20Projects/Registro-mascotas/db/import.sql) de este repositorio y presiona **Ejecutar**.
4. El script creará todas las tablas (`usuarios`, `mascotas`, `mensajes_contacto`, `servicios_veterinarios`, `citas`, `notificaciones_citas`, `agenda_citas`) con sus relaciones y datos de ejemplo.

### 5. Configurar la Conexión (`includes/config.php`)
Crea o verifica el archivo `includes/config.php` en la raíz del proyecto con tus credenciales de base de datos locales:

```php
<?php
defined('DB_HOST')    || define('DB_HOST', 'localhost');
defined('DB_NAME')    || define('DB_NAME', 'registro_mascotas');
defined('DB_USER')    || define('DB_USER', 'root');
defined('DB_PASS')    || define('DB_PASS', '');
defined('DB_CHARSET') || define('DB_CHARSET', 'utf8mb4');
```

> **Nota de Seguridad:** El archivo `includes/config.php` está protegido en `.gitignore` para no exponer credenciales locales en el repositorio.

### 6. Ejecutar y Probar el Sistema
Abre tu navegador e ingresa a:
- Vía Apache en XAMPP: [http://localhost/Registro-mascotas/index.php](http://localhost/Registro-mascotas/index.php)
- Vía servidor PHP embebido: [http://localhost:8000/index.php](http://localhost:8000/index.php)

---

## 🔑 Cuentas de Acceso de Prueba

El script `db/import.sql` inicializa dos cuentas preparadas para evaluar todos los roles del sistema:

| Rol | Correo Electrónico | Contraseña | Panel Asignado |
|---|---|---|---|
| **Administrador** | `admin@veterinaria.com` | `12345678` | `/admin/panel.php` |
| **Cliente** | `laura@example.com` | `12345678` | `/cliente/panel.php` |

---

## 📚 Documentación Adicional

En la carpeta [`docs/`](file:///c:/Antigravity%20Projects/Registro-mascotas/docs/) se encuentran disponibles los manuales y registros técnicos detallados del proyecto:

- 📖 **[Manual de Usuario y Arquitectura Técnica](file:///c:/Antigravity%20Projects/Registro-mascotas/docs/manual.md):** Guía completa de uso, explicación detallada de flujos, sentencias PDO, arquitectura del sistema y esquema de base de datos.
- 📝 **[Bitácora de Desarrollo](file:///c:/Antigravity%20Projects/Registro-mascotas/docs/bitacora.md):** Historial cronológico con los avances, objetivos, lecciones aprendidas y resultados de cada jornada de desarrollo (David).
- 🎨 **[Organización de Estilos y Temas](file:///c:/Antigravity%20Projects/Registro-mascotas/docs/organizacion-estilos.md):** Documentación de variables CSS, colores por módulo, reglas del Modo Oscuro y componentes UI (Toasts, modales).
- 📊 **[Estado del Proyecto](file:///c:/Antigravity%20Projects/Registro-mascotas/docs/estado-proyecto.md):** Resumen ejecutivo del avance y matriz de cumplimiento de requerimientos funcionales.

