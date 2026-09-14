# Checklist de implementación

## Fase 0: decisiones y hosting

- [ ] Confirmar versión de PHP y MySQL/MariaDB en InfinityFree.
- [ ] Confirmar soporte de Apache, HTTPS, `.htaccess` y tareas programadas.
- [ ] Configurar `public/` como document root.
- [ ] Definir política de secretos, backups, logs y rollback.
- [ ] Confirmar roles: `usuario`, `colaborador`, `admin`.
- [ ] Confirmar si la trivia permite visitantes sin cuenta.

## Fase 1: estructura y documentación

- [ ] Alinear README raíz y documentación de `docs/`.
- [ ] Crear `.gitignore` para `.env`, logs, dependencias y archivos temporales.
- [ ] Corregir enlaces a `index.php`, CSS, JS e imágenes.
- [ ] Verificar que las carpetas privadas no sean descargables.
- [ ] Definir una convención única para rutas y nombres de clases.

## Fase 2: núcleo MVC

- [ ] Implementar bootstrap de la aplicación.
- [ ] Implementar autoload y configuración segura.
- [ ] Implementar router y respuestas HTTP.
- [ ] Definir rutas públicas y métodos HTTP.
- [ ] Implementar respuestas 404, 403, 419 y 500 sin revelar detalles internos.
- [ ] Mantener el flujo controller -> service -> repository.

## Fase 3: base de datos

- [ ] Crear migración inicial para `electromusiccrdb`.
- [ ] Crear `users` con `username`, `password_hash`, `role`, estado y timestamps.
- [ ] Crear `trivia` y tablas relacionadas para preguntas, opciones e intentos.
- [ ] Crear `publics` con estado, autor y timestamps.
- [ ] Añadir claves foráneas, índices, restricciones y `utf8mb4`.
- [ ] Implementar conexión PDO con excepciones y timeouts.
- [ ] Implementar repositories con consultas preparadas.
- [ ] Probar backup, restauración y rollback.

## Fase 4: seguridad

- [ ] Implementar `password_hash()` y `password_verify()`.
- [ ] Regenerar la sesión después del login.
- [ ] Configurar cookies `Secure`, `HttpOnly` y `SameSite`.
- [ ] Implementar logout por POST.
- [ ] Implementar middleware de autenticación y autorización por rol.
- [ ] Añadir protección CSRF a todos los POST que cambien estado.
- [ ] Añadir rate limiting a login, trivia y formularios.
- [ ] Validar tamaños, tipos, rangos y formatos en servidor.
- [ ] Escapar toda salida y sanitizar contenido permitido.
- [ ] Configurar HTTPS, cabeceras de seguridad y errores sin información sensible.

## Fase 5: contenido y trivia

- [ ] Implementar publicaciones con estados de moderación.
- [ ] Implementar pregunta diaria y respuesta validada en servidor.
- [ ] Calcular puntos exclusivamente en servidor.
- [ ] Impedir intentos duplicados según la regla definida.
- [ ] Implementar ranking paginado y resistente a manipulación.
- [ ] Definir el flujo de colaboración y aprobación administrativa.
- [ ] Mantener ventas personales y pagos desactivados.

## Fase 6: panel administrativo

- [ ] Proteger cada ruta admin con middleware.
- [ ] Crear gestión de usuarios y roles con controles estrictos.
- [ ] Crear revisión, aprobación y rechazo de publicaciones.
- [ ] Crear gestión de preguntas y campañas de trivia.
- [ ] Registrar acciones administrativas sin secretos.
- [ ] Verificar permisos con los tres roles y usuarios desactivados.

## Fase 7: pruebas y despliegue

- [ ] Añadir PHPUnit o una estrategia de pruebas reproducible.
- [ ] Probar autenticación, autorización, sesión y logout.
- [ ] Probar CSRF, XSS, SQL injection, rate limiting y payloads grandes.
- [ ] Probar concurrencia, duplicados y manipulación de puntos.
- [ ] Ejecutar smoke test de rutas, assets y errores HTTP.
- [ ] Verificar que `.env`, código privado, logs y migraciones no sean públicos.
- [ ] Verificar cookies, HTTPS, cabeceras y permisos de almacenamiento.
- [ ] Ejecutar despliegue de prueba en InfinityFree.
- [ ] Realizar backup antes de producción.
- [ ] Documentar rollback y soporte operativo.
