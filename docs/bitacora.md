# Bitácora de mi proyecto: Cuidado de tus Mascotas

Asignatura: SOF-109  
Proyecto: Registro de Mascotas  
Última actualización: 9 de noviembre de 2026

## Planificación y estructura inicial

### Mi objetivo
Organizar el proyecto antes de comenzar a programar y definir qué funciones necesito desarrollar.

### Mi avance
Elegí un sistema para registrar las mascotas atendidas en una veterinaria. Revisé los requisitos del proyecto y organicé un plan de trabajo de dos semanas.

Preparé la estructura en `C:\xampp\htdocs\Proyecto-final\registro-mascotas`, con las carpetas `admin`, `cliente`, `includes`, `assets`, `db` y `docs`. Dentro de `assets` separé los estilos, los scripts y las imágenes.

Durante la revisión inicial apareció un error en el nombre del archivo principal: estaba escrito como `indes.php` y quedó corregido a `index.php`.

Decidí comenzar con las pantallas y la navegación. Dejé la carpeta `db` preparada, pero pospuse la creación de la base de datos hasta completar esta etapa.

### Lo que aprendí
La estructura de carpetas me permite encontrar los archivos y separar las pantallas del cliente de las del administrador.

## Diseño del inicio de sesión y formularios de acceso

### Mi objetivo
Preparar una presentación clara y mantener el mismo estilo en los formularios de acceso.

### Mi avance
Escogí un diseño con colores azules y morados, una tarjeta translúcida y un fondo que cambia suavemente de color. Trabajé el HTML de `login.php` y mantuve el CSS separado.

Al probarlo, el formulario apareció sin diseño. Revisé las rutas de los estilos y la actualización del navegador. Más adelante, el proyecto incorporó versiones automáticas de los CSS para solicitar los cambios guardados.

Continué con las ondas decorativas, las huellas animadas y la reducción del tamaño de la tarjeta. También agregué enlaces para crear una cuenta y recuperar la contraseña.

Preparé las pantallas `registro.php`, `recuperar-contrasena.php` y `cambiar-contrasena.php`. Probé una decoración de letras con orejas y cola; decidí quitar las orejas porque no me gustó el resultado y conservar la cola.

### Lo que aprendí
HTML define los campos y enlaces, CSS controla su apariencia y JavaScript permite agregar comportamientos como las huellas animadas. Que el formulario se vea completo no significa que ya guarde información.

### Lo que me falta
Conectar el acceso, el registro y la recuperación con el procesamiento de PHP. Todavía no se autentican cuentas, no se envían correos y no se cambian contraseñas reales.

## Página principal del sitio

### Mi objetivo
Crear una página de presentación desde la que pueda acceder a las distintas áreas del sistema.

### Mi avance
Trabajé en `index.php` con una presentación, accesos a las cuentas, tarjetas de servicios, misión, visión, objetivos y pie de página.

Agregué imágenes y las nombré según su contenido. El inicio quedó con siete imágenes: una en la presentación y seis en las tarjetas. La imagen de Atención preventiva se incorporó después de las primeras seis.

Las rutas de las imágenes quedaron identificadas con comentarios en el HTML y el CSS.

### Lo que me falta
Por ahora dejé las citas y el video como posibles ampliaciones. Mi prioridad es completar las funciones del registro de mascotas.

## Panel del cliente

### Mi objetivo
Preparar el espacio donde el cliente consultará sus mascotas y sus datos.

### Mi avance
Trabajé en tres pantallas:

- `cliente/panel.php`: bienvenida, resumen y tarjetas con mascotas de ejemplo.
- `cliente/mis-mascotas.php`: listado con filtro por especie.
- `cliente/perfil.php`: consulta de los datos del cliente y acceso al cambio de contraseña.

Utilicé a Laura Pérez, Max y Luna como ejemplos. Estos datos todavía están escritos en HTML.

Revisé el menú lateral y el resaltado de la página actual. El menú permanece visible al desplazar la página en computadora y pasa arriba del contenido en pantallas pequeñas.

También aclaré la diferencia entre especie y raza: en los ejemplos, perro y gato corresponden al campo especie, mientras que Golden Retriever y Siamés son razas.

### Lo que aprendí
El selector se construye con HTML y JavaScript filtra las tarjetas que ya están cargadas. El menú activo y el comportamiento del lateral se resuelven con HTML y CSS.

### Lo que me falta
Mostrar los datos del cliente conectado y habilitar la edición de su perfil cuando implemente la base de datos y las sesiones.

## Panel del administrador

### Mi objetivo
Preparar las pantallas necesarias para administrar mascotas y clientes.

### Mi avance
Continué con estas pantallas:

