# ElectroMusicCR.github.io

Sitio estático mobile-first para la comunidad ElectroMusicCR. La estructura quedó separada por carpetas para mantener HTML, CSS y recursos estáticos de forma limpia.

## Estructura

- [index.html](index.html) — portada principal.
- [pages/formulario-evento.html](pages/formulario-evento.html) — plantilla para informar un evento.
- [pages/formulario-venta.html](pages/formulario-venta.html) — plantilla para notificar una venta de entrada.
- [pages/nosotros.html](pages/nosotros.html) — vista interna de presentación.
- [pages/contacto.html](pages/contacto.html) — vista interna de contacto.
- [styles/main.css](styles/main.css) — estilos compartidos.
- [assets/](assets/) — carpeta reservada para imágenes y otros recursos estáticos.

## Contenido editable

- La sección de botones rápidos en [index.html](index.html) apunta a los formularios y se puede cambiar editando el texto del enlace y su `href`.
- Las secciones de [eventos](index.html) y [ventas](index.html) están pensadas para crecer manualmente en el HTML, sin base de datos.
- La sección de [noticias](index.html) usa una imagen decorativa, un título y un cuerpo de texto por tarjeta.

## Uso

Abre [index.html](index.html) en el navegador. Desde ahí puedes navegar a las secciones de la portada y entrar a las vistas internas, que incluyen botón de “Volver al inicio”.
