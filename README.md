# Experiencia de cumpleaños · Seydi

Una experiencia web cinematográfica, hecha a mano, para celebrar a Seydi.
PHP + HTML5 + CSS3 + JavaScript (ES6) puros: **sin Node, sin npm, sin compilación**.
Lista para subir a **InfinityFree** (o cualquier hosting compartido con PHP 7.4+ y Apache).

---

## 1. Dirección artística: *Medianoche en champagne*

| Elemento | Decisión |
|---|---|
| Paleta | Vino casi negro `#120a0d`, vino `#5a1b2a`, champagne `#d9be8f`, dorado suave `#c8a46a`, crema `#f4ece1`, rosa empolvado `#c9a0a0`. Secciones oscuras (cine) alternadas con secciones "papel" crema (editorial). |
| Tipografía | **Cormorant Garamond** (titulares editoriales, cursiva elegante) · **Jost** (texto e interfaz, geométrica y ligera) · **Pinyon Script** (firma y detalles manuscritos). Las tres alojadas en local (licencia SIL OFL), sin peticiones a Google. |
| Ritmo | Mucho espacio negativo, numeración romana por capítulos (I–VII), líneas finas doradas, grano de película sutil. |
| Movimiento | Apariciones suaves al desplazarse, máscaras que descubren las fotos, un "iris" dorado como transición de la intro, parallax mínimo. Todo se desactiva con *Reducir movimiento*. |

La dirección se definió con ayuda de la skill **ui-ux-pro-max** (instalada en `.claude/skills/ui-ux-pro-max`): emparejamientos tipográficos románticos/lujo, reglas de accesibilidad (contraste, objetivos táctiles de 44 px, foco visible, `prefers-reduced-motion`) y checklist de entrega.

---

## 2. Recorrido de la experiencia

0. **Introducción** — polvo dorado, un sello con su inicial que se dibuja, texto que gira alrededor, frases que aparecen enfocándose. Al pulsar **Comenzar** suena la música y un iris dorado se abre hacia la portada.
1. **Portada** — foto en arco, su nombre en gran formato con degradado champagne, fecha, mensaje y estrella escondida.
2. **Nuestra historia** — línea de tiempo que se "dibuja" con el scroll; cada capítulo con foto, fecha, título y número romano.
3. **Galería** — masonry editorial (las fotos conservan su proporción), visor a pantalla completa con teclado (← → Esc), deslizamiento en móvil, precarga y contador.
4. **La carta** — sobre con sello de cera: el sello se rompe, la solapa se abre, el papel sale, la carta se despliega con pliegues; el saludo se escribe a máquina, el cuerpo aparece palabra a palabra y la firma se dibuja. Botón *Mostrar todo* y *Guardar en el sobre*.
5. **Momentos** — cinco composiciones distintas: polaroid, cita, foto + texto, tarjeta que se gira y entrada tipo ticket.
6. **Tu día** — reloj circular con 60 marcas que siguen los segundos + días/horas/minutos/segundos. Al llegar la fecha se transforma solo en "¡Hoy es tu día!" con confeti; después, en un mensaje permanente. Nunca muestra valores negativos.
7. **Final** — foto a pantalla completa con zoom lento, brasas doradas que ascienden, frases que aparecen una a una y la firma.
   **Epílogo** — formulario privado para que ella te deje un mensaje, *Volver a empezar* (repite la intro) y *Volver arriba*.

**Sorpresa:** hay **3 estrellas escondidas** (portada, galería y momentos). Al encontrar las tres se abre un mensaje secreto.

**Música:** tocadiscos flotante (disco que gira y ecualizador). Ver sección 5.

---

## 3. Estructura

```
public_html/                 ← sube el CONTENIDO de esta carpeta a htdocs/ en InfinityFree
├── index.php                ← punto de entrada
├── .htaccess                ← seguridad, caché, compresión, bloqueo de carpetas privadas
├── robots.txt
├── api/
│   └── mensaje.php          ← recibe el formulario de respuesta (POST + CSRF)
├── assets/
│   ├── .htaccess            ← impide ejecutar scripts dentro de assets
│   ├── css/                 ← base.css (tokens), components.css, sections.css
│   ├── js/                  ← módulos ES6: main, intro, nav, reveal, cursor, gallery,
│   │                          letter, countdown, music, particles, secrets, reply, utils, boot
│   ├── fonts/               ← tipografías locales .woff2
│   ├── images/
│   │   ├── fotos/           ← AQUÍ VAN SUS FOTOS REALES
│   │   └── provisionales/   ← marcadores "Foto provisional" (puedes borrarlos al final)
│   ├── audio/               ← aquí va la canción local (opcional)
│   └── icons/favicon.svg
├── config/                  ← BLOQUEADO al navegador
│   └── config.example.php   ← plantilla técnica (copiar como config.php)
├── data/                    ← BLOQUEADO al navegador
│   └── content.php          ← ★ TODO el contenido editable ★
├── includes/                ← BLOQUEADO al navegador
│   ├── bootstrap.php, functions.php, header.php, footer.php, gate.php
│   └── sections/            ← intro, hero, story, gallery, letter, moments, countdown, final
└── storage/                 ← BLOQUEADO; aquí se guardan los mensajes (mensajes.json)
```

