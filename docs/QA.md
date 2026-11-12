# Checklist de QA — resultados reales

Entorno de prueba: PHP 8.3 (servidor integrado) y **Apache 2.4 + mod_php con el `.htaccess` del proyecto**,
Chromium (Playwright). Dispositivos emulados: iPhone 13 (vertical y horizontal), Pixel 7, iPad (7.ª gen.),
pantalla de 320×568, escritorio 1280×800 y 1440×900.

| # | Verificación | Resultado |
|---|---|---|
| 1 | Todas las secciones se muestran (intro, portada, historia, galería, carta, momentos, reloj, final, epílogo) | ✅ |
| 2 | Botones: Comenzar, menú (abrir/cerrar/enlaces/Esc), Empezar la historia, sello, Mostrar todo, Guardar en el sobre, tarjeta que gira, estrellas, Enviar, Volver a empezar, Volver arriba, reproductor | ✅ |
| 3 | Animaciones sin bloqueos: intro → iris → portada; carta (apertura, escritura, firma); apariciones al hacer scroll | ✅ (se corrigió un fallo: las fotos con máscara no aparecían porque `IntersectionObserver` ignora elementos con `clip-path: inset(100%)`) |
| 4 | Visor: abre, ← →, botones, navegación circular, deslizar en táctil, Esc, foco restaurado | ✅ |
| 5 | Todas las imágenes cargan (0 rotas) | ✅ |
| 6 | Reproductor modo local (archivo de prueba generado, no incluido): no suena antes de interactuar, suena con "Comenzar", pausa, volumen, progreso, panel, Esc | ✅ |
| 6b | Reproductor modo Spotify | ⚠️ No verificable aquí: la red de pruebas bloquea `open.spotify.com`. Se comprobó que el fallo se maneja sin errores ("Música no disponible" + aviso). Debe probarse en el sitio publicado. |
| 7 | Reloj: valores no negativos, avanza cada segundo, cambio automático a "¡Hoy es tu día!" al llegar a cero, estado "celebración" y estado "pasado" calculados también por PHP | ✅ |
| 8 | Navegación: menú, indicador de capítulo, barra de progreso, ocultar/mostrar barra superior | ✅ |
| 9 | Errores JavaScript | ✅ 0 errores (solo los avisos esperados de Spotify bloqueado en el entorno de pruebas) |
| 10 | Errores PHP | ✅ `php -l` en todos los archivos, sin avisos en la salida |
| 11 | Rutas rotas | ✅ CSS, JS, fuentes, imágenes y API responden 200/405 correctamente |
| 12 | Responsive | ✅ sin desbordamiento horizontal en ningún tamaño; ajustes hechos para portada y reproductor en móvil |
| 13 | Sin Node/npm/build | ✅ solo PHP, HTML, CSS y JS nativos |
| 14 | Archivos necesarios incluidos (fuentes locales, iconos, marcadores de fotos) | ✅ |
| 15 | Despliegue en Apache con `.htaccess`: `/config`, `/data`, `/includes`, `/storage`, `.htaccess`, `*.php` en assets → 403; sin listado de directorios; cabeceras de seguridad y caché | ✅ |
| 16 | Formulario: vacío rechazado, envío guardado en `storage/mensajes.json`, CSRF, tiempo mínimo | ✅ |
| 17 | Código de acceso: pantalla, código erróneo rechazado, código correcto (con espacios) acepta | ✅ |
| 18 | `prefers-reduced-motion` y navegación sin JavaScript (contenido completo visible) | ✅ |

Pendiente para ti, tras publicar: probar en tu iPhone/Android reales y la reproducción de Spotify con su cuenta.

## Versión 2 · MySQL + panel (MariaDB 10 / Apache 2.4 / PHP 8.3)

| Verificación | Resultado |
|---|---|
| `seydi.sql` importa sin errores (7 tablas, 7 fotos, 5 capítulos, 7 de galería, 5 momentos, usuario) | ✅ |
| Instalador web: escribe `config/config.php`, ejecuta el SQL y se bloquea | ✅ |
| La web lee todo de MySQL; 0 textos provisionales; fotos servidas por `foto.php` (WebP, caché) | ✅ |
| `/database/seydi.sql`, `.gz`, `/config`, `/includes`, `/data` → 403 | ✅ |
| Panel: login incorrecto rechazado, login correcto, guardar ajustes (se refleja en la web), validación de fecha | ✅ |
| Subida de foto → WebP optimizada y añadida a la galería; no deja borrar fotos en uso | ✅ |
| Editar capítulo; mensaje enviado desde la web llega al panel con aviso de "nuevo" | ✅ |
| Cambio de contraseña; POST sin token CSRF rechazado; cerrar sesión | ✅ |
| Reloj: en la última hora muestra minutos (no "0 días"); al llegar muestra "18 años" | ✅ |
| Sin errores JavaScript; sin desbordes en escritorio y móvil | ✅ |
