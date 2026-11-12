/* Marca que JavaScript está disponible antes de pintar (evita parpadeos).
   Archivo externo para cumplir la Content-Security-Policy (sin scripts en línea). */
(function (d) {
    var h = d.documentElement;
    // Sin soporte de módulos ES el contenido se muestra completo, sin intro.
    if (!('noModule' in d.createElement('script'))) return;
    h.className = h.className.replace(/\bno-js\b/, '') + ' js is-intro';
})(document);
