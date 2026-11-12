<?php
/**
 * Experiencia de cumpleaños — punto de entrada.
 *
 * Estructura:
 *   includes/bootstrap.php   → configuración, sesión y seguridad
 *   includes/header.php      → <head>, navegación y capas globales
 *   includes/sections/*.php  → cada sección de la experiencia
 *   includes/footer.php      → reproductor, visor, modales y scripts
 *
 * Para cambiar textos y fotos edita SOLO data/content.php
 */

define('SEYDI_APP', true);
require __DIR__ . '/includes/bootstrap.php';

$accessError = handle_access_attempt();
if (access_required()) {
    require __DIR__ . '/includes/gate.php';
    exit;
}

require __DIR__ . '/includes/header.php';

require __DIR__ . '/includes/sections/intro.php';

$sections = ['hero', 'story', 'gallery', 'letter', 'moments', 'countdown', 'final'];
echo '<main id="contenido">';
foreach ($sections as $section) {
    require __DIR__ . '/includes/sections/' . $section . '.php';
}
echo '</main>';

require __DIR__ . '/includes/footer.php';
