# Guía del proyecto ElectroMusicCR

## Propósito

ElectroMusicCR es una web de entretenimiento para una comunidad de fans de la música electrónica. El sitio publicará información, eventos y actividades interactivas sin convertirse, por ahora, en una plataforma de ventas personales o pagos.

La migración actual lleva el proyecto desde una web estática para GitHub Pages hacia una aplicación PHP/MySQL alojada en InfinityFree.

## Objetivos de la primera etapa

1. Conservar un índice público y las vistas informativas existentes.
2. Separar presentación, rutas, lógica de negocio y persistencia.
3. Añadir usuarios con los roles `usuario`, `colaborador` y `admin`.
4. Preparar publicaciones administrables y moderadas.
5. Crear una trivia diaria con respuestas, puntos y ranking.
6. Establecer una base segura antes de conectar formularios o datos reales.

## Arquitectura MVC

El código debe respetar este recorrido:

`HTTP -> route -> controller -> service -> repository -> MySQL`

- **Routes:** asignan URL y método HTTP a un controller.
- **Controller:** valida la entrada superficial, invoca un service y decide la respuesta.
- **Service:** contiene reglas de negocio, autorización contextual y transacciones.
- **Repository:** contiene consultas PDO preparadas; es la única capa que toca la BD.
- **View:** presenta datos ya procesados y escapa la salida.
- **Middleware:** aplica autenticación, autorización, CSRF, rate limiting y controles transversales.

No se permite SQL dentro de las vistas, consultas desde controllers, credenciales hardcodeadas ni lógica de negocio en JavaScript.

## Estructura de carpetas

| Carpeta | Responsabilidad | ¿Pública? |
| --- | --- | --- |
| `public/` | Front controller, CSS, JS e imágenes públicas | Sí |
| `app/controller/` | Coordinación de peticiones | No |
| `app/services/` | Casos de uso y reglas de negocio | No |
| `app/repository/` | Acceso a MySQL | No |
| `app/resources/views/` | Vistas públicas | No directa |
| `config/` | Configuración y middleware | No |
| `routes/` | Rutas | No |
| `bootstrap/` | Inicialización | No |
| `storage/` | Logs, caché y archivos internos | No |

El servidor debe apuntar a `public/` como document root. Si InfinityFree no lo permite, se deberá mantener un front controller mínimo en la raíz y bloquear explícitamente todas las carpetas privadas.

## Roles y permisos

- **usuario:** autenticación y participación en la trivia.
- **colaborador:** gestión limitada de contenido asignado.
- **admin:** usuarios, publicaciones, trivia, moderación y configuración.

Cada ruta protegida debe comprobar el rol en middleware. Los permisos no deben depender de un enlace visible u oculto.

## Flujo de trabajo

1. Definir el caso de uso y su permiso.
2. Crear o actualizar la migración de BD.
3. Implementar repository con consultas preparadas.
4. Implementar service con validación y reglas de negocio.
5. Implementar controller y ruta.
6. Crear la vista con escape de salida.
7. Añadir pruebas de autorización, validación y abuso.
8. Ejecutar la migración y probar en una BD desechable.

## Despliegue

Antes de publicar en InfinityFree se debe comprobar PHP, MySQL, HTTPS, document root, límites de recursos y soporte de `.htaccess`. Los secretos deben configurarse fuera del repositorio. El despliegue debe comenzar en una copia de prueba y tener backup y rollback documentados.