- `admin/panel.php`: resumen, accesos rápidos y tabla de ejemplo.
- `admin/mascotas.php`: listado con filtro por especie y botones de editar y eliminar todavía deshabilitados.
- `admin/clientes.php`: listado con búsqueda por nombre o correo.
- `admin/formulario-cliente.php`: formulario con nombre, correo y contraseña inicial.

La búsqueda de clientes permite encontrar un nombre aunque escriba sin acentos. Los filtros ocultan las filas que no coinciden, pero no eliminan los datos.

### Lo que aprendí
Los filtros pueden conservarse cuando PHP genere las filas desde MySQL. PHP también se encargará de validar los datos, comprobar permisos y ejecutar las consultas necesarias.

### Lo que me falta
Construir el formulario de mascotas y después implementar el guardado, la modificación y la eliminación real de registros.

## Validaciones de los formularios

### Mi objetivo
Detectar errores en los datos antes de preparar su procesamiento en PHP.

### Mi avance
El formulario de clientes quedó con campos obligatorios, formato de correo, longitud mínima de contraseña y comparación de la confirmación. También rechaza un nombre compuesto únicamente por espacios.

En el cambio de contraseña quedaron la opción de mostrar u ocultar los caracteres, la comparación de ambas contraseñas nuevas y la comprobación de que la nueva no sea igual a la actual escrita.

### Lo que aprendí
Estas comprobaciones son visuales. La contraseña actual todavía no se verifica contra una cuenta real y los datos no se guardan. Cuando implemente PHP, también necesitaré validar los datos en el servidor.

## 27 de octubre de 2026 - Revisión y organización del código

### Mi objetivo
Revisar errores y facilitar la identificación de los estilos que pertenecen a cada pantalla.

### Mi avance
Solicité una revisión de los títulos, encabezados, enlaces y conexiones de los archivos. En esa revisión se detectaron scripts que se cargaban antes del HTML y conexiones faltantes en los formularios de clientes y cambio de contraseña.

El proyecto quedó con los scripts conectados mediante `defer`, para esperar a que existan los elementos de la página antes de ejecutar los filtros y las validaciones.

Organicé los estilos por su uso: mantuve archivos compartidos y separé los exclusivos de los paneles, listados, perfil y formularios. Cada CSS quedó dividido con comentarios de sección.

La guía de esta organización quedó en `docs/organizacion-estilos.md`.

### Resultados registrados en la revisión
- 18 archivos PHP y 6 archivos JavaScript sin errores de sintaxis.
- 12 páginas revisadas, junto con sus referencias locales y etiquetas.
- 11 archivos CSS cargados en el navegador.
- Búsquedas y filtros de cliente y administrador comprobados.
- Siete comprobaciones de validación de formularios con datos ficticios.

### Lo que me falta
Los resultados corresponden a la revisión del prototipo. Todavía necesito probar las operaciones reales cuando implemente PHP y MySQL.

## 28 de octubre de 2026 - Actualización de mi documentación

### Mi objetivo
Dejar registrado lo que llevo realizado para retomar el proyecto sin olvidar los avances y pendientes.

### Mi avance
Reuní el historial de trabajo en esta bitácora y organicé la explicación del sistema en `docs/manual.md`. También dejé un resumen en `docs/estado-proyecto.md` para identificar qué funciona y cuál es la siguiente tarea.

Organicé las actividades iniciales por etapas porque no había anotado una fecha individual para cada una. En esta actualización documental no realicé nuevas pruebas funcionales.

### Estado en el que dejo el proyecto
Tengo preparadas las pantallas públicas y las áreas del cliente y del administrador. Los filtros y algunas validaciones funcionan con datos de ejemplo. La base de datos, las sesiones, los permisos y el guardado todavía están pendientes.

### Mi siguiente paso
Continuar con `admin/formulario-mascota.php`, incluyendo nombre, especie, edad positiva y selección del dueño. Después revisaré los formularios antes de comenzar la etapa de MySQL.

## 9 de noviembre de 2026 - Conexión de base de datos, ejecución web y formulario de mascotas (David)

### Mi objetivo
Retomar el proyecto en un nuevo entorno de trabajo mediante control de versiones, vincular la base de datos MySQL con PDO, desplegar la aplicación en el servidor local y construir el formulario administrativo de mascotas con sus validaciones.

### Mi avance
Cloné el repositorio de trabajo y creé la rama `feature/mi-primer-aporte` para trabajar sin afectar `main`. Preparé la estructura inicial de la base de datos `registro_mascotas` en `db/import.sql` con las tablas `usuarios` y `mascotas`, cargué los registros iniciales de prueba en MySQL e implementé la conexión centralizada en `includes/conexion.php`.

Vinculé el proyecto al directorio de publicación de XAMPP y comprobé que todas las pantallas principales respondieran en el navegador. A continuación, construí `admin/formulario-mascota.php` manteniendo el diseño de los formularios administrativos y añadí `assets/js/formulario-mascota.js` para validar los datos antes del envío.

