# Plan: Vista de Ranking de Trivia

## Objetivo
Crear una vista de ranking que muestre:
- Título, imagen y descripción de la trivia (desde `trivia_settings`)
- Lista de jugadores con nick y puntos obtenidos
- Contador regresivo hasta el 27 de noviembre a las 2pm (días, horas, minutos)

## Contexto Actual
- Ya existe `RankingController`, `RankingService`, `RankingRepository` y vista `ranking.php`
- La vista actual carga datos vía JavaScript desde `/api/ranking`
- Los settings de trivia se almacenan en tabla `trivia_settings` (title, description, image_path)
- El contador debe finalizar: **27 de noviembre de 2026 a las 14:00:00** (hora del servidor o cliente)

## Decisiones de Diseño

### 1. Origen del contador
**Recomendación:** Calcular desde el navegador (JavaScript)
- Evita dependencia de zona horaria del servidor
- Actualización en tiempo real sin polling
- Más simple de implementar

### 2. Estructura de datos del ranking
El endpoint `/api/ranking` debe devolver adicionalmente:
```json
{
  "items": [...],
  "page": 1,
  "limit": 20,
  "settings": {
    "title": "Trivia ElectroMusicCR",
    "description": "Descripción...",
    "image_path": "/public/images/trivia/..."
  }
}
```

### 3. Contador regresivo
- Fecha límite: `2026-11-27T14:00:00`
- Mostrar: "X días, Y horas, Z minutos"
- Formato pequeño/discreto en la vista
- **Cuando finalice:** Ocultar el contador completamente

## Archivos a Modificar

### 1. `app/repository/RankingRepository.php`
Añadir método `getSettings()`:
```php
public function getSettings(): ?array
{
    $statement = $this->connection->prepare(
        'SELECT title, description, image_path FROM trivia_settings WHERE id = 1 LIMIT 1'
    );
    $statement->execute();
    return $statement->fetch() ?: null;
}
```

### 2. `app/services/RankingService.php`
Modificar `paginate()` para incluir settings:
```php
public function paginate(int $requestedPage, int $requestedLimit): array
{
    // ... lógica existente ...
    return [
        'items' => $this->repository->paginate($limit, $offset),
        'page' => $page,
        'limit' => $limit,
        'settings' => $this->repository->getSettings(),
    ];
}
```

### 3. `app/resources/views/ranking.php`
Añadir sección de encabezado con imagen, título, descripción y contador:
- Contenedor para imagen (si existe en settings)
- Título y descripción desde settings
- Elemento HTML para el contador: `<span data-countdown></span>`

### 4. `public/js/ranking.js`
- Añadir lógica de contador regresivo cliente-side
- Renderizar settings (título, descripción, imagen) al cargar
- Función `updateCountdown()` que actualice cada minuto

### 5. `public/css/main.css`
Añadir estilos para:
- `.ranking-header` - contenedor de título/imagen/descripción
- `.countdown-timer` - estilo pequeño y discreto
- Media queries responsive

## Orden de Implementación

1. **Repository** - Añadir `getSettings()` en `RankingRepository.php`
2. **Service** - Modificar `RankingService.php` para incluir settings
3. **Vista** - Actualizar `ranking.php` con nueva estructura HTML
4. **JavaScript** - Modificar `ranking.js` para:
   - Renderizar settings recibidos
   - Implementar contador regresivo
5. **CSS** - Añadir estilos en `main.css`

## Validación
- Verificar que `/api/ranking` devuelve settings correctamente
- Comprobar que el contador se actualiza en tiempo real
- Probar responsive en móvil y escritorio
- Verificar que funciona sin imagen (cuando `image_path` es null)

## Riesgos
- Si `trivia_settings` está vacío, mostrar valores por defecto
- Zona horaria del usuario afecta la precisión del contador (aceptable para este caso)
