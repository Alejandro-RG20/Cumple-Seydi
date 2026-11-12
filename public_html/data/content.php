<?php
/**
 * ============================================================================
 *  CONTENIDO EDITABLE — EL ÚNICO ARCHIVO QUE NECESITAS PARA PERSONALIZAR LA WEB
 * ============================================================================
 *
 *  Aquí están TODOS los textos, fechas, fotografías y la música.
 *  No necesitas tocar HTML, CSS ni JavaScript.
 *
 *  REGLAS RÁPIDAS
 *  - Los textos van entre comillas simples: 'Hola'. Si necesitas un apóstrofo
 *    escríbelo así: 'it\'s'.
 *  - Puedes escribir texto normal: todo se escapa automáticamente (seguro).
 *  - Para un salto de párrafo en textos largos usa una línea vacía.
 *  - Las fotos se referencian desde la carpeta /assets/images/
 *    Ejemplo: 'fotos/nosotros-playa.jpg'  →  /assets/images/fotos/nosotros-playa.jpg
 *  - Todo lo marcado con  [PROVISIONAL]  es contenido de ejemplo: reemplázalo.
 *
 *  Si rompes algo (por ejemplo, olvidas una coma), la web mostrará un aviso.
 *  Compara con el archivo original en el repositorio para encontrar el error.
 * ============================================================================
 */

defined('SEYDI_APP') || exit;

