<?php
/**
 * ============================================================================
 *  CONFIGURACIÓN TÉCNICA — PLANTILLA
 * ============================================================================
 *
 *  1. Copia este archivo como  config/config.php  (mismo directorio).
 *  2. Edita los valores de config.php. Ese archivo NO se sube al repositorio
 *     (está en .gitignore) y está bloqueado para el navegador por .htaccess.
 *
 *  Si config.php no existe, la web usa esta plantilla con valores seguros.
 *
 *  ¿Por qué no un archivo .env?
 *  InfinityFree no ofrece variables de entorno configurables. Un archivo PHP
 *  que devuelve un array es la alternativa más compatible: aunque alguien
 *  intentara abrirlo desde el navegador, PHP lo ejecuta y no muestra nada,
 *  y además .htaccess bloquea el acceso a toda la carpeta /config.
 * ============================================================================
 */

defined('SEYDI_APP') || exit;

return [
    // URL pública final, sin barra al final. Se usa para SEO y para compartir.
    // Ejemplo: 'https://seydi.infinityfreeapp.com'
    'site_url' => '',

    // Código de acceso opcional. Déjalo vacío ('') para que la página sea pública.
    // Si escribes uno (p. ej. la fecha en que se conocieron: '14022023'),
    // la página pedirá ese código antes de mostrarse.
    'access_code' => '',
    'access_hint' => 'Pista: una fecha que solo nosotros conocemos.',

    // false = pide a los buscadores que NO indexen la página (recomendado para algo personal).
    'indexable' => false,

    // true solo mientras pruebas: muestra errores de PHP en pantalla.
    'debug' => false,

    // Límite de mensajes del formulario de respuesta por visitante y hora.
    'reply_rate_limit' => 5,

    // ------------------------------------------------------------------
    //  BASE DE DATOS MySQL (InfinityFree → Control Panel → MySQL Databases)
    //  Copia aquí los datos que te muestra el panel. Ejemplo:
    //    'host' => 'sql123.infinityfree.com',
    //    'name' => 'if0_12345678_seydi',
    //    'user' => 'if0_12345678',
    //    'pass' => 'la contraseña de tu cuenta (vPanel)',
    // ------------------------------------------------------------------
    'db' => [
        'host' => '',
        'port' => 3306,
        'name' => '',
        'user' => '',
        'pass' => '',
    ],
];
