# Plan: Panel de Configuración de Trivia — Edición de Título, Descripción e Imagen

## Problema
- En el panel admin, al crear una trivia se pueden ingresar título, descripción e imagen.
- Una vez creada, la sección "Configuración general" aparece como `readonly` y **no hay forma de editar esos valores** para trivias existentes.
- La sección "Trivia" del admin (`/app/resources/views/ranking.php`) no incluye un botón ni un formulario dedicado a editar el título/descripción que se muestra en el ranking público.

## Arquitectura Actual (contexto)
- `trivia_settings` (id=1) almacena `title`, `description`, `image_path`.
- `AdminRepository::dashboard()` → `trivia()` devuelve estos campos para la vista admin.
- `TriviaRepository::saveAdmin()` guarda settings e inserta/actualiza preguntas en una transacción.
- El formulario "Trivia" envía todo como `multipart/form-data` a `/api/admin/trivia` → `AdminController::saveTrivia()`.

## Decisiones de Diseño (con recomendación)

### 1. Separación de responsabilidades
- **Recomendado:** Crear una sección **"Configuración de Trivia"** dedicada exclusivamente al título, descripción, y imagen que aparecen en el ranking público y la vista de trivia. Esto evita mezclar settings globales con preguntas individuales.

### 2. Menú de navegación
- Agregar un botón "Configuración" entre "Eventos" y "Trivia" en `settings.php`.
- Cargar los datos de `dashboard.trivia` (que ya incluye settings) en esta sección.

### 3. Estados del formulario
- **Ver:** Campos deshabilitados (`readonly`), con botón "Editar configuración".
- **Editar:** Campos habilitados, con botón "Cancelar" y "Guardar cambios".
- Al guardar → POST a `/api/admin/trivia-settings`.

### 4. API y backend
- Nuevo endpoint `POST /api/admin/trivia-settings` → `AdminController::updateTriviaSettings()`.
- Nuevo método en `AdminRepository::updateTriviaSettings()` que hace `UPDATE trivia_settings`.
- Reutilizar el método `upload()` existente de `AdminController`.

## Archivos a Modificar

### 1. `app/resources/adminviews/settings.php`
Agregar botón de navegación:
```html
<button type="button" data-admin-panel="trivia-settings">Configuración</button>
```
(entre "Eventos" y "Trivia")

### 2. `app/controller/AdminController.php`
Agregar método:
```php
public function updateTriviaSettings(): never
{
    $user = SessionManager::requireAdmin();
    SessionManager::validateCsrf();
    $title = trim((string) ($_POST['title'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    if ($title === '') {
        Response::json(['error' => 'El título es obligatorio.'], 422);
    }
    Response::json(['data' => $this->repository->updateTriviaSettings(
        $title, $description, $this->upload('image'), (int) $user['id']
    )]);
}
```
**Nota:** Reutiliza el helper privado `upload()` ya existente en `AdminController.php:175`.

### 3. `app/repository/AdminRepository.php`
Agregar método:
```php
public function updateTriviaSettings(string $title, string $description, ?string $imagePath, int $userId): array
{
    $this->ensureSettingsTable();
    $existing = $this->connection->query(
        'SELECT image_path FROM trivia_settings WHERE id = 1'
    )->fetch();
    $currentImage = $existing !== false ? ($existing['image_path'] ?? null) : null;
    $finalImage = $imagePath ?? $currentImage;

    $statement = $this->connection->prepare(
        'INSERT INTO trivia_settings (id, title, description, image_path, updated_by)
         VALUES (1, :title, :description, :image_path, :updated_by)
         ON DUPLICATE KEY UPDATE title = VALUES(title), description = VALUES(description),
         image_path = VALUES(image_path), updated_by = VALUES(updated_by)'
    );
    $statement->execute([
        'title' => $title,
        'description' => $description,
        'image_path' => $finalImage,
        'updated_by' => $userId,
    ]);
    return ['updated' => true];
}
```

### 4. `routes/api.php`
Agregar ruta **antes** del `/api/admin/trivia` route (insert at line 42, above existing trivia route):
```php
if ($adminController !== null && $path === '/api/admin/trivia-settings' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $adminController->updateTriviaSettings();
}
```

### 5. `public/js/admin.js`
- Variable `editingTriviaSettings`.
- Función `renderTriviaSettings(settings)`:
  - Si `!editingTriviaSettings`: ver formulario con `readonly`, botón "Editar configuración".
  - Si `editingTriviaSettings`: formulario editable con `Cancelar` y `Guardar cambios`.
  - Imagen previa con fallback.
- Handler `click` para `[data-edit-settings]` → activa modo edición.
- Handler `click` para `[data-cancel-settings]` → cancela edición.
- Handler `submit` para `form[data-form="trivia-settings"]` → POST a `/api/admin/trivia-settings`.
- Agregar `'trivia-settings': renderTriviaSettings` en el mapeo de navegación.

### 6. `app/repository/AdminRepository.php` — método `trivia()`
Normalizar `image_path` con regex de 32 chars hex (igual que `TriviaRepository::findCurrent()`).

## Flujo de Trabajo
1. Admin abre panel → hace click en "Configuración".
2. Se muestra formulario de vista con título, descripción, imagen previa (readonly).
3. Admin hace click en "Editar configuración" → campos se habilitan.
4. Admin modifica y hace click en "Guardar cambios" → POST `/api/admin/trivia-settings`.
5. El API llama a `AdminRepository::updateTriviaSettings()` → actualiza `trivia_settings`.
6. Se recarga el dashboard y se vuelve a renderizar la sección.

## Validación
- Título vacío → error 422.
- Image upload reutiliza lógica existente (máx 5MB, JPG/PNG/WebP).
- Sin imagen nueva → conserva `image_path` existente.
- Verificar que el ranking público refleja los cambios después de guardar.

## Riesgos
- Si `trivia_settings` no existe → `ensureSettingsTable()` la crea.
- Zona horaria de imágenes no es un problema (se guardan paths relativos).
- No se elimina el archivo viejo de imagen al subir una nueva (considerar limpieza futura).
