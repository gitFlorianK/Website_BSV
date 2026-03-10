# Bogensportverein 1960 Plauen e.V. – Website

Offizielle Website des Bogensportvereins 1960 Plauen e.V.

## Technologie

- **PHP 8+** – Shared Includes für Header/Footer, Custom Pages
- **CSS3** – Custom Properties, Grid, Flexbox, Animationen
- **Vanilla JavaScript** – Navigation, Lightbox, Scroll-Animationen, CMS-Anbindung
- **SQLite** – CMS-Backend mit Nutzerverwaltung
- **Google Fonts** – Inter (CDN)

## CMS

Die Website verfügt über ein integriertes Content-Management-System unter `/admin/`.

### Funktionen

- **Beiträge verwalten** – Blog-Berichte und Galerie-Alben erstellen, bearbeiten und veröffentlichen
- **Terminverwaltung** – Termine auf der Startseite pflegen
- **Seiteninhalte bearbeiten** – Alle Seitentexte über das CMS pflegen: Trainingszeiten, Anfängerkurse (Termine, Preise, Status), Sponsoren, Vorstand, Kontaktdaten, Impressum, Datenschutz, externe Links
- **Eigene Seiten** – Neue Seiten mit frei wählbarem Inhalt erstellen, optional in der Navigation anzeigen
- **Bild-Upload** – Bilder direkt im CMS hochladen (max. 5 MB, JPG/PNG/WebP/GIF); große Bilder werden automatisch im Browser komprimiert und verkleinert
- **Nutzerverwaltung** – Rollen-basiertes System

### Sicherheit

- **Session-Schutz** – `session_regenerate_id(true)` nach Login verhindert Session Fixation
- **Rate-Limiting** – Max. 10 Login-Versuche in 15 Minuten (Session-basiert)
- **XSS-Schutz** – Doppelte Absicherung: serverseitig `sanitizeContentHtml()` via `strip_tags`-Allowlist, clientseitig `sanitizeHtml()` entfernt Script/Style/Event-Handler
- **CSRF-Schutz** – Token-basiert für alle POST-Formulare
- **CORS** – Same-Origin-Only (kein `Access-Control-Allow-Origin: *`)
- **Security-Header** – `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy: strict-origin-when-cross-origin`
- **API-Pagination** – `LIMIT/OFFSET` mit Maximum (200) verhindert Speicherprobleme
- **Standard-Passwort-Warnung** – Dashboard zeigt Warnung, wenn `admin2024` noch aktiv ist

### Rollen

| Rolle | Rechte |
|---|---|
| **Admin** | Voller Zugriff: Beiträge, Termine, Seiteninhalte, Benutzerverwaltung |
| **Redakteur** | Beiträge und Termine erstellen/bearbeiten |

### Standard-Login

- **Benutzer:** `admin`
- **Passwort:** `admin2024`
- Nach erstem Login bitte Passwort ändern!

## Abhängigkeiten

| Abhängigkeit | Typ | Beschreibung |
|---|---|---|
| PHP 8.0+ mit SQLite | Server | Für CMS-Backend, API und Seiten-Rendering |
| Moderner Webbrowser | Laufzeit | Chrome, Firefox, Safari oder Edge (aktuelle Version) |
| Google Fonts (Inter) | CDN | Fallback auf System-Fonts |

## Lokal starten

### HTTPS (empfohlen)

Voraussetzung: `stunnel` und `openssl` installiert.

```bash
# 1. Selbst-signiertes Zertifikat erstellen (einmalig):
mkdir -p .ssl
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout .ssl/server.key -out .ssl/server.crt \
  -subj "/CN=BSV-Plauen-Dev" \
  -addext "subjectAltName=DNS:localhost,IP:0.0.0.0,IP:<EIGENE-IP>"
cat .ssl/server.key .ssl/server.crt > .ssl/server.pem

# 2. stunnel-Konfiguration erstellen (.ssl/stunnel.conf):
#    [https]
#    accept = 0.0.0.0:8443
#    connect = 127.0.0.1:8000
#    cert = <PFAD>/.ssl/server.pem

# 3. Server starten:
php -S 127.0.0.1:8000 &
stunnel .ssl/stunnel.conf
```

### HTTP (einfach)

```bash
php -S 0.0.0.0:8000
```

| Zugriff | URL |
|---|---|
| HTTPS lokal | https://localhost:8443 |
| HTTPS Netzwerk | https://&lt;IP-Adresse&gt;:8443 |
| HTTP lokal | http://localhost:8000 |
| CMS-Login | https://localhost:8443/admin/ |

> **Hinweis:** Bei selbst-signierten Zertifikaten zeigt der Browser eine Warnung – diese kann übersprungen werden.
> Firewall-Freigabe falls nötig: `sudo firewall-cmd --add-port=8443/tcp`

## Projektstruktur

```
website_bsv/
├── index.php               # Startseite (Willkommen, Termine, Vorstand)
├── aktuelles.php           # Blog + Galerie (CMS-gesteuert)
├── training.php            # Trainingszeiten Sommer/Winter
├── sponsors.php            # Sponsoren
├── information.php         # Links zu Verbänden und Vereinen
├── anfaengerkurs.php       # Anfängerkurs-Infos und Termine
├── contact.php             # Kontaktdaten
├── imprint.php             # Impressum
├── datenschutz.php         # Datenschutzerklärung
├── page.php                # Template für eigene CMS-Seiten
├── includes/
│   ├── header.php          # Gemeinsamer Header (Head, Navigation, Security-Header)
│   ├── footer.php          # Gemeinsamer Footer (Kontakt, Links, Scripts)
│   └── bootstrap.php       # Minimaler Bootstrap für Frontend-DB-Zugriff
├── Logo_Verein_2_FK.JPG    # Vereinslogo (Header)
├── css/
│   └── style.css           # Gesamtes Styling
├── js/
│   ├── main.js             # Navigation, Lightbox, Animationen, Hilfsfunktionen
│   ├── content.js          # CMS-Content-Loader (alle Seiten)
│   ├── aktuelles.js        # CMS-Content-Loader (Aktuelles-Seite)
│   └── termine.js          # Termin-Loader (Startseite)
├── admin/
│   ├── index.php           # Login
│   ├── dashboard.php       # Dashboard mit Statistiken
│   ├── posts.php           # Beitragsübersicht
│   ├── edit-post.php       # Beitrag erstellen/bearbeiten
│   ├── events.php          # Terminverwaltung
│   ├── pages.php           # Seiteninhalte-Übersicht
│   ├── edit-content.php    # Seiteninhalte bearbeiten
│   ├── custom-pages.php    # Eigene Seiten verwalten
│   ├── edit-custom-page.php # Eigene Seite erstellen/bearbeiten
│   ├── users.php           # Benutzerverwaltung (nur Admin)
│   ├── api.php             # JSON-API für Frontend
│   ├── config.php          # DB-Setup, Auth, CSRF, Sanitize-Funktionen
│   ├── schema.php          # Datenbank-Schema, Migrationen, Seed-Daten
│   ├── logout.php          # Abmeldung
│   ├── css/admin.css       # Admin-Panel-Styling
│   ├── partials/nav.php    # Admin-Navigation
│   └── data/               # SQLite-Datenbank (wird automatisch erstellt)
├── REVIEW.md              # Code-Review mit Sicherheitsaudit
├── uploads/                # Hochgeladene Bilder
└── images/
    ├── index/              # Vorstand-Fotos
    └── training/           # Trainingsplatz-Foto
```

## Browser-Kompatibilität

- Chrome / Edge 88+
- Firefox 78+
- Safari 14+
