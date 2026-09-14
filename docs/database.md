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
| `starts_at` | timestamp | Inicio |
| `ends_at` | timestamp nullable | Fin opcional |
| `status` | varchar restringido | `draft`, `active`, `closed` |
| `created_by` | entero sin signo | FK a `users.id` |
| `created_at` | timestamp | Obligatorio |

Tablas relacionadas previstas:

- `trivia_questions`: pregunta, fecha o posición, puntos y estado.
- `trivia_options`: opciones de respuesta, con la correcta protegida del cliente.
- `trivia_attempts`: usuario o identificador temporal, pregunta, respuesta validada, puntos calculados y fecha.

El ranking se calcula a partir de intentos válidos. El navegador nunca puede enviar los puntos finales.

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
- Registrar auditoría mínima para cambios administrativos sin guardar contraseñas ni tokens.
- No eliminar usuarios o contenido si se necesita conservar trazabilidad; preferir estados o desactivación.
- Separar migraciones de datos de prueba y datos de producción.

## Migraciones y operación

La migración inicial se encuentra en [database/migrations/001_initial_schema.sql](../database/migrations/001_initial_schema.sql). Debe ejecutarse en una base de datos de prueba antes de InfinityFree y registrarse como parte del despliegue.

Antes de implementar repositories se debe crear una migración inicial y documentar:

1. Creación de `electromusiccrdb`.
2. Creación de tablas, claves, índices y restricciones.
3. Creación segura del primer administrador.
4. Backup, restauración y rollback.
5. Verificación de charset, permisos y conexión desde PHP.

No se deben ejecutar cambios manuales en producción sin registrar la versión de la migración.