---

## 4. Personalización (sin tocar código)

### 4.1 Textos, fechas y fotos → `public_html/data/content.php`

Es el **único archivo** que necesitas editar. Está comentado sección por sección:

| Qué | Clave |
|---|---|
| Nombre de ella / quien dedica / firma / inicial del sello | `her_name`, `from_name`, `signature`, `monogram` |
| Fecha y hora del cumpleaños + zona horaria | `birthday` (`'AAAA-MM-DD HH:MM'`), `timezone` |
| Frases de la introducción | `intro.lines` |
| Portada (título, mensaje, foto principal) | `hero` |
| Capítulos de la historia | `story` (añade o quita bloques) |
| Fotos de la galería | `gallery` |
| Carta (saludo, cuerpo, despedida) | `letter` — párrafos separados por una línea vacía |
| Momentos (y su composición) | `moments` (`layout`: polaroid, split, quote, flip, ticket) |
| Textos del reloj | `countdown` |
| Mensaje final y foto | `final` |
| Mensaje secreto de las estrellas | `secret` |
| Formulario de respuesta | `reply` (`'enabled' => false` para ocultarlo) |
| Música | `music` |
| Título/descripción para compartir | `seo` |

Todo lo marcado con **`[PROVISIONAL]`** o `[Fecha]` es contenido de ejemplo para reemplazar. No se inventaron fechas ni acontecimientos.

> ⚠️ **La fecha `2026-12-31 00:00` es provisional.** Cámbiala por la real.

Todo el texto se escapa automáticamente (seguro contra XSS): escribe texto normal, sin HTML.

### 4.2 Cómo poner sus fotos

1. Reduce cada foto a ~2000 px en el lado mayor (≈300–500 KB). Recomendado: <https://squoosh.app>.
2. Súbelas a `assets/images/fotos/` con nombres simples, sin espacios: `primera-cita.jpg`.
3. En `data/content.php` cambia la ruta, por ejemplo:
   ```php
   'photo' => ['src' => 'fotos/primera-cita.jpg', 'alt' => 'Nuestra primera cita'],
   ```
4. Listo. Las dimensiones se leen solas (no hay saltos de diseño) y las fotos **nunca se deforman** ni se alteran.
   *Extra:* si subes también `primera-cita.webp`, la web la usará automáticamente en navegadores compatibles.

Sugerencias: portada → vertical (3:4 o 4:5); final → horizontal (16:9 o 3:2); galería → mezcla de proporciones.

### 4.3 Configuración técnica → `public_html/config/config.php`

Copia `config/config.example.php` como `config/config.php` y ajusta:

| Opción | Para qué |
|---|---|
| `site_url` | URL final (`https://tusitio.infinityfreeapp.com`) para SEO y vista previa al compartir. |
| `access_code` | **Código de acceso opcional** (por ejemplo, la fecha en que se conocieron). Vacío = página pública. |
| `access_hint` | Pista que se muestra en la pantalla del código. |
| `indexable` | `false` (recomendado) = los buscadores no la indexan. |
| `debug` | `true` solo mientras pruebas. |

**¿Por qué no `.env`?** InfinityFree no permite variables de entorno. Un archivo PHP que devuelve un array es la alternativa compatible y segura: PHP lo ejecuta (nunca muestra su contenido) y además `.htaccess` bloquea toda la carpeta `/config`. `config.php` está en `.gitignore` para que no se suba al repositorio.

---

## 5. Música

La canción elegida es la de Spotify `3AeicLnm55RqcXGBKYQolM`. Spotify no permite descargar ni alojar sus pistas (DRM y derechos de autor), así que la web ofrece dos modos, **automáticos**:

**Modo Spotify (activo por defecto).** El tocadiscos de la página controla el **reproductor oficial de Spotify** (iFrame API). Empieza al pulsar *Comenzar* (nunca sin interacción). Si ella tiene sesión iniciada en Spotify en ese navegador suena la canción completa; si no, Spotify reproduce una vista previa. En algunos móviles el navegador exige tocar ▶ dentro del reproductor de Spotify: la web abre el panel y lo indica.

