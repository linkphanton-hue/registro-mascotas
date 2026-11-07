# Documentación del proyecto: Cuidado de tus Mascotas

Actualizada: 28 de octubre de 2026. Documento de trabajo para preparar la entrega final.

## 1. Descripción y propósito
Sistema web académico de registro de mascotas atendidas en una veterinaria. El administrador gestionará los registros y cada cliente consultará únicamente sus propias mascotas. Las pantallas actuales usan datos ficticios y todavía no aplican esos permisos.

## 2. Alcance acordado
Campos centrales de la mascota: nombre, especie, edad positiva y dueño. El administrador tendrá operaciones de crear, consultar, modificar y eliminar. El cliente tendrá consulta de sus mascotas. Se requiere filtro por especie y validaciones en navegador y servidor.

La página pública presenta servicios, misión, visión y objetivos. Registro de cuenta, perfil y recuperación son pantallas añadidas durante el desarrollo. La funcionalidad de citas y el video se propusieron, pero no se han implementado.

## 3. Tecnologías y responsabilidades
| Tecnología | Responsabilidad |
|---|---|
| HTML | Estructura, campos, tablas, enlaces y contenido |
| CSS | Colores, fondos, tamaños, menú sticky, animaciones y adaptación a pantallas |
| JavaScript | Huellas, filtros, búsqueda y validaciones visuales |
| PHP | Actualmente sirve páginas y genera versiones de CSS con filemtime; posteriormente validará sesiones, permisos y datos y ejecutará consultas |
| MySQL | Almacenamiento futuro; todavía no se ha creado ni conectado en este trabajo |
| XAMPP | Apache y entorno local de ejecución de PHP |

Las páginas con extensión PHP todavía contienen principalmente HTML. Tener la extensión no significa que los formularios ya procesen información.

## 4. Ubicación y ejecución
Ruta: `C:\xampp\htdocs\Proyecto-final\registro-mascotas`.

1. Abrir XAMPP e iniciar Apache.
2. Entrar a `http://localhost/Proyecto-final/registro-mascotas/index.php`.
3. Utilizar los enlaces de navegación pública.
4. Para probar paneles, abrir sus direcciones directamente. No existe todavía redirección de login por rol.
5. Si Apache usa otro puerto, añadirlo después de localhost.

No es necesario iniciar MySQL para las pantallas actuales. Usar datos ficticios en los formularios de práctica.

## 5. Pantallas
| Archivo | Contenido y estado |
|---|---|
| `index.php` | Presentación, servicios, accesos, información institucional y pie |
| `login.php` | Inicio de sesión visual; no autentica |
| `registro.php` | Alta pública visual; no guarda usuarios |
| `recuperar-contrasena.php` | Solicitud visual por correo; no envía mensajes |
| `cambiar-contrasena.php` | Cambio desde perfil con validación de práctica; no guarda |
| `cliente/panel.php` | Bienvenida e indicadores de ejemplo |
| `cliente/mis-mascotas.php` | Tarjetas con filtro por especie |
| `cliente/perfil.php` | Consulta de datos ficticios y enlace de cambio de contraseña |
| `admin/panel.php` | Resumen administrativo y accesos |
| `admin/mascotas.php` | Tabla y filtro; edición/eliminación pendientes |
| `admin/clientes.php` | Tabla y búsqueda por nombre o correo |
| `admin/formulario-cliente.php` | Formulario con validación, sin guardado |
| `admin/formulario-mascota.php` | Archivo reservado; todavía no contiene el formulario |

Datos actuales: Laura Pérez, Max y Luna. Los nombres, cantidades y asociaciones están escritos en HTML.

## 6. Organización de carpetas
- `admin`: pantallas de administración.
- `cliente`: pantallas de consulta del cliente.
- `assets/css`: estilos compartidos y por pantalla, todos con comentarios de sección.
- `assets/js`: comportamiento de filtros, validaciones y animaciones.
- `assets/img`: siete imágenes del inicio público.
- `includes`: archivos reservados para componentes compartidos; aún no se usan.
- `db`: contiene `import.sql` vacío; no existe implementación de base de datos.
- `docs`: bitácora, manual, resumen de avance y guía de estilos.