### Resultados registrados en la revisión
- Rama `feature/mi-primer-aporte` creada y base de datos `registro_mascotas` activa en MySQL.
- Conexión PDO en `includes/conexion.php` verificada sin advertencias ni errores.
- `admin/formulario-mascota.php` y `assets/js/formulario-mascota.js` probados en navegador con respuestas HTTP 200 OK.

### Lo que aprendí
Configurar PDO con manejo controlado de excepciones permite detectar fallos de conexión de forma temprana sin exponer errores internos al usuario ni interrumpir bruscamente la interfaz.

### Mi siguiente paso
Proteger las credenciales de conexión, incorporar el borrado lógico en la base de datos y conectar el guardado y listado real de mascotas mediante consultas preparadas.

## 9 de noviembre de 2026 - Auditoría de código, seguridad de credenciales, borrado lógico y persistencia PDO

### Mi objetivo
Auditar el código existente sin alterar la interfaz visual, aislar las credenciales de la base de datos, incorporar el borrado lógico y conectar tanto el alta como el listado de mascotas con MySQL.

### Mi avance
Revisé todas las carpetas del proyecto y corregí en `assets/js/formulario-mascota.js` el bloqueo que impedía enviar formularios válidos al servidor. Separé las credenciales de conexión en `includes/config.php` y añadí este archivo a `.gitignore` para no exponer contraseñas en el repositorio.

Actualicé `db/import.sql` y las tablas activas `usuarios` y `mascotas` en MySQL para agregar la columna `estado` y habilitar el borrado lógico. Finalmente, conecté `admin/formulario-mascota.php` para validar y guardar mascotas mediante método `POST` y consultas preparadas, y actualicé `admin/mascotas.php` para listar dinámicamente las mascotas activas junto al nombre de su dueño. Las explicaciones técnicas detalladas de PDO y del borrado lógico quedaron documentadas en `docs/manual.md`.

### Resultados registrados en la revisión
- 20 archivos PHP verificados sin errores de sintaxis.
- Credenciales aisladas en `includes/config.php` y excluidas en `.gitignore`.
- Registro y listado dinámico de mascotas activos verificados en `http://localhost:8000`.

### Lo que aprendí
Aislar las credenciales fuera del control de versiones protege el entorno de trabajo al compartir código, y combinar la validación del navegador con consultas preparadas en el servidor asegura que solo ingresen datos íntegros a la base de datos.

### Mi siguiente paso
Completar el módulo administrativo de clientes, habilitar la edición y baja lógica de mascotas, e implementar el inicio de sesión y permisos por rol.

## 9 de noviembre de 2026 - Gestión completa de clientes y mascotas, edición, borrado lógico y sesiones por rol (David)

### Mi objetivo
Conectar el CRUD administrativo de clientes, la edición y borrado lógico de mascotas, el panel de indicadores, la autenticación de usuarios con sesiones por rol y las vistas privadas del cliente.

### Mi avance
Conecté `admin/formulario-cliente.php` y `admin/clientes.php` con MySQL para permitir registrar y editar clientes con contraseñas cifradas y mostrar el conteo real de mascotas activas por dueño. En `admin/formulario-mascota.php` y `admin/mascotas.php` habilité la edición por identificador y el borrado lógico mediante `POST`, y actualicé `admin/panel.php` para mostrar conteos reales desde la base de datos.

Implementé el inicio de sesión en `login.php`, el registro público en `registro.php`, el cambio de clave en `cambiar-contrasena.php`, la validación de acceso por rol en `includes/sesion.php` y la carga dinámica de datos en `cliente/panel.php`, `cliente/mis-mascotas.php` y `cliente/perfil.php`.

### Resultados registrados en la revisión
- Rutas privadas de `admin/` y `cliente/` protegidas según el rol activo de la sesión.
- Inicio de sesión, registro de clientes, edición y baja lógica de mascotas verificados mediante peticiones `POST` contra MySQL.

### Lo que aprendí
Probar los flujos completos con sesiones reales y envíos `POST` permite confirmar que los permisos de cada rol y la actualización de registros funcionen de extremo a extremo más allá de una revisión estática.

### Mi siguiente paso
Incorporar mejoras de experiencia visual (Modo Oscuro, colores por módulo, notificaciones flotantes y estados vacíos), edad detallada en años y meses, y la página pública de contacto.

## 9 de noviembre de 2026 - Modo oscuro, identidad por módulos, notificaciones Toast, estados vacíos, edad detallada y sección de contacto (David)

### Mi objetivo
Elevar la experiencia visual y de interacción del sistema incorporando Modo Oscuro persistente, acentos de color por módulo, notificaciones flotantes (Toasts), estados vacíos ilustrados, edad en años y meses y una página de contacto conectada a la base de datos.