**Modo local (control total: volumen, barra de progreso, repetición).** Sube a `assets/audio/` un archivo que **tengas derecho a usar** con el nombre `nuestra-cancion.mp3` (o cambia `music.local_file`). Si el archivo existe, se usa automáticamente en lugar de Spotify.

Para cambiar de canción de Spotify: copia el enlace de la canción y pega en `music.spotify_track_id` la parte entre `/track/` y `?`.

---

## 6. Subir a InfinityFree (paso a paso)

1. Crea la cuenta y el sitio en <https://infinityfree.com> (subdominio gratuito o tu dominio).
2. Edita `public_html/data/content.php` (y opcionalmente crea `config/config.php`).
3. Abre **File Manager** (o conecta por **FTP** con FileZilla usando los datos de *FTP Details* del panel).
4. Entra en la carpeta **`htdocs`** y borra el archivo de bienvenida (`index2.html` o similar).
5. Sube **el contenido** de `public_html/` dentro de `htdocs/` (no la carpeta en sí): debe quedar `htdocs/index.php`, `htdocs/.htaccess`, `htdocs/assets/`…
   - En FileZilla activa *Mostrar archivos ocultos* para que se suba `.htaccess`.
6. Abre tu dirección. La primera visita puede tardar unos segundos (InfinityFree activa SSL y su protección anti-bots).
7. Activa **HTTPS** desde el panel (*SSL Certificates*, gratuito) para que la sesión y el formulario funcionen con cookie segura.

No hay que ejecutar comandos, instalar paquetes ni compilar nada. MySQL **no** es necesario.

### Leer los mensajes que ella te deje
Se guardan en `htdocs/storage/mensajes.json` (carpeta bloqueada al público). Ábrelo desde el File Manager de InfinityFree. No se guarda su IP: solo un identificador anónimo para limitar envíos.

---

## 7. Seguridad aplicada

- Todo contenido dinámico se escapa (`htmlspecialchars`) y los datos para JS viajan como JSON seguro.
- `Content-Security-Policy` sin scripts en línea (solo propios + Spotify oficial), `X-Frame-Options`, `nosniff`, `Referrer-Policy`, `Permissions-Policy`.
- `/config`, `/data`, `/includes`, `/storage`, archivos ocultos y extensiones sensibles → **403**. Sin listado de directorios.
- `assets/` no ejecuta scripts aunque alguien subiera uno (no existe ninguna función de subida).
- Archivos PHP internos con guarda `defined('SEYDI_APP') || exit;` (doble protección).
- Formulario: solo POST, token CSRF ligado a la sesión, campo trampa (honeypot), tiempo mínimo de llenado, límite de envíos por sesión y por IP (hash), longitud máxima, limpieza de caracteres de control, escritura con bloqueo de archivo.
- Código de acceso opcional: comparación en tiempo constante, regeneración de sesión, bloqueo temporal tras 5 intentos.
- Cookie de sesión `HttpOnly`, `SameSite=Lax` y `Secure` en HTTPS.
- No se recogen datos personales: el formulario solo pide un mensaje.

---

## 8. Rendimiento y accesibilidad

- Imágenes con `width`/`height` reales, `loading="lazy"` y `decoding="async"` (la portada, prioritaria). Soporte WebP automático.
- 3 hojas CSS y módulos JS pequeños, sin librerías externas. Fuentes locales con `font-display: swap` y precarga.
- Partículas en `<canvas>` con densidad reducida en móvil, pausadas fuera de pantalla o con la pestaña oculta.
- Un único bucle `requestAnimationFrame` para todo lo que depende del scroll; apariciones con `IntersectionObserver`.
- Caché de un año para recursos versionados (`?v=` según fecha de modificación) y compresión gzip.
- Navegación completa con teclado, foco visible, diálogos con foco atrapado y restaurado, `aria-*` en reproductor, menú, visor y tarjetas; objetivos táctiles ≥ 44 px; `prefers-reduced-motion` respetado; sin JavaScript el contenido se ve completo.
- El cursor personalizado solo aparece con ratón y **acompaña** al cursor del sistema (no lo sustituye).

---

## 9. Checklist de pruebas realizadas

Ver [`docs/QA.md`](docs/QA.md) — resultados reales de las pruebas ejecutadas con Chromium (Playwright), PHP 8.3 y Apache 2.4 con el `.htaccess` del proyecto.