Consultar `organizacion-estilos.md` para la correspondencia entre CSS y páginas. Los estilos comunes se cargan primero y los específicos después. Las rutas de imágenes dentro del CSS parten de `assets/css`, por eso usan `../img/`.

## 7. JavaScript existente
| Archivo | Función |
|---|---|
| `principal.js` | Dibuja recorridos de huellas y elimina cada huella al terminar su animación |
| `mascotas.js` | Filtra las tarjetas del cliente por especie y actualiza el contador |
| `admin-mascotas.js` | Filtra filas de la tabla administrativa por especie |
| `admin-clientes.js` | Busca nombre o correo sin distinguir mayúsculas ni acentos |
| `formulario-cliente.js` | Rechaza nombres vacíos y contraseñas diferentes; informa que no hay guardado |
| `cambiar-contrasena.js` | Muestra/oculta claves, compara confirmación y evita repetir la clave actual escrita |

Los scripts se conectan con `defer` para esperar a que exista el HTML. Los filtros actuales solo recorren datos ya cargados; podrán conservarse cuando PHP genere las filas desde MySQL. Si se incorpora paginación, habrá que decidir cómo buscar en todos los registros.

## 8. Diseño y decisiones
- Paleta azul y morada, tarjeta de acceso translúcida y ondas SVG.
- Huellas animadas detrás del formulario, sin bloquear clics.
- Tarjeta de acceso reducida por preferencia del estudiante.
- Se retiraron las orejas decorativas de los títulos; se conservó la cola.
- Menú lateral sticky en escritorio y menú superior en celulares.
- La página activa se marca con HTML y CSS; no requiere JavaScript.
- Versiones automáticas de CSS mediante filemtime para reducir problemas de caché.
- Diferenciación de especie y raza en las mascotas de ejemplo.

## 9. Imágenes del inicio
| Archivo en assets/img | Ubicación |
|---|---|
| `su cuidado.jpeg` | Presentación principal |
| `mis mascotas.jpeg` | Tarjeta Mis mascotas |
| `unete a nuestra comunidad.jpeg` | Tarjeta Únete a nuestra comunidad |
| `area del personal.jpeg` | Tarjeta Área del personal |
| `consulta veterinaria.jpeg` | Tarjeta Consulta veterinaria |
| `atencion preventiva.jpeg` | Tarjeta Atención preventiva |
| `registro de mascotas.jpeg` | Tarjeta Registro de mascotas |

## 10. Verificaciones realizadas
Según el registro de la revisión del 27 de octubre:
- 18 archivos PHP y 6 JavaScript sin errores de sintaxis.
- 12 páginas revisadas, con referencias locales y etiquetas comprobadas.
- 11 CSS cargados en el navegador.
- Búsqueda de clientes con y sin resultados y filtros de especies del cliente y administrador probados.
- Siete comprobaciones de validación de clientes y cambio de contraseña con datos ficticios.

Esta actualización documental del 28 de octubre no añade nuevas pruebas funcionales. Las cifras describen el alcance de la revisión anterior, no una certificación de que el sistema esté terminado.

## 11. Pendientes técnicos
Faltan MySQL y su modelo ER, conexión PDO, consultas preparadas, password_hash/password_verify, validación del servidor, sesiones y permisos, CRUD persistente, edición del perfil y recuperación real por enlace temporal. La validación de una contraseña escrita no verifica la identidad del usuario.

El plan detallado y la siguiente tarea se mantienen en `estado-proyecto.md`. El desarrollo de MySQL comenzará cuando el estudiante lo indique.

## 12. Documentación de entrega pendiente
Completar portada e integrantes, diagrama y explicación ER, capturas finales, instrucciones de importación de SQL, pruebas de instalación desde cero y conclusiones. Convertir el contenido final a Word o PDF según lo solicitado en la guía del profesor.