return [

    /* ------------------------------------------------------------------
     *  1. PERSONAS
     * ------------------------------------------------------------------ */
    'her_name'      => 'Seydi',                 // Nombre de ella (como aparece en grande).
    'her_full_name' => 'Seydi',                 // Opcional: nombre completo para la carta.
    'from_name'     => 'Tu nombre',             // [PROVISIONAL] Quien dedica la página.
    'signature'     => 'Tu nombre',             // [PROVISIONAL] Firma manuscrita de la carta y del final.
    'monogram'      => 'S',                     // Inicial del sello (1–2 letras).

    /* ------------------------------------------------------------------
     *  2. FECHA DEL CUMPLEAÑOS
     *  Formato: 'AAAA-MM-DD HH:MM'. La cuenta regresiva llega a cero en ese
     *  momento exacto y después se transforma en un mensaje de celebración.
     *  Zona horaria: https://www.php.net/manual/es/timezones.php
     * ------------------------------------------------------------------ */
    'birthday'          => '2026-12-31 00:00',   // [PROVISIONAL] Cambia por la fecha real.
    'timezone'          => 'America/Mexico_City',// [PROVISIONAL] Tu zona horaria.
    'celebration_hours' => 24,                   // Horas que dura el modo "¡Hoy es tu día!".

    /* ------------------------------------------------------------------
     *  3. INTRODUCCIÓN CINEMATOGRÁFICA (pantalla inicial)
     *  Cada elemento de 'lines' aparece como una frase independiente.
     * ------------------------------------------------------------------ */
    'intro' => [
        'lines' => [
            'Hay personas que hacen que el mundo sea un lugar más bonito…',
            'Esta experiencia es para una de ellas.',
        ],
        'button'     => 'Comenzar',
        'hint'       => 'Mejor con sonido · toca para empezar',
        'ring_text'  => 'una historia que merece ser contada · ',  // Texto que gira alrededor del sello.
    ],

    /* ------------------------------------------------------------------
     *  4. PORTADA (HERO)
     * ------------------------------------------------------------------ */
    'hero' => [
        'eyebrow' => 'Feliz cumpleaños',
        'title'   => 'Seydi',                   // Normalmente su nombre.
        'message' => 'Hoy el mundo celebra el día en que llegó alguien capaz de volverlo más luminoso. Esta página es mi forma de decirte, despacio y con detalle, todo lo que significas para mí.', // [PROVISIONAL]
        'photo'   => ['src' => 'provisionales/foto-02.svg', 'alt' => 'Fotografía principal de Seydi'],
    ],

    /* ------------------------------------------------------------------
     *  5. NUESTRA HISTORIA (línea de tiempo)
     *  Añade, quita o reordena capítulos libremente.
     *  'date' es texto libre: '14 de febrero de 2023', 'Verano 2024', etc.
     * ------------------------------------------------------------------ */
    'story_intro' => 'Algunas historias se escriben despacio, con días pequeños que terminan siendo enormes. Esta es la nuestra.', // [PROVISIONAL]
    'story' => [
        [
            'date'  => '[Fecha] · Cómo comenzó todo',
            'title' => 'El principio',
            'text'  => '[PROVISIONAL] Escribe aquí cómo se conocieron: el lugar, la primera impresión, aquello que todavía recuerdas de ese día.',
            'photo' => ['src' => 'provisionales/foto-01.svg', 'alt' => 'El día en que nos conocimos'],
        ],
        [
            'date'  => '[Fecha] · Primera cita',
            'title' => 'La primera vez',
            'text'  => '[PROVISIONAL] Cuenta cómo fue la primera cita o el primer momento en que supiste que esto era especial.',
            'photo' => ['src' => 'provisionales/foto-05.svg', 'alt' => 'Nuestra primera cita'],
        ],
        [
            'date'  => '[Fecha] · Un recuerdo importante',
            'title' => 'Lo que nos unió',
            'text'  => '[PROVISIONAL] Un momento que los acercó: un viaje, una conversación, un día difícil que superaron juntos.',
            'photo' => ['src' => 'provisionales/foto-04.svg', 'alt' => 'Un recuerdo importante'],
        ],
        [
            'date'  => '[Fecha] · Experiencias compartidas',
            'title' => 'Todo lo que vivimos',
            'text'  => '[PROVISIONAL] Las aventuras, los lugares, las costumbres pequeñas que ahora son solo de ustedes.',
            'photo' => ['src' => 'provisionales/foto-03.svg', 'alt' => 'Experiencias compartidas'],
        ],
        [
            'date'  => 'Hoy',
            'title' => 'Y lo que viene',
            'text'  => '[PROVISIONAL] Cierra la historia mirando hacia adelante: lo que sueñas vivir con ella.',
            'photo' => ['src' => 'provisionales/foto-08.svg', 'alt' => 'Nosotros hoy'],
        ],
    ],

    /* ------------------------------------------------------------------
     *  6. GALERÍA
     *  Las fotos pueden tener cualquier proporción: nunca se deforman.
     *  'caption' es opcional (aparece en el visor a pantalla completa).
     * ------------------------------------------------------------------ */
    'gallery_intro' => 'Fragmentos de luz que quiero guardar para siempre.',
    'gallery' => [
        ['src' => 'provisionales/foto-02.svg', 'alt' => 'Fotografía 1', 'caption' => '[PROVISIONAL] Pie de foto opcional'],
        ['src' => 'provisionales/foto-05.svg', 'alt' => 'Fotografía 2', 'caption' => ''],
        ['src' => 'provisionales/foto-07.svg', 'alt' => 'Fotografía 3', 'caption' => ''],
        ['src' => 'provisionales/foto-04.svg', 'alt' => 'Fotografía 4', 'caption' => ''],
        ['src' => 'provisionales/foto-01.svg', 'alt' => 'Fotografía 5', 'caption' => ''],
        ['src' => 'provisionales/foto-06.svg', 'alt' => 'Fotografía 6', 'caption' => ''],
        ['src' => 'provisionales/foto-03.svg', 'alt' => 'Fotografía 7', 'caption' => ''],
        ['src' => 'provisionales/foto-08.svg', 'alt' => 'Fotografía 8', 'caption' => ''],
        ['src' => 'provisionales/foto-02.svg', 'alt' => 'Fotografía 9', 'caption' => ''],
        ['src' => 'provisionales/foto-05.svg', 'alt' => 'Fotografía 10', 'caption' => ''],
    ],

    /* ------------------------------------------------------------------
     *  7. CARTA
     *  'body': separa los párrafos con una línea vacía.
     * ------------------------------------------------------------------ */
    'letter' => [
        'envelope_label' => 'Para ti, Seydi',
        'envelope_hint'  => 'Toca el sello para abrir',
        'greeting'       => 'Mi amor,',
        'body'           => <<<'TXT'
[PROVISIONAL] Escribe aquí tu carta. Este texto aparecerá poco a poco, como si se escribiera frente a ella.

Puedes usar tantos párrafos como quieras. Deja una línea vacía entre párrafo y párrafo para separarlos.

Habla de lo que admiras de ella, de lo que te enseñó, de lo que sientes cuando la ves sonreír y de lo que deseas para este nuevo año de su vida.
TXT,
        'closing'        => 'Con todo mi amor,',
    ],

    /* ------------------------------------------------------------------
     *  8. MOMENTOS QUE QUIERO RECORDAR
     *  'layout' define la composición de cada tarjeta:
     *    'polaroid' → foto instantánea inclinada con texto manuscrito
     *    'split'    → foto grande + texto editorial al lado
     *    'quote'    → solo texto, como una cita (no necesita foto)
     *    'flip'     → tarjeta que se gira al tocarla para revelar un mensaje
     *    'ticket'   → como la entrada de un evento: ideal para fechas
     * ------------------------------------------------------------------ */
    'moments_intro' => 'No todos los momentos importantes hacen ruido. Estos son algunos de los que llevo conmigo.',
    'moments' => [
        ['layout' => 'polaroid', 'date' => '[Fecha]', 'title' => '[PROVISIONAL] Aquella tarde', 'text' => 'Describe un momento sencillo que nunca vas a olvidar.', 'photo' => ['src' => 'provisionales/foto-04.svg', 'alt' => 'Aquella tarde']],
        ['layout' => 'quote',    'date' => '',        'title' => '',                                'text' => '[PROVISIONAL] Una frase que ella dijo y que se te quedó grabada.', 'author' => 'Seydi'],
        ['layout' => 'split',    'date' => '[Fecha]', 'title' => '[PROVISIONAL] Nuestro lugar',     'text' => 'Un sitio que ahora significa algo distinto porque estuvieron ahí juntos.', 'photo' => ['src' => 'provisionales/foto-05.svg', 'alt' => 'Nuestro lugar']],
        ['layout' => 'flip',     'date' => '',        'title' => 'Toca para descubrir',              'text' => '[PROVISIONAL] Un pequeño secreto, una promesa o un recuerdo divertido.', 'photo' => ['src' => 'provisionales/foto-01.svg', 'alt' => 'Un recuerdo escondido']],
        ['layout' => 'ticket',   'date' => '[Fecha]', 'title' => '[PROVISIONAL] El concierto / el viaje', 'text' => 'Un evento al que fueron juntos. Lugar y hora, como en la entrada.', 'place' => '[Lugar]'],
    ],

    /* ------------------------------------------------------------------
     *  9. CUENTA REGRESIVA
     * ------------------------------------------------------------------ */
    'countdown' => [
        'title_before' => 'Falta muy poco',
        'text_before'  => 'Cada segundo nos acerca a tu día.',
        'title_after'  => '¡Hoy es tu día!',
        'text_after'   => 'Que este nuevo año te devuelva multiplicado todo lo bonito que regalas.',
        'title_past'   => 'Celebrándote siempre',
        'text_past'    => 'Tu cumpleaños ya pasó, pero celebrarte no tiene fecha.',
    ],

    /* ------------------------------------------------------------------
     *  10. MENSAJE FINAL
     * ------------------------------------------------------------------ */
    'final' => [
        'eyebrow' => 'Para terminar… o para empezar',
        'lines'   => [
            '[PROVISIONAL] Gracias por existir,',
            'por cada risa, cada abrazo y cada día compartido.',
            'Feliz cumpleaños.',
        ],
        'photo'   => ['src' => 'provisionales/foto-06.svg', 'alt' => 'Nosotros'],
        'closing' => 'Te quiero más de lo que esta página puede decir.', // [PROVISIONAL]
    ],

    /* ------------------------------------------------------------------
     *  11. SORPRESA OCULTA
     *  Hay estrellas escondidas en la página. Al encontrarlas todas se
     *  desbloquea este mensaje secreto.
     * ------------------------------------------------------------------ */
    'secret' => [
        'title'   => 'Encontraste todas las estrellas',
        'message' => '[PROVISIONAL] Un mensaje secreto solo para ella: un vale por una cena, una promesa, un plan sorpresa…',
    ],

    /* ------------------------------------------------------------------
     *  12. RESPUESTA (formulario privado)
     *  Ella puede dejarte un mensaje. Se guarda en /storage/mensajes.json
     *  (carpeta protegida). Puedes leerlo desde el administrador de
     *  archivos de InfinityFree. Pon 'enabled' => false para ocultarlo.
     * ------------------------------------------------------------------ */
    'reply' => [
        'enabled'     => true,
        'title'       => '¿Me dejas unas palabras?',
        'placeholder' => 'Escribe lo que quieras… solo yo lo leeré.',
        'button'      => 'Enviar',
        'thanks'      => 'Recibido. Lo guardaré para siempre.',
    ],

    /* ------------------------------------------------------------------
     *  13. MÚSICA
     *  Opción A (recomendada, control total y volumen):
     *    Sube un archivo de audio que tengas derecho a usar a
     *    /assets/audio/ y escribe su nombre en 'local_file'.
     *    Si el archivo existe, se usa automáticamente.
     *  Opción B (por defecto): reproductor oficial de Spotify integrado,
     *    controlado desde el tocadiscos de la página. Spotify reproduce la
     *    canción completa si ella tiene sesión iniciada en Spotify en ese
     *    navegador; si no, reproduce una vista previa.
     * ------------------------------------------------------------------ */
    'music' => [
        'local_file'       => 'nuestra-cancion.mp3',      // Dentro de /assets/audio/
        'spotify_track_id' => '3AeicLnm55RqcXGBKYQolM',   // Parte final del enlace de Spotify.
        'title'            => 'Nuestra canción',          // Texto que muestra el reproductor.
        'artist'           => 'Toca para escuchar',
        'volume'           => 0.6,                        // 0.0 – 1.0 (solo audio local)
    ],

    /* ------------------------------------------------------------------
     *  14. SEO / COMPARTIR
     * ------------------------------------------------------------------ */
    'seo' => [
        'title'       => 'Feliz cumpleaños, Seydi',
        'description' => 'Una experiencia hecha a mano para celebrar a Seydi.',
        'share_image' => 'provisionales/foto-06.svg', // Ideal: un JPG de 1200×630 (las redes no muestran SVG).
    ],
];
