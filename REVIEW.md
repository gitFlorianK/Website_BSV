# Code-Review – BSV 1960 Plauen Website

Datum: 2026-03-10

## Kritisch

| # | Problem | Datei | Status |
|---|---------|-------|--------|
| 1 | **XSS in post.content** – Blog-Inhalt wird unescaped via `innerHTML` eingefügt | `js/aktuelles.js:94,122` | behoben |
| 2 | **Session Fixation** – Nach Login fehlt `session_regenerate_id(true)` | `admin/index.php:16-19` | behoben |
| 3 | **Standard-Passwort** `admin2024` im Code und Git-History | `admin/schema.php:79` | behoben (Warnung im Dashboard) |

## Hoch

| # | Problem | Datei | Status |
|---|---------|-------|--------|
| 4 | **Kein Rate-Limiting** beim Login – Brute-Force möglich | `admin/index.php` | behoben (10 Versuche / 15 Min) |
| 5 | **CORS `*`** – API erlaubt alle Origins | `admin/api.php:5` | behoben (same-origin only) |
| 6 | **Custom Page Content unescaped** – `<?= $pageData['content'] ?>` direkt ausgegeben | `page.php:42` | behoben (sanitizeContentHtml) |
| 7 | **Unerreichbarer Code** – Post-Reload nach `header()+exit` wird nie ausgeführt | `admin/edit-post.php:99-107` | behoben (entfernt) |
| 8 | **Keine Security-Header** – X-Frame-Options, CSP, X-Content-Type-Options fehlen | alle Seiten | behoben |

## Mittel

| # | Problem | Datei | Status |
|---|---------|-------|--------|
| 9 | **Keine Pagination** bei Posts-API – Speicherprobleme bei vielen Beiträgen | `admin/api.php:22-40` | behoben (LIMIT/OFFSET) |
| 10 | **Fehlende Timezone** – `date('Y')` ohne `date_default_timezone_set()` | `includes/footer.php:35` | behoben (Europe/Berlin) |
| 11 | **Inkonsistente API-Fehlerbehandlung** – manche Endpoints geben Fehler, andere nicht | `admin/api.php` | behoben (alle Endpoints mit HTTP-Status + JSON-Error) |
| 12 | **Tight Coupling** – Frontend `page.php` braucht `admin/config.php` | `page.php:2` | behoben (includes/bootstrap.php) |
| 13 | **Kein Error-Logging** – Fehler werden überall stillschweigend verschluckt | JS `.catch(() => {})` | behoben (console.warn) |
| 14 | **Slug-Generierung** – Titel nur aus Sonderzeichen ergibt leeren Slug | `admin/edit-custom-page.php:36-45` | behoben (Fallback: seite-{timestamp}) |

## Niedrig (offen)

| # | Problem | Datei | Status |
|---|---------|-------|--------|
| 15 | **Unbenutzte Variable** `$postImages` und `$success` | `admin/edit-post.php:10` | $success entfernt |
| 16 | **Globaler JS-Namespace** – `esc()`, `renderTitle()` etc. sind global | `js/main.js`, `js/content.js` | offen |
| 17 | **Uploads-Pfad hardcoded** in JS (`uploads/`) | `js/aktuelles.js:100,128` | offen |
