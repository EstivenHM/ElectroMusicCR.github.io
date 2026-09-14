# ElectroMusicCR

Sitio web de entretenimiento para la comunidad costarricense aficionada a la música electrónica. El proyecto está migrando de una maqueta estática a una aplicación PHP/MVC con MySQL para publicar contenido, administrar usuarios y ofrecer una trivia diaria con puntos y ranking.

## Estado del proyecto

La implementación se encuentra en la primera etapa. La estructura MVC ya está preparada, pero todavía deben implementarse el router, el bootstrap, la conexión PDO, las migraciones, la autenticación y el panel administrativo.

## Arquitectura

- `public/`: única carpeta que debe quedar expuesta por el servidor web; contiene assets públicos y el front controller.
- `app/controller/`: recibe la petición y coordina la respuesta.
- `app/services/`: reglas de negocio, validación y casos de uso.
- `app/repository/`: única capa autorizada para consultar o modificar la base de datos.
- `app/resources/views/`: vistas públicas sin SQL, credenciales, estilos inline ni lógica de negocio.
- `app/resources/adminviews/`: vistas protegidas del panel administrativo.
- `config/`: configuración y middleware; nunca debe ser accesible desde el navegador.
- `routes/`: definición de rutas y métodos HTTP permitidos.
- `bootstrap/`: inicialización de la aplicación y dependencias.
- `storage/`: logs, caché y archivos internos; no debe ser público.
- `docs/`: documentación técnica, modelo de datos y seguimiento.

El flujo obligatorio para datos es:

`ruta -> controller -> service -> repository -> base de datos`

Las vistas reciben datos ya preparados y escapan toda salida según su contexto.

## Roles

- `usuario`: puede autenticarse y participar en las funciones públicas permitidas.
- `colaborador`: puede proponer o gestionar contenido según permisos asignados.
- `admin`: puede moderar contenido, gestionar usuarios y configurar la plataforma.

Las contraseñas se almacenarán únicamente mediante `password_hash()`. El rol se validará en middleware; ocultar un enlace no constituye autorización.

## Funcionalidades previstas

- Índice y contenido público.
- Secciones informativas de la comunidad.
- Publicaciones administrables con moderación.
- Trivia diaria relacionada con conciertos o actividades.
- Puntos calculados en el servidor y ranking protegido contra manipulación.
- Panel administrativo para usuarios, publicaciones y trivia.
- Formularios de contacto o eventos no comerciales.

Las ventas personales, pagos y transacciones comerciales quedan fuera de esta etapa.

## Seguridad mínima obligatoria

- Configurar `public/` como document root en el hosting.
- Mantener `.env`, `app/`, `config/`, `routes/`, `bootstrap/` y `storage/` fuera del acceso web.
- Usar PDO con consultas preparadas y `utf8mb4`.
- Validar datos en servidor y escapar la salida.
- Usar sesiones seguras, regeneración de sesión, CSRF y rate limiting.
- Aplicar autorización por rol a cada ruta protegida.
- No habilitar uploads hasta definir validación MIME, límites, nombres seguros y almacenamiento fuera del document root.

## Documentación

- [docs/readme.md](docs/readme.md): visión, arquitectura, flujo de trabajo y despliegue.
- [docs/database.md](docs/database.md): modelo de datos y reglas para MySQL.
- [docs/checklist.md](docs/checklist.md): tareas y criterios de finalización.

## Desarrollo local

El proyecto requiere PHP y MySQL/MariaDB para las funciones dinámicas. No se deben subir credenciales reales al repositorio. Antes de conectar la aplicación, configurar las variables de entorno indicadas en [docs/database.md](docs/database.md) y ejecutar las migraciones en una base de datos de prueba.
