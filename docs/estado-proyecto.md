# Estado del proyecto

Actualizado: 28 de octubre de 2026.

## Dónde nos quedamos
Ya están diseñadas las pantallas públicas, del cliente y del administrador. Hay filtros y algunas validaciones de práctica. El siguiente trabajo es **el formulario de mascotas**. Todavía no hemos comenzado MySQL.

## Qué puedes probar ahora
| Área | Disponible | Límite actual |
|---|---|---|
| Inicio público | Presentación, servicios, misión, visión, objetivos y siete imágenes | No hay citas ni video |
| Login | Diseño, fondo animado y enlaces | No comprueba credenciales |
| Registro público | Formulario visual | No registra cuentas ni compara aún las contraseñas mediante JS |
| Recuperación | Campo de correo y enlace de regreso | No envía enlaces ni correos |
| Cambio de contraseña | Mostrar/ocultar, comparación de contraseñas y mensajes | No comprueba la contraseña actual ni guarda cambios |
| Inicio del cliente | Bienvenida, resumen y tarjetas de ejemplo | Usuario y cantidades escritos en HTML |
| Mis mascotas | Filtro por especie y contador | Filtra solo los ejemplos cargados |
| Mi perfil | Datos en lectura y acceso al cambio de contraseña | Editar datos está deshabilitado |
| Inicio del administrador | Indicadores, accesos y tabla | Datos de ejemplo |
| Mascotas del administrador | Tabla y filtro por especie | Editar y eliminar están deshabilitados |
| Clientes del administrador | Búsqueda por nombre/correo y mensaje sin resultados | Datos de ejemplo; edición deshabilitada |
| Formulario de clientes | Campos obligatorios, correo, longitud y coincidencia de claves | Valida sin guardar |

## Pendientes en orden recomendado
- [ ] Construir `admin/formulario-mascota.php`: nombre, especie, edad positiva y dueño de ejemplo.
- [ ] Revisar formularios, enlaces y presentación en computadora y celular.
- [ ] Completar validaciones visuales de acceso/registro/recuperación según el flujo que se acuerde.
- [ ] Extraer encabezados y menús a `includes` si se decide reutilizarlos.
- [ ] Cuando el estudiante lo indique: diseñar y crear MySQL, relaciones e `import.sql`.
- [ ] Conectar PHP con consultas preparadas y validación del servidor.
- [ ] Implementar acceso, cierre de sesión, roles y permisos.
- [ ] Implementar altas, consultas, edición y eliminación de mascotas; asociarlas a clientes.
- [ ] Reemplazar todos los datos de ejemplo por consultas.
- [ ] Implementar edición del perfil y cambio real de contraseña.
- [ ] Implementar recuperación con enlace temporal y envío de correo si se mantiene esa ampliación.
- [ ] Preparar diagrama ER, capturas, instalación desde cero y documento final para el profesor.

## Fuera del alcance implementado
Citas, video, pagos, vacunas e historial clínico no se han desarrollado. Evaluar ampliaciones después de completar los requisitos del registro de mascotas.

## Cómo retomar el trabajo
1. Iniciar Apache en XAMPP.
2. Abrir `http://localhost/Proyecto-final/registro-mascotas/index.php`.
3. Para trabajar en un panel, abrir directamente `cliente/panel.php` o `admin/panel.php`: no hay autenticación todavía.
4. Revisar la siguiente tarea de esta lista.
5. Consultar `organizacion-estilos.md` antes de modificar CSS.
6. Al terminar, registrar actividad, archivos, pruebas reales y próximo paso en `bitacora.md`.
