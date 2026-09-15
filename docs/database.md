# Base de datos `electromusiccrdb`

## Propósito

La base de datos almacenará usuarios, contenido público y la actividad de trivia. El esquema debe versionarse mediante migraciones reproducibles y probarse primero en una base de datos desechable.

## Reglas de conexión

- Motor objetivo: MySQL/MariaDB compatible con InfinityFree.
- Charset y collation: `utf8mb4` y una collation moderna compatible con el hosting.
- Acceso desde PHP: PDO con excepciones activadas.
- Consultas: siempre preparadas; nunca concatenar valores recibidos del usuario.
- Credenciales: variables de entorno, nunca código fuente ni vistas.
- Usuario de BD: privilegios mínimos sobre `electromusiccrdb`.

Variables previstas, sin valores reales:

```text
DB_HOST=
DB_PORT=3306
DB_NAME=electromusiccrdb
DB_USER=
DB_PASSWORD=
```

## Tablas iniciales

### `users`

| Campo | Tipo previsto | Reglas |
| --- | --- | --- |
| `id` | entero sin signo | PK autoincremental |
| `username` | varchar | Único, normalizado y no vacío |
| `password_hash` | varchar | Hash generado con `password_hash()`; nunca texto plano |
| `role` | enum o varchar restringido | `usuario`, `colaborador` o `admin` |
| `created_at` | timestamp | Obligatorio |
| `updated_at` | timestamp | Actualización controlada |
| `is_active` | boolean | Permite revocar acceso sin borrar auditoría |

El requerimiento inicial `name/password` se implementa de forma segura como `username/password_hash`. No se almacenará una columna de contraseña sin hash.

### `trivia`

Debe representar la actividad o campaña de trivia. Como una trivia necesita preguntas, respuestas, intentos y puntos, esta tabla no debe almacenar toda la actividad en un único campo.

| Campo | Tipo previsto | Reglas |
| --- | --- | --- |
| `id` | entero sin signo | PK autoincremental |
| `title` | varchar | Título de la actividad |
| `scheduled_date` | date | Única fecha en la que se publica la trivia |
| `starts_at` | timestamp | Inicio |
| `ends_at` | timestamp nullable | Fin opcional |
| `status` | varchar restringido | `draft`, `active`, `closed`; una trivia vencida se trata como `closed` |
| `created_by` | entero sin signo | FK a `users.id` |
| `created_at` | timestamp | Obligatorio |

Se programa una trivia por día mediante `scheduled_date`. Se pueden cargar varias con anticipación, pero no se permite más de una trivia para la misma fecha. La consulta pública solo debe mostrar la trivia cuyo estado sea `active`, cuya fecha sea la fecha actual y cuyo horario permita participar. Las trivias con fecha pasada no deben aparecer, aunque no se haya actualizado manualmente su estado.

Tablas relacionadas:

- `trivia_questions`: pregunta, fecha o posición, puntos y estado.
- `trivia_options`: opciones de respuesta, con la correcta protegida del cliente.
- `trivia_players`: nickname único normalizado y hash del código de acceso; no guarda el código en texto plano.
- `trivia_submissions`: un envío completo de una trivia, con intento `1` o `2`, jugador, puntos calculados por el servidor y estado.
- `trivia_attempts`: respuestas individuales asociadas a un envío, respuesta validada y puntos calculados.

Cada jugador dispone de dos envíos como máximo para cada trivia diaria. La restricción única de `trivia_submissions` impide registrar un tercer intento para el mismo jugador y trivia; la aplicación debe rechazarlo antes de mostrar el formulario. Los puntos se calculan en el servidor dentro de una transacción y el navegador nunca puede enviarlos como valor confiable.

### Identidad de participantes y sesión

Para participar, la persona crea un nickname único. El servidor genera un código aleatorio, guarda únicamente su hash y muestra el código una sola vez junto con un mensaje indicando que debe conservarlo: actualmente no existe recuperación.

