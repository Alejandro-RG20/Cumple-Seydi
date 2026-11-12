"""Genera la actualización 2 (textos + fotos enderezadas) para importar en phpMyAdmin.
Uso: python3 -I tools/build-update-2.py CARPETA_FOTOS_GRANDES CARPETA_SALIDA"""
import sys, io, gzip
from PIL import Image

src, out = sys.argv[1].rstrip('/'), sys.argv[2].rstrip('/')

def q(s):
    if s is None:
        return 'NULL'
    return "'" + s.replace('\\', '\\\\').replace("'", "\\'").replace('\n', '\\n').replace('\r', '\\r') + "'"

head = ["SET NAMES utf8mb4;", "SET time_zone = '+00:00';"]

# ---------------------------------------------------------------- textos
alts_caps = {
    'seydi-tapandose-la-cara': ('Seydi tapándose la cara con la mano', 'La que se esconde de la cámara, pero nunca de mí.'),
    'nosotros-tiktokers': ('Seydi y Alejandro en una selfie', 'Nosotros, versión tiktokers.'),
    'puertas-el-cielo-crucero': ('Seydi y Alejandro en Puertas del Cielo, El Crucero', 'Puertas del Cielo, El Crucero: tan alto como lo que siento por ti.'),
    '15-de-luisary': ('Seydi y Alejandro en los 15 años de Luisary', 'En los 15 de Luisary, la más bonita de la fiesta eras tú.'),
    'yandeles': ('Seydi y Alejandro con lentes de sol en Yandeles', 'Yandeles: lentes oscuros y sonrisas de verdad.'),
    'jinotepe': ('Seydi y Alejandro en Jinotepe', 'Jinotepe, tú y yo: no hace falta nada más.'),
    'iglesia': ('Seydi y Alejandro sentados juntos en la iglesia', 'Juntos en la iglesia, dando gracias por lo nuestro.'),
    'boquete': ('Seydi guiñando un ojo junto a Alejandro en Boquete', 'Boquete y tu cara de traviesa, mi combinación favorita.'),
    'chiquita': ('Seydi de bebé con vestido blanco', 'Desde chiquita ya tenías esa sonrisa que ilumina todo.'),
    'maquillando': ('Seydi y Alejandro con los labios pintados', 'El día que me convertiste en tu modelo de maquillaje.'),
    'un-dia-normal': ('Alejandro con mascarilla negra y Seydi lanzando un beso', 'Un día normal: tú preciosa y yo con mascarilla.'),
    'en-mi-cumple': ('Seydi y Alejandro en el cumpleaños de Alejandro', 'En mi cumpleaños, el mejor regalo fuiste tú.'),
    'domingo-de-novios': ('Seydi y Alejandro abrazados un domingo', 'Domingo de novios: sin planes, solo nosotros.'),
    'piedras-azules-mirador': ('Seydi y Alejandro en el mirador Piedras Azules', 'Mirador Piedras Azules, con la mejor vista de todas: tú.'),
    'atardecer-crucero': ('Atardecer naranja en El Crucero', 'Un atardecer en El Crucero que todavía guardo.'),
}
t = head + [
    "-- Actualización 2 · textos (no borra mensajes ni cambia la contraseña del panel)",
]
for slug, (alt, cap) in alts_caps.items():
    t.append(f"UPDATE `photos` SET `alt` = {q(alt)} WHERE `slug` = {q(slug)};")
    t.append(f"UPDATE `gallery` SET `caption` = {q(cap)} WHERE `photo_slug` = {q(slug)};")
# foto duplicada
t.append("DELETE FROM `gallery` WHERE `photo_slug` = 'jinotepe-2';")
t.append("DELETE FROM `photos` WHERE `slug` = 'jinotepe-2' AND `slug` NOT IN (SELECT `photo_slug` FROM (SELECT `photo_slug` FROM `story` WHERE `photo_slug` IS NOT NULL UNION SELECT `photo_slug` FROM `moments` WHERE `photo_slug` IS NOT NULL) AS usadas);")

