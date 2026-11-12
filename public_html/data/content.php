<?php
/**
 * ============================================================================
 *  CONTENIDO INICIAL DE LA WEB
 * ============================================================================
 *
 *  La web lee TODO desde la base de datos MySQL y se edita desde el panel
 *  /admin (usuario y contraseña). Este archivo cumple dos funciones:
 *
 *   1. Es la "semilla": con él se generó database/seydi.sql (textos + fotos).
 *   2. Es el respaldo: si la base de datos no está disponible, la web
 *      muestra estos textos para no quedarse en blanco.
 *
 *  Las fotos con prefijo 'db:' se guardan en la tabla `photos` de MySQL.
 * ============================================================================
 */

defined('SEYDI_APP') || exit;

return [

    /* 1. PERSONAS ------------------------------------------------------- */
    'her_name'      => 'Seydi',
    'her_full_name' => 'Seydi',
    'from_name'     => 'Alejandro Rodríguez',
    'signature'     => 'Alejandro',
    'monogram'      => 'S',
    'age'           => '18',

    /* 2. FECHA (hora de Nicaragua) ------------------------------------- */
    'birthday'          => '2026-11-12 00:00',
    'timezone'          => 'America/Managua',
    'celebration_hours' => 24,

    /* 3. INTRODUCCIÓN --------------------------------------------------- */
    'intro' => [
        'lines' => [
            'Hay personas que hacen que el mundo sea un lugar más bonito…',
            'Esta experiencia es para una de ellas.',
        ],
        'button'    => 'Comenzar',
        'hint'      => 'Mejor con sonido · toca para empezar',
        'ring_text' => '18 años de luz · una historia que merece ser contada · ',
    ],

    /* 4. PORTADA -------------------------------------------------------- */
    'hero' => [
        'eyebrow' => 'Feliz cumpleaños número 18',
        'title'   => 'Seydi',
        'message' => 'Hoy cumples 18 y el mundo celebra el día en que llegó alguien capaz de volverlo más luminoso. Esta página es mi forma de decirte, despacio y con detalle, todo lo que significas para mí.',
        'photo'   => ['src' => 'db:seydi-beso', 'alt' => 'Seydi'],
    ],

    /* 5. NUESTRA HISTORIA ---------------------------------------------- */
    'story_intro' => 'Algunas historias se escriben despacio, con días pequeños que terminan siendo enormes. Esta es la nuestra, contada a mi manera.',
    'story' => [
        [
            'date'  => 'Capítulo uno · El comienzo',
            'title' => 'Llegaste tú',
            'text'  => 'No sé explicar exactamente en qué momento pasó, pero un día me di cuenta de que mis días tenían otro color. Empezaste siendo una sonrisa que buscaba sin darme cuenta y terminaste siendo la persona en la que pienso al despertar.',
            'photo' => ['src' => 'db:juntos-al-anochecer', 'alt' => 'Seydi y Alejandro sonriendo al anochecer'],
        ],
        [
            'date'  => 'Capítulo dos · Bajo el mismo cielo',
            'title' => 'Nuestras tardes azules',
            'text'  => 'Hay cielos que se olvidan y cielos que se quedan para siempre. Los nuestros, con las nubes oscuras y ese último pedazo de sol, son de los que se quedan. Contigo, cualquier atardecer se vuelve un recuerdo.',
            'photo' => ['src' => 'db:beso-bajo-el-cielo', 'alt' => 'Alejandro besa la mejilla de Seydi bajo el cielo nocturno'],
        ],
        [
            'date'  => 'Capítulo tres · Tu risa',
            'title' => 'La risa que me desarma',
            'text'  => 'Me encanta que no tengas miedo de ser tú: la que hace caras, la que se ríe con todo el cuerpo, la que convierte una foto seria en una carcajada. Esa versión tuya es mi favorita.',
            'photo' => ['src' => 'db:risas-y-brackets', 'alt' => 'Seydi riendo junto a Alejandro'],
        ],
        [
            'date'  => 'Capítulo cuatro · Tú',
            'title' => 'Todo lo que eres',
            'text'  => 'Eres fuerza y ternura al mismo tiempo. Eres la mirada que lo dice todo sin hablar, la que cuida, la que sueña en grande. Verte crecer y convertirte en la mujer que eres es uno de los regalos más bonitos de mi vida.',
            'photo' => ['src' => 'db:seydi-mirada', 'alt' => 'Retrato de Seydi'],
        ],
        [
            'date'  => 'Hoy · 18 años',
            'title' => 'Y lo que viene',
            'text'  => 'Hoy empieza una etapa nueva para ti, y quiero estar ahí para verla. Que estos 18 sean el inicio de todo lo que sueñas: yo voy a estar a tu lado, aplaudiendo cada paso.',
            'photo' => ['src' => 'db:seydi-espejo', 'alt' => 'Seydi frente al espejo'],
        ],
    ],

    /* 6. GALERÍA -------------------------------------------------------- */
    'gallery_intro' => 'Fragmentos de luz que quiero guardar para siempre.',
    'gallery' => [
        ['src' => 'db:seydi-beso',         'alt' => 'Seydi lanzando un beso',               'caption' => 'Ese beso a la cámara que en realidad era para mí.'],
        ['src' => 'db:juntos-al-anochecer', 'alt' => 'Seydi y Alejandro al anochecer',       'caption' => 'Tú, yo y un cielo que parecía pintado.'],
        ['src' => 'db:seydi-mirada',        'alt' => 'Retrato de Seydi',                     'caption' => 'Esa mirada que me dice todo sin decir nada.'],
        ['src' => 'db:beso-bajo-el-cielo',  'alt' => 'Beso en la mejilla bajo el cielo',     'caption' => 'Mi lugar favorito: a tu lado.'],
        ['src' => 'db:seydi-espejo',        'alt' => 'Seydi frente al espejo',               'caption' => 'Siempre con estilo propio.'],
        ['src' => 'db:risas-y-brackets',    'alt' => 'Seydi riendo junto a Alejandro',       'caption' => 'La risa más bonita del mundo, con brackets y todo.'],
        ['src' => 'db:seydi-traviesa',      'alt' => 'Seydi sacando la lengua',              'caption' => 'Tu lado travieso, mi favorito.'],
    ],

    /* 7. CARTA ---------------------------------------------------------- */
    'letter' => [
        'envelope_label' => 'Para ti, Seydi',
        'envelope_hint'  => 'Toca el sello para abrir',
        'greeting'       => 'Mi amor,',
        'body'           => <<<'TXT'
Hoy cumples 18 años y quise regalarte algo que no se rompa ni se pierda: un lugar hecho solo para ti, con nuestras fotos, nuestras risas y todo lo que a veces no sé decirte en voz alta.

Gracias por ser como eres. Por tu risa que contagia, por tus ocurrencias, por la forma en que miras las cosas y por la paciencia que me tienes. Gracias por elegirme y por dejarme ser parte de tu vida.

Admiro a la mujer en la que te estás convirtiendo: valiente, auténtica, cariñosa y con un corazón enorme. Nunca dejes que nadie apague esa luz que tienes, porque es de las cosas más bonitas que he visto.

Te deseo un año lleno de sueños cumplidos, de días tranquilos y de noches bajo cielos bonitos. Y donde sea que te lleven estos 18, quiero seguir caminando a tu lado.

Feliz cumpleaños, mi niña. Te quiero muchísimo.
TXT,
        'closing'        => 'Con todo mi amor,',
    ],

    /* 8. MOMENTOS ------------------------------------------------------- */
    'moments_intro' => 'No todos los momentos importantes hacen ruido. Estos son algunos de los que llevo conmigo.',
    'moments' => [
        ['layout' => 'polaroid', 'date' => 'Un instante cualquiera', 'title' => 'Tu lado travieso', 'text' => 'Puedes estar seria un segundo y al siguiente estar sacándome la lengua. Así eres, y así te quiero.', 'photo' => ['src' => 'db:seydi-traviesa', 'alt' => 'Seydi sacando la lengua']],
        ['layout' => 'quote', 'date' => '', 'title' => '', 'text' => 'Contigo aprendí que los mejores momentos no se planean: simplemente pasan cuando estamos juntos.', 'author' => 'Alejandro'],
        ['layout' => 'split', 'date' => 'Al caer la tarde', 'title' => 'Nuestro cielo', 'text' => 'Ese cielo azul oscuro, la hierba, tu cabeza en mi hombro y la sensación de que el mundo podía esperar. Si pudiera repetir un día, sería uno así contigo.', 'photo' => ['src' => 'db:juntos-al-anochecer', 'alt' => 'Seydi y Alejandro bajo el cielo azul']],
        ['layout' => 'flip', 'date' => '', 'title' => 'Toca para descubrir', 'text' => 'Te debo una cita especial para celebrar tus 18. Tú eliges el lugar; yo pongo el resto.', 'photo' => ['src' => 'db:seydi-espejo', 'alt' => 'Seydi frente al espejo']],
        ['layout' => 'ticket', 'date' => '12 de noviembre de 2026', 'title' => 'Tus 18 años', 'text' => 'Entrada válida para celebrar contigo hoy y todos los cumpleaños que vengan.', 'place' => 'Donde tú estés'],
    ],

    /* 9. CUENTA REGRESIVA ---------------------------------------------- */
    'countdown' => [
        'title_before' => 'Falta muy poco',
        'text_before'  => 'Cada segundo nos acerca a tus 18.',
        'title_after'  => '¡Hoy es tu día!',
        'text_after'   => 'Feliz cumpleaños número 18. Que este nuevo año te devuelva multiplicado todo lo bonito que regalas.',
        'title_past'   => 'Celebrándote siempre',
        'text_past'    => 'Tu cumpleaños ya pasó, pero celebrarte no tiene fecha.',
    ],

    /* 10. MENSAJE FINAL ------------------------------------------------- */
    'final' => [
        'eyebrow' => 'Para terminar… o para empezar',
        'lines'   => [
            'Gracias por existir,',
            'por cada risa, cada abrazo y cada día compartido.',
            'Feliz cumpleaños, Seydi.',
        ],
        'photo'   => ['src' => 'db:beso-bajo-el-cielo', 'alt' => 'Seydi y Alejandro'],
        'closing' => 'Te quiero más de lo que esta página puede decir.',
    ],

    /* 11. SORPRESA OCULTA ---------------------------------------------- */
    'secret' => [
        'title'   => 'Encontraste todas las estrellas',
        'message' => 'Sabía que las encontrarías. Este secreto vale por un abrazo largo, una salida a donde tú quieras y un "sí" a lo que me pidas hoy. Feliz cumpleaños, mi amor.',
    ],

    /* 12. RESPUESTA ----------------------------------------------------- */
    'reply' => [
        'enabled'     => true,
        'title'       => '¿Me dejas unas palabras?',
        'placeholder' => 'Escribe lo que quieras… solo yo lo leeré.',
        'button'      => 'Enviar',
        'thanks'      => 'Recibido. Lo guardaré para siempre.',
    ],

    /* 13. MÚSICA (suena sola al pulsar "Comenzar") ---------------------- */
    'music' => [
        'local_file'       => 'tu-cancion.mp3',
        'spotify_track_id' => '3AeicLnm55RqcXGBKYQolM',
        'title'            => 'Tu canción',
        'artist'           => 'Para ti, Seydi',
        'volume'           => 0.6,
    ],

    /* 14. SEO ----------------------------------------------------------- */
    'seo' => [
        'title'       => 'Feliz cumpleaños, Seydi',
        'description' => 'Una experiencia hecha a mano por Alejandro Rodríguez para celebrar los 18 años de Seydi.',
        'share_image' => 'db:juntos-al-anochecer',
    ],
];
