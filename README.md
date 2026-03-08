# Bogensportverein 1960 Plauen e.V. – Website

Offizielle Website des Bogensportvereins 1960 Plauen e.V.

## Technologie

Statische Website ohne Build-Tools oder Frameworks:

- **HTML5** – 10 Seiten
- **CSS3** – Custom Properties, Grid, Flexbox, Animationen
- **Vanilla JavaScript** – Navigation, Lightbox, Scroll-Animationen
- **Google Fonts** – Inter (wird über CDN geladen)

## Abhängigkeiten

Keine Installation nötig. Die Website besteht aus rein statischen Dateien.

| Abhängigkeit | Typ | Beschreibung |
|---|---|---|
| Moderner Webbrowser | Laufzeit | Chrome, Firefox, Safari oder Edge (aktuelle Version) |
| Python 3 **oder** ein beliebiger HTTP-Server | Entwicklung | Zum lokalen Hosten (optional, Dateien können auch direkt geöffnet werden) |
| Google Fonts (Inter) | CDN | Wird automatisch vom Browser geladen, Fallback auf System-Fonts |

## Lokal hosten

### Option 1: Python (empfohlen)

Python ist auf den meisten Systemen vorinstalliert.

```bash
# Im Projektverzeichnis:
python3 -m http.server 8080
```

Die Website ist dann erreichbar unter: **http://localhost:8080**

### Option 2: Node.js (npx)

```bash
npx serve -p 8080
```

### Option 3: PHP

```bash
php -S localhost:8080
```

### Option 4: Dateien direkt öffnen

Die HTML-Dateien können auch direkt im Browser geöffnet werden (Doppelklick auf `index.html`). Dabei funktionieren alle Features, da keine serverseitigen Abhängigkeiten bestehen.

## Projektstruktur

```
website_bsv/
├── index.html            # Startseite (Willkommen, Termine, Vorstand)
├── training.html         # Trainingszeiten Sommer/Winter
├── blog.html             # Wettkampfberichte
├── gallery.html          # Bildergalerie mit Lightbox
├── sponsors.html         # Sponsoren
├── information.html      # Links zu Verbänden und Vereinen
├── anfaengerkurs.html    # Anfängerkurs-Infos und Termine
├── contact.html          # Kontaktdaten
├── imprint.html          # Impressum
├── datenschutz.html      # Datenschutzerklärung
├── Logo_Verein_2_FK.JPG  # Vereinslogo (Header)
├── css/
│   └── style.css         # Gesamtes Styling
├── js/
│   └── main.js           # Navigation, Lightbox, Animationen
└── images/
    ├── bsv_logo_web.png
    ├── top/              # Hero-Banner
    ├── index/            # Vorstand-Fotos
    ├── training/         # Trainingsplatz-Foto
    └── gallery/          # Galerie-Bilder
        └── 2017/
            ├── schneeberg/
            ├── dm_ak/
            └── dm/
```

## Browser-Kompatibilität

Die Website nutzt moderne CSS-Features (Custom Properties, `clamp()`, `clip-path`, `backdrop-filter`). Unterstützt werden:

- Chrome / Edge 88+
- Firefox 78+
- Safari 14+