story = [
    (5, 'Antes de todo · Chiquita', 'Donde empezó tu luz', 'Antes de conocerte ya existía esta sonrisa. Mirar esta foto me hace pensar en todo lo que has crecido, en todo lo que has vivido y en lo afortunado que soy de que, entre tantos caminos, el tuyo se cruzara con el mío.', 'chiquita'),
    (15, 'Nuestros lugares · Jinotepe, Boquete, El Crucero', 'Los lugares que ahora son nuestros', 'Jinotepe, Boquete, Puertas del Cielo, Piedras Azules… Cada lugar al que fuimos juntos dejó de ser un punto en el mapa y se convirtió en un pedacito de nuestra historia. No importa a dónde vayamos: contigo cualquier sitio se siente como casa.', 'puertas-el-cielo-crucero'),
    (25, 'Domingos de novios', 'Los días sin planes', 'Me encantan los días en los que no hacemos nada especial y aun así terminan siendo especiales. Un domingo contigo, sin prisa y sin plan, vale más que cualquier salida.', 'domingo-de-novios'),
    (35, 'En mi cumpleaños', 'Mi mejor regalo', 'Hasta en mi cumpleaños la protagonista fuiste tú. Hoy me toca devolverte un poquito de todo lo que me das: esta página, este día y todo mi cariño.', 'en-mi-cumple'),
    (45, 'Lo que de verdad importa', 'Gracias por lo nuestro', 'Doy gracias cada día por haberte puesto en mi camino. Verte a mi lado en los lugares y momentos que importan me recuerda lo bendecido que soy de tenerte.', 'iglesia'),
]
for pos, d, title, body, slug in story:
    t.append("INSERT INTO `story` (`position`, `date_label`, `title`, `body`, `photo_slug`) SELECT "
             f"{pos}, {q(d)}, {q(title)}, {q(body)}, {q(slug)} FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `story` WHERE `title` = {q(title)});")

moments = [
    (60, 'polaroid', 'Un día de risas', 'Tu modelo favorito', 'Me pintaste los labios y yo me dejé, porque verte reír así vale cualquier cosa.', '', '', 'maquillando'),
    (70, 'quote', '', '', 'Eres mi lugar favorito, más bonito que cualquier mirador.', 'Alejandro', '', None),
    (80, 'split', 'El Crucero', 'Nuestro atardecer', 'Ese sol escondiéndose detrás de los árboles, el cielo naranja y tú a mi lado. Hay paisajes que se miran y otros que se sienten: ese fue de los segundos.', '', '', 'atardecer-crucero'),
    (90, 'flip', '', 'Toca para descubrir', 'Prometo seguir haciendo el ridículo con tal de verte sonreír. ¡Mascarillas incluidas!', '', '', 'un-dia-normal'),
    (100, 'ticket', 'Muy pronto', 'Piedras Azules, otra vez', 'Válida para volver juntos al mirador y tomarnos mil fotos más.', '', 'Mirador Piedras Azules', None),
]
for pos, lay, d, title, body, author, place, slug in moments:
    t.append("INSERT INTO `moments` (`position`, `layout`, `date_label`, `title`, `body`, `author`, `place`, `photo_slug`) SELECT "
             f"{pos}, {q(lay)}, {q(d)}, {q(title)}, {q(body)}, {q(author)}, {q(place)}, {q(slug)} FROM DUAL "
             f"WHERE NOT EXISTS (SELECT 1 FROM `moments` WHERE `body` = {q(body)});")
open(out + '/actualizacion-2-textos.sql', 'w').write('\n'.join(t) + '\n')

# ---------------------------------------------------------------- fotos enderezadas
f = head + ["-- Actualización 2 · fotos que se subieron giradas (se enderezan 90° a la derecha)"]
for slug in ['15-de-luisary', 'jinotepe', 'piedras-azules-mirador', 'puertas-el-cielo-crucero', 'yandeles', 'maquillando']:
    im = Image.open(f'{src}/{slug}.webp').convert('RGB').rotate(-90, expand=True)
    big = io.BytesIO(); im.save(big, 'WEBP', quality=86, method=6)
    th = im.copy(); th.thumbnail((800, 800), Image.LANCZOS)
    small = io.BytesIO(); th.save(small, 'WEBP', quality=76, method=6)
    f.append(f"UPDATE `photos` SET `data` = 0x{big.getvalue().hex()}, `thumb` = 0x{small.getvalue().hex()}, "
             f"`width` = {im.width}, `height` = {im.height}, `thumb_width` = {th.width}, `thumb_height` = {th.height}, "
             f"`bytes` = {len(big.getvalue())} WHERE `slug` = {q(slug)};")
    print(slug, im.size, len(big.getvalue()) // 1024, 'KB')
data = ('\n'.join(f) + '\n').encode()
open(out + '/actualizacion-2-fotos.sql', 'wb').write(data)
open(out + '/actualizacion-2-fotos.sql.gz', 'wb').write(gzip.compress(data, 9))