La sesión normal se conserva mediante una cookie de sesión segura, `HttpOnly`, `Secure` y con `SameSite=Lax`, vinculada a `trivia_players.id`. No se debe guardar el código secreto en `localStorage` ni exponerlo en el HTML. Si la cookie se pierde, la aplicación solicita nickname y código, verifica el hash y emite una nueva sesión. Los intentos se autorizan siempre contra el jugador resuelto en el servidor, nunca contra un id recibido sin validación.

### Ranking público

La migración [002_trivia_players_schedule_ranking.sql](../database/migrations/002_trivia_players_schedule_ranking.sql) crea la vista `trivia_ranking`, que devuelve directamente `position`, `nickname` y `points` para una tabla pública. Solo incluye jugadores activos y envíos con estado `submitted`; los puntos de envíos invalidados no cuentan. La consulta de la vista debe aplicar paginación en la capa de aplicación si el ranking crece.

### `publics`

| Campo | Tipo previsto | Reglas |
| --- | --- | --- |
| `id` | entero sin signo | PK autoincremental |
| `title` | varchar | Título escapado al mostrar |
| `descriptions` | text | Contenido validado; evitar HTML sin sanitización |
| `fileroute` | varchar nullable | Ruta controlada, no una ruta arbitraria del servidor |
| `status` | varchar restringido | `draft`, `pending`, `published`, `rejected` |
| `created_by` | entero sin signo | FK a `users.id` |
| `created_at` | timestamp | Obligatorio |
| `updated_at` | timestamp | Actualización controlada |

El significado final de `fileroute` debe definirse antes de habilitar archivos. No se aceptarán rutas proporcionadas directamente por el usuario ni uploads ejecutables.

## Integridad y seguridad

- Usar claves foráneas e índices para búsquedas por estado, fecha y usuario.
- Aplicar límites y paginación a listados públicos y administrativos.
- Usar transacciones cuando una acción escriba varias tablas.
- Crear el jugador y su código dentro de una operación controlada; el código se muestra solo en la confirmación inicial y nunca se registra en logs.
- Aplicar límite de dos envíos por jugador y trivia en servidor y conservar la restricción única de la base de datos como segunda barrera.
- Cerrar o excluir por fecha las trivias vencidas; no confiar únicamente en que el cliente o una tarea programada cambie `status`.
- Registrar auditoría mínima para cambios administrativos sin guardar contraseñas ni tokens.
- No eliminar usuarios o contenido si se necesita conservar trazabilidad; preferir estados o desactivación.
- Separar migraciones de datos de prueba y datos de producción.

## Migraciones y operación

Las migraciones se ejecutan en orden: [001_initial_schema.sql](../database/migrations/001_initial_schema.sql), [002_trivia_players_schedule_ranking.sql](../database/migrations/002_trivia_players_schedule_ranking.sql) y [003_admin_content.sql](../database/migrations/003_admin_content.sql). Deben ejecutarse en una base de datos de prueba antes de InfinityFree y registrarse como parte del despliegue.

La migración 003 agrega el nombre visible de usuario, la descripción de trivia y las tablas `news` y `events`. Después de ejecutarla se debe crear el primer usuario con rol `admin`; la contraseña debe ser un hash generado por `password_hash()` y nunca una contraseña escrita directamente en la tabla.

Antes de implementar repositories se debe crear una migración inicial y documentar:

1. Creación de `electromusiccrdb`.
2. Creación de tablas, claves, índices y restricciones.
3. Creación segura del primer administrador.
4. Backup, restauración y rollback.
5. Verificación de charset, permisos y conexión desde PHP.

Para probar trivia se deben verificar también la carga anticipada de varias fechas, la exclusión automática de fechas vencidas, el rechazo del tercer envío, la recuperación de sesión mediante nickname y código, y el orden de la vista `trivia_ranking`.

No se deben ejecutar cambios manuales en producción sin registrar la versión de la migración.