### Mi avance
Creé `assets/css/tema.css` y `assets/js/interfaz.js` para habilitar el cambio entre Modo Claro y Modo Oscuro con preferencia guardada en `localStorage`, diferenciar visualmente cada sección del sistema mediante colores de acento por módulo y añadir microinteracciones de elevación en las tarjetas. También implementé notificaciones flotantes (Toasts) y la confirmación de baja en dos clics para evitar alertas nativas del navegador, además de estados vacíos ilustrados cuando una tabla o filtro no tiene resultados. Toda la estructura de variables y componentes CSS se documentó en `docs/organizacion-estilos.md`.

Actualicé la base de datos y `admin/formulario-mascota.php` junto con `includes/formato.php` para registrar y mostrar la edad en años y meses, y construí la pantalla `contacto.php` conectada a la tabla `mensajes_contacto`.

### Resultados registrados en la revisión
- Cambio de tema claro/oscuro con persistencia en `localStorage` y notificaciones Toast comprobados en los paneles y formularios.
- Registro de edad en años y meses y envío de mensajes en `contacto.php` verificados en MySQL.

### Lo que aprendí
Organizar las variables de color y los estados interactivos en una hoja transversal permite renovar la apariencia y usabilidad de todo el sistema sin alterar la estructura HTML ni duplicar reglas en cada archivo CSS.

### Mi siguiente paso
Realizar una auditoría final de seguridad y QA, integrar el campo manual de Raza en todo el flujo de mascotas y reorganizar la documentación técnica del proyecto.

## 9 de noviembre de 2026 - Auditoría final de seguridad, integración completa del campo Raza y reorganización documental (David)

### Mi objetivo
Cerrar la jornada auditando la seguridad de las sesiones, revirtiendo cualquier cambio estructural sobre el HTML original, integrando la captura y visualización dinámica del campo "Raza" en mascotas y reorganizando la documentación de hoy en la carpeta `docs/`.

### Mi avance
Reforcé `includes/sesion.php` y creé `logout.php` para validar en cada petición que el usuario autenticado siga activo en la base de datos con el rol correspondiente y pueda cerrar su sesión de forma segura. Eliminé cualquier respaldo de cliente por defecto en `cliente/perfil.php` y `cambiar-contrasena.php`, y verifiqué que todas las pantallas respeten íntegramente el diseño HTML original.

Para garantizar la congruencia del dato de raza en todo el sistema, aseguré la columna `raza` en `db/import.sql` y en la tabla `mascotas` de MySQL, agregué el campo de texto `<input type="text" id="raza" name="raza">` en `admin/formulario-mascota.php` y actualicé las consultas preparadas (`INSERT` y `UPDATE`) para recibir y guardar `:raza`. Asimismo, actualicé `admin/mascotas.php` y verifiqué `cliente/panel.php` y `cliente/mis-mascotas.php` para que impriman dinámicamente la raza almacenada en la base de datos.

Finalmente, audité y reorganicé la documentación de hoy sin tocar los registros de fechas anteriores: trasladé el detalle de variables CSS, Modo Oscuro, colores por módulo y Toasts a `docs/organizacion-estilos.md`; incorporé la explicación técnica de PDO, borrado lógico, sesiones por rol y el campo Raza en `docs/manual.md`; actualicé el avance general en `docs/estado-proyecto.md` y sinteticé las entradas de hoy en esta bitácora.

### Resultados registrados en la revisión
- 25 archivos PHP verificados con `php -l` sin errores de sintaxis.
- Alta y edición de mascotas con el nuevo campo `raza` verificadas mediante `POST` en `admin/formulario-mascota.php`, comprobando su visualización dinámica en `admin/mascotas.php`, `cliente/panel.php` y `cliente/mis-mascotas.php`.
- Protección de sesiones por rol, cierre en `logout.php` y fidelidad al HTML original comprobados en todas las vistas.
- Documentación de `docs/organizacion-estilos.md`, `docs/manual.md`, `docs/estado-proyecto.md` y `docs/bitacora.md` reorganizada conservando intacto todo el historial previo al 9 de noviembre de 2026.

### Lo que aprendí
Mantener la congruencia de un dato desde la columna en MySQL hasta el formulario administrativo y las vistas del cliente evita inconsistencias entre paneles. Asimismo, separar en la documentación las especificaciones técnicas (`manual.md` y `organizacion-estilos.md`) de la narrativa de trabajo (`bitacora.md`) facilita tanto el mantenimiento del código como la lectura del historial del proyecto.

### Estado al cierre del día
El sistema de Registro de Mascotas queda completamente funcional en su base de datos, backend con PDO, CRUD de clientes y mascotas (con raza y edad en años y meses), autenticación y sesiones por rol, interfaz con Modo Oscuro y notificaciones Toast, y documentación actualizada al 9 de noviembre de 2026.
