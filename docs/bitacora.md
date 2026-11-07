# Bitácora de mi proyecto: Cuidado de tus Mascotas

Asignatura: SOF-109  
Proyecto: Registro de Mascotas  
Última actualización: 28 de octubre de 2026

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
