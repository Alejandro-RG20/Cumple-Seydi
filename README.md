# Experiencia de cumpleaños · Seydi

Una experiencia web cinematográfica creada por **Alejandro Rodríguez** para celebrar los **18 años de Seydi**.
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

## 3. Instalación en InfinityFree (≈ 5 minutos)

1. **Crea la base de datos:** InfinityFree → *Control Panel* → **MySQL Databases** → crea una (por ejemplo `seydi`). Anota los 4 datos que aparecen: *MySQL Host Name*, *DB Name*, *User Name* y *Password* (es la contraseña de tu cuenta/vPanel).
2. **Sube los archivos:** con *File Manager* o FTP (FileZilla), sube **el contenido** de `public_html/` dentro de `htdocs/` (borra antes el `index2.html` de bienvenida). Activa "mostrar archivos ocultos" para que se suba `.htaccess`.
3. **Instala la base de datos** — elige una opción:
   - **A (recomendada): instalador web.** Abre `https://TU-SITIO/instalar.php`, pega los 4 datos de MySQL y pulsa **Instalar**. Crea `config/config.php`, ejecuta `database/seydi.sql` (tablas + textos + fotos + usuario) y se bloquea solo.
   - **B: phpMyAdmin.** Selecciona tu base de datos → *Importar* → `public_html/database/seydi.sql` (o `seydi.sql.gz` si el archivo pesa demasiado). Después copia `config/config.example.php` como `config/config.php` y rellena el bloque `db`.
4. Abre tu web. Activa **HTTPS** gratis en *SSL Certificates*.

No hay que ejecutar comandos ni compilar nada.

### Panel de administración
`https://TU-SITIO/admin` — usuario **`alejandro`**; la contraseña inicial te la di por el chat (no se guarda en el repositorio: solo su hash bcrypt). **Cámbiala en *Mi cuenta*.**
Desde el panel puedes editar:
- **Textos y ajustes:** nombres, fecha y hora, introducción, portada, carta, reloj, final, secreto, música y SEO.
- **Historia, Galería y Momentos:** editar, añadir, borrar y reordenar.
- **Fotos:** subir fotos nuevas (se convierten solas a WebP ligero de máx. 1600 px + miniatura, sin metadatos ni GPS) y editar sus descripciones.
- **Mensajes:** leer lo que ella te escriba desde la web.

---

## 4. Dónde está cada cosa: todo en MySQL

| Tabla | Contenido |
|---|---|
| `settings` | Todos los textos y ajustes (clave/valor) |
| `photos` | Las fotos en WebP (grande + miniatura) guardadas como BLOB |
| `story` / `gallery` / `moments` | Capítulos, galería y momentos, con su orden |
| `messages` | Los mensajes que ella deja |
| `admins` | Usuarios del panel (contraseña con bcrypt, bloqueo tras 5 intentos) |

- `foto.php?f=slug` sirve las fotos desde la base, con caché larga en el navegador.
- `data/content.php` es la **semilla** con la que se generó el SQL y el **respaldo**: si MySQL fallara, la web sigue mostrando los textos.
- Para regenerar el SQL (solo desarrollo): `php tools/build-sql.php --photos=CARPETA --user=alejandro --hash='HASH'`.

### Fecha
Cumpleaños: **12 de noviembre de 2026, 00:00, hora de Nicaragua (`America/Managua`)**. El reloj cuenta hacia atrás y a esa hora cambia solo a "¡Hoy es tu día!" con el **18** en el centro.

---

## 5. Música

Viene configurada y **suena sola cuando ella pulsa "Comenzar"**, sin que tengas que tocar nada: el tocadiscos controla el reproductor oficial de Spotify con la canción `3AeicLnm55RqcXGBKYQolM` ("Tu canción").
- Si ella tiene sesión iniciada en Spotify en ese navegador, suena la canción completa; si no, Spotify reproduce un fragmento.
- En algunos iPhone el navegador obliga a tocar ▶ dentro de Spotify la primera vez: la web abre el panel y lo indica.
- Opcional: si subes `assets/audio/tu-cancion.mp3` (un audio que tengas derecho a usar), se usa ese archivo con control de volumen.

---

## 6. Estructura

```
public_html/            ← el contenido va dentro de htdocs/
├── index.php, foto.php, instalar.php, .htaccess, robots.txt
├── admin/index.php     ← panel
├── api/mensaje.php     ← formulario (CSRF, límites)
├── assets/             ← css, js (módulos ES6), fuentes locales, iconos
├── config/             ← BLOQUEADO · config.example.php (+ config.php generado)
├── data/content.php    ← BLOQUEADO · semilla/respaldo
├── database/           ← BLOQUEADO · schema.sql, seydi.sql, seydi.sql.gz
├── includes/           ← BLOQUEADO · bootstrap, db, admin, funciones, secciones
└── storage/            ← BLOQUEADO · candado del instalador
tools/build-sql.php     ← generador del SQL (no se sube)
```

---

## 7. Seguridad aplicada

- Todo contenido dinámico se escapa (`htmlspecialchars`) y los datos para JS viajan como JSON seguro.
- `Content-Security-Policy` sin scripts en línea (solo propios + Spotify oficial), `X-Frame-Options`, `nosniff`, `Referrer-Policy`, `Permissions-Policy`.
- `/config`, `/data`, `/includes`, `/storage`, archivos ocultos y extensiones sensibles → **403**. Sin listado de directorios.
- `assets/` no ejecuta scripts aunque alguien subiera uno (no existe ninguna función de subida).
- Archivos PHP internos con guarda `defined('SEYDI_APP') || exit;` (doble protección).
- Panel: sesión con caducidad, `password_hash` (bcrypt), bloqueo tras 5 intentos, CSRF en todas las acciones, fotos re-codificadas con GD (elimina cualquier contenido oculto y metadatos).
- Instalador: se bloquea tras usarse; el SQL y la carpeta `database/` no son accesibles desde el navegador.
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
