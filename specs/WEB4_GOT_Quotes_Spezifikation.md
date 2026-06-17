# WEB4 PHP - Game of Thrones Zitate: Arbeits- und Aufgabenspezifikation

**Team:** 7 
**Semester:** SS 2026  
**Abgabetermin:** 23.06.2026, 23:59 Uhr (Moodle)  
**Abgabeformat:** `WEB4_PHP_TEAM7.zip`  
**Dokumentation:** Typst → `WEB4_PHP_TEAM7.pdf` (Unterverzeichnis `doc/`)

---

## 1. Ziel und Vision

Es wird eine **eigenständige PHP-Webanwendung** entwickelt, die berühmte **Game-of-Thrones-Zitate** als **Foren-Posts** präsentiert. Jedes Zitat ist ein eigenständiger Beitrag; darunter führen Nutzer **threadartige Diskussionen** in Baumstruktur (Antworten auf Antworten). Das visuelle Konzept orientiert sich an einem modernen **Philosophie-/Zitate-Forum**: ruhige Typografie, Zitat als zentrales Element, Avatare und Bilder als visuelle Anker.

Die Anwendung erfüllt die Anforderungen der [Projektangabe](../WEB4_Projekt_Angabe.md): MVC-Architektur, PDO-Datenbankzugriff, rollenbasierte Interaktion, vollständiges CRUD für mindestens eine Ressource, serverseitige Validierung sowie XSS-/SQL-Injection-Schutz.

**Leitbild:** Intuitive Bedienung ohne Erklärungstexte; klare Navigation; sofortiges Feedback bei Aktionen und Fehlern; **REST-konforme HTTP-Schnittstelle** mit semantisch korrekten Methoden.

**Design-Leitbild:** Modernes, responsives UI mit **Tailwind CSS 4 (CDN)** — dunkle/warme Farbpalette, großzügige Whitespace, Karten-Layout für Zitate, eingerückte Kommentar-Threads wie in Foren.

---

## 2. Abgrenzung und Annahmen

| Thema | Entscheidung |
|---|---|
| Zitate (Quotes) | Foren-**Posts** in der DB; initial per SQL-Dump. Nur Admins: CRUD inkl. **Bild-Upload** (optional). |
| Kommentare (Comments) | **Haupt-CRUD-Ressource** für eingeloggte Nutzer; **Baumstruktur** via `parent_id` (Antworten auf Kommentare). |
| Benutzer (Users) | Registrierung; optional **Profilbild-Upload** oder Platzhalter-Avatar. Admins verwalten Benutzer. |
| HTTP / REST | Ressourcenorientierte URLs; **GET** lesen, **POST** anlegen, **PUT/PATCH** aktualisieren, **DELETE** löschen. Kein `POST …/delete`. |
| JavaScript | **Pflicht** für `DELETE`- und `PATCH`-Requests (Fetch API), da HTML-Formulare diese Methoden nicht nativ unterstützen. JS **nicht** für Validierung oder Sicherheitslogik. |
| Externe Abhängigkeiten | Kein Composer/npm/Build-Schritt. **Tailwind CSS 4 via CDN** im Layout; kein lokales Bootstrap mehr. |
| Authentifizierung | Session-basiert (PHP `$_SESSION`). |
| Medien-Uploads | Dateien unter `public/uploads/` (Unterordner `quotes/`, `avatars/`); nur erlaubte MIME-Typen, Größenlimit serverseitig. |

---

## 3. Benutzerrollen und Berechtigungen

### 3.1 Rollen

| Rolle | Beschreibung |
|---|---|
| **Gast (anonym)** | Nicht eingeloggt |
| **Benutzer (User)** | Eingeloggt, `is_admin = 0` |
| **Administrator (Admin)** | Eingeloggt, `is_admin = 1` |

### 3.2 Berechtigungsmatrix

| Aktion | Gast | User | Admin |
|---|:---:|:---:|:---:|
| Zitat-Übersicht (Foren-Feed) anzeigen | ✓ | ✓ | ✓ |
| Zitat-Detail inkl. Kommentar-Thread anzeigen | ✓ | ✓ | ✓ |
| Registrierung (optional mit Avatar) | ✓ | - | - |
| Login / Logout | ✓ / - | ✓ | ✓ |
| Eigenes Profilbild hochladen/entfernen | - | ✓ | ✓ |
| Top-Level-Kommentar schreiben | - | ✓ | ✓ |
| Auf Kommentar antworten (Thread) | - | ✓ | ✓ |
| Eigenen Kommentar bearbeiten | - | ✓ | ✓ |
| Eigenen Kommentar löschen | - | ✓ | ✓ |
| Fremden Kommentar löschen | - | - | ✓ |
| Fremden Kommentar bearbeiten | - | - | **✗** |
| Zitat anlegen / bearbeiten / löschen (inkl. Bild) | - | - | ✓ |
| Benutzerverwaltung (Liste, Löschen, Rolle ändern) | - | - | ✓ |
| Sich selbst löschen | - | ✓* | ✓* |
| Eigene Admin-Rolle entziehen (Selbst-Demotion) | - | - | **✗** (Schutz) |

\* Selbstlöschung optional; falls implementiert, darf der **letzte Admin** nicht gelöscht werden.

---

## 4. Funktionale Anforderungen

### 4.1 Authentifizierung und Registrierung

#### Registrierung (`GET /register`, `POST /register`)
- Felder: **Benutzername**, **Passwort**, optional **Avatar** (JPEG/PNG/WebP).
- Nach Erfolg: Redirect zum Login mit Erfolgsmeldung (kein Auto-Login).
- Fehler serverseitig im Formular.

#### Login (`GET /login`, `POST /login`)
- Session: `user_id`, `username`, `is_admin`, optional `avatar_path`.
- Generische Fehlermeldung bei Fehlschlag.

#### Logout (`POST /logout`)
- Session invalidieren; Redirect zur Startseite.

#### Profilbild (`GET /profile`, `POST /profile/avatar`, `DELETE /profile/avatar`)
- Eingeloggte Nutzer können Avatar hochladen oder löschen (leerer Platzhalter mit Initialen).
- Validierung: max. 2 MB, nur `image/jpeg`, `image/png`, `image/webp`.

### 4.2 Zitate (Foren-Posts)

#### Übersicht (`GET /` oder `GET /quotes`)
- **Foren-Feed:** Karten pro Zitat — Zitat-Ausschnitt, Sprecher, optional Thumbnail, Kommentar-Anzahl, Datum.
- Paginierung ab > 20 Einträgen empfohlen.
- Link zur Detail-/Thread-Seite.

#### Detail (`GET /quotes/{id}`)
- Vollständiger Zitat-Text (typografisch hervorgehoben), Sprecher, Staffel/Episode, optionales **Hero-Bild**.
- Darunter: **Kommentar-Baum** (Top-Level + eingerückte Antworten).
- Eingeloggt: Formular „Kommentar schreiben“; pro Kommentar „Antworten“-Button.
- Bearbeiten/Löschen nur bei berechtigten Kommentaren (Löschen via Fetch `DELETE`).

#### Admin: Zitat-Verwaltung
- **Liste** `GET /admin/quotes`
- **Anlegen** `GET /admin/quotes/new`, `POST /admin/quotes` — Text, Sprecher, Staffel/Episode, **optionales Bild**
- **Bearbeiten** `GET /admin/quotes/{id}/edit`, `PUT /admin/quotes/{id}` — inkl. Bild ersetzen/entfernen
- **Löschen** `DELETE /admin/quotes/{id}` — per Fetch + CSRF; Kommentare per CASCADE

### 4.3 Kommentare (CRUD-Ressource, Thread-Modell)

| Operation | Route | Methode | Berechtigung |
|---|---|---|---|
| **Create (Top-Level)** | `/quotes/{quoteId}/comments` | POST | User, Admin |
| **Create (Antwort)** | `/comments/{parentId}/replies` | POST | User, Admin |
| **Read** | implizit auf Zitat-Detail | GET | alle |
| **Update** | `/comments/{id}` | PUT oder PATCH | Autor |
| **Delete** | `/comments/{id}` | DELETE | Autor oder Admin |

**Thread-Regeln:**
- `parent_id = NULL` → Top-Level-Kommentar zum Zitat.
- `parent_id = {comment.id}` → Antwort; beliebige Tiefe erlaubt (UI empfiehlt max. 5 Ebenen Einrückung).
- Beim Löschen eines Kommentars mit Kindern: **CASCADE** auf `parent_id` (Antworten mitlöschen) oder Soft-Delete — Entscheidung: **ON DELETE CASCADE** auf `comments.parent_id`.
- `user_id` stets aus Session; `quote_id` aus Route bzw. vom Parent-Kommentar vererben.

**Regeln:**
- Inhalt Pflicht, Länge begrenzt (Abschnitt 7).
- Bearbeitung setzt `updated_at`; Anzeige „bearbeitet“ optional.
- Admin bei fremden Kommentaren nur **Löschen** (Fetch `DELETE`), kein Bearbeiten.

### 4.4 Benutzerverwaltung (Admin)

Route-Bereich: `/admin/users`

| Aktion | Route | Methode |
|---|---|---|
| Liste | `/admin/users` | GET |
| Rolle ändern | `/admin/users/{id}/admin` | PATCH (Toggle) |
| Benutzer löschen | `/admin/users/{id}` | DELETE |

**Schutzregeln:** unverändert (letzter Admin, keine Selbst-Demotion, keine Klartext-Passwörter).

---

## 5. REST-Konformität

### 5.1 Prinzipien

| Prinzip | Umsetzung |
|---|---|
| Ressourcen-URLs | Substantive, plural: `/quotes`, `/comments`, `/users` |
| HTTP-Methoden semantisch | GET = lesen, POST = erstellen, PUT/PATCH = aktualisieren, DELETE = löschen |
| Statuscodes | 200 OK, 201 Created, 204 No Content (DELETE), 302/303 Redirect (HTML-Flow), 403 Forbidden, 404 Not Found, 422 Unprocessable Entity (Validierung) |
| Keine Aktion in URL | ~~`/comments/{id}/delete`~~ → `DELETE /comments/{id}` |
| Content-Negotiation (optional) | `Accept: application/json` → JSON-Antwort; Standard: HTML |

### 5.2 Routing (vollständig)

| Methode | Pfad | Controller::action | Anmerkung |
|---|---|---|---|
| GET | `/` | `QuoteController::index` | Foren-Feed |
| GET | `/quotes` | `QuoteController::index` | Alias |
| GET | `/quotes/{id}` | `QuoteController::show` | Post + Thread |
| GET | `/register` | `AuthController::showRegister` | |
| POST | `/register` | `AuthController::register` | multipart wenn Avatar |
| GET | `/login` | `AuthController::showLogin` | |
| POST | `/login` | `AuthController::login` | |
| POST | `/logout` | `AuthController::logout` | |
| GET | `/profile` | `ProfileController::show` | Avatar-Verwaltung |
| POST | `/profile/avatar` | `ProfileController::uploadAvatar` | |
| DELETE | `/profile/avatar` | `ProfileController::deleteAvatar` | Fetch |
| POST | `/quotes/{quoteId}/comments` | `CommentController::store` | Top-Level |
| POST | `/comments/{parentId}/replies` | `CommentController::reply` | Thread-Antwort |
| GET | `/comments/{id}/edit` | `CommentController::edit` | HTML-Formular |
| PUT | `/comments/{id}` | `CommentController::update` | Fetch oder Form `_method` |
| DELETE | `/comments/{id}` | `CommentController::destroy` | **Fetch + CSRF** |
| GET | `/admin/users` | `Admin\UserController::index` | |
| PATCH | `/admin/users/{id}/admin` | `Admin\UserController::toggleAdmin` | Fetch |
| DELETE | `/admin/users/{id}` | `Admin\UserController::destroy` | Fetch |
| GET | `/admin/quotes` | `Admin\QuoteController::index` | |
| GET | `/admin/quotes/new` | `Admin\QuoteController::create` | Formular |
| POST | `/admin/quotes` | `Admin\QuoteController::store` | multipart |
| GET | `/admin/quotes/{id}/edit` | `Admin\QuoteController::edit` | |
| PUT | `/admin/quotes/{id}` | `Admin\QuoteController::update` | multipart |
| DELETE | `/admin/quotes/{id}` | `Admin\QuoteController::destroy` | Fetch |

### 5.3 Router-Erweiterung

`Router` unterstützt zusätzlich: `put()`, `patch()`, `delete()`.  
Für Browser-Formulare ohne Fetch: verstecktes Feld `_method` als Fallback (nur wo explizit dokumentiert); **Lösch-Aktionen ausschließlich per Fetch**.

### 5.4 Client-seitiges JavaScript (`public/assets/js/app.js`)

```javascript
// Pseudocode — DELETE mit CSRF
async function deleteResource(url, csrfToken) {
  const res = await fetch(url, {
    method: 'DELETE',
    headers: { 'X-CSRF-Token': csrfToken, 'Accept': 'text/html' }
  });
  if (res.redirected) window.location = res.url;
  else if (res.ok) window.location.reload();
}
```

- Bestätigungsdialog vor DELETE (UX).
- Keine Validierung — serverseitige Prüfung bleibt maßgeblich.
- CSRF-Token aus `<meta name="csrf-token">` oder `data-csrf` am Button.

---

## 6. Nicht-funktionale Anforderungen

| Anforderung | Umsetzung |
|---|---|
| PHP ≥ 8.x | Strict types wo sinnvoll |
| MVC-Trennung | Siehe Abschnitt 7 |
| PDO | Prepared Statements für **alle** DB-Zugriffe |
| XSS-Schutz | `htmlspecialchars()` bei HTML-Ausgabe |
| Upload-Sicherheit | MIME via `finfo`, Größenlimit, zufällige Dateinamen, kein PHP in Upload-Ordner (`.htaccess` deny execution) |
| Valides HTML5 | W3C-fähig |
| CSS | **Tailwind CSS 4 CDN**; kein Inline-Styling als Hauptmechanismus |
| Serverseitige Validierung | Jedes Formular und jeder Upload |
| Self-contained | XAMPP-ready ohne Build |

---

## 7. Architektur (MVC)

### 7.1 Verzeichnisstruktur (`public/`)

```
public/
├── index.php
├── .htaccess
├── assets/
│   └── js/
│       └── app.js              # Fetch DELETE/PATCH, Thread-UI-Helfer
├── uploads/
│   ├── quotes/                 # Admin-Zitatbilder
│   ├── avatars/                # Nutzer-Avatare
│   └── .htaccess               # Options -ExecCGI, nur statische Auslieferung
├── src/
│   ├── bootstrap.php
│   ├── config.php
│   ├── Router.php              # GET, POST, PUT, PATCH, DELETE
│   ├── Controller/
│   │   ├── AuthController.php
│   │   ├── ProfileController.php
│   │   ├── QuoteController.php
│   │   ├── CommentController.php
│   │   └── Admin/
│   │       ├── UserController.php
│   │       └── QuoteController.php
│   ├── Model/
│   │   ├── User.php
│   │   ├── Quote.php
│   │   └── Comment.php
│   ├── Service/
│   │   ├── AuthService.php
│   │   ├── ValidationService.php
│   │   └── UploadService.php   # Bild-Validierung & Speicherung
│   └── ...
└── views/
    ├── layouts/
    │   └── main.php            # Tailwind CDN, CSRF meta, app.js
    ├── partials/
    │   ├── comment-tree.php    # Rekursive Thread-Darstellung
    │   ├── avatar.php
    │   └── delete-button.php   # data-url + Fetch
    ├── auth/
    ├── quotes/
    ├── profile/
    ├── comments/
    └── admin/
```

### 7.2 Schichtenverantwortung

Unverändert: Controller → Model (PDO) → View (escaped HTML).

---

## 8. Datenmodell

### 8.1 ER-Übersicht (logisch)

```
┌─────────────┐       ┌─────────────────────┐       ┌─────────────┐
│    users    │       │      comments       │       │   quotes    │
├─────────────┤       ├─────────────────────┤       ├─────────────┤
│ id (PK)     │──┐    │ id (PK)             │    ┌──│ id (PK)     │
│ username    │  │    │ quote_id (FK)       │───>│  │ text        │
│ password    │  └───<│ user_id (FK)        │    │  │ speaker     │
│ is_admin    │       │ parent_id (FK,self) │    │  │ season      │
│ avatar_path │       │ content             │    │  │ episode     │
│ created_at  │       │ created_at          │    │  │ image_path  │
└─────────────┘       │ updated_at          │    │  │ created_at  │
                      └─────────────────────┘    └──└─────────────┘
```

Beziehungen:
- `comments.quote_id` → `quotes.id` (**ON DELETE CASCADE**)
- `comments.user_id` → `users.id` (**ON DELETE SET NULL**)
- `comments.parent_id` → `comments.id` (**ON DELETE CASCADE**, nullable)

### 8.2 Tabellendefinitionen

#### `users` (Erweiterung)

| Spalte | Typ | Constraints |
|---|---|---|
| `id` | `INT UNSIGNED` | PK, AUTO_INCREMENT |
| `username` | `VARCHAR(50)` | NOT NULL, UNIQUE |
| `password_hash` | `VARCHAR(255)` | NOT NULL |
| `is_admin` | `TINYINT(1)` | NOT NULL, DEFAULT 0 |
| `avatar_path` | `VARCHAR(255)` | NULL — relativer Pfad unter `/uploads/avatars/` |
| `created_at` | `DATETIME` | NOT NULL, DEFAULT CURRENT_TIMESTAMP |

#### `quotes` (Erweiterung)

| Spalte | Typ | Constraints |
|---|---|---|
| `id` | `INT UNSIGNED` | PK, AUTO_INCREMENT |
| `text` | `TEXT` | NOT NULL |
| `speaker` | `VARCHAR(100)` | NOT NULL |
| `season` | `TINYINT UNSIGNED` | NULL |
| `episode` | `TINYINT UNSIGNED` | NULL |
| `image_path` | `VARCHAR(255)` | NULL — relativer Pfad unter `/uploads/quotes/` |
| `created_at` | `DATETIME` | NOT NULL, DEFAULT CURRENT_TIMESTAMP |

#### `comments` (Erweiterung)

| Spalte | Typ | Constraints |
|---|---|---|
| `id` | `INT UNSIGNED` | PK, AUTO_INCREMENT |
| `quote_id` | `INT UNSIGNED` | NOT NULL, FK → `quotes.id` |
| `user_id` | `INT UNSIGNED` | NULL, FK → `users.id`, ON DELETE SET NULL |
| `parent_id` | `INT UNSIGNED` | NULL, FK → `comments.id`, ON DELETE CASCADE |
| `content` | `TEXT` | NOT NULL |
| `created_at` | `DATETIME` | NOT NULL, DEFAULT CURRENT_TIMESTAMP |
| `updated_at` | `DATETIME` | NULL ON UPDATE CURRENT_TIMESTAMP |

**Indizes:** `comments.quote_id`, `comments.user_id`, `comments.parent_id`

### 8.3 Testdaten

- ≥ 1 Admin, ≥ 2 User (mind. einer mit Avatar)
- ≥ 10 Zitate (mind. 3 mit Bild)
- ≥ 15 Kommentare inkl. **verschachtelter Antworten** (mind. 5 Replies)

---

## 9. Validierung und Fehlerbehandlung

### 9.1 Eingaberegeln

| Feld | Regeln |
|---|---|
| `username` | 3–50 Zeichen; `[a-zA-Z0-9_]`; unique |
| `password` | min. 8 Zeichen |
| `quote.text` | 1–2000 Zeichen |
| `quote.speaker` | 1–100 Zeichen |
| `quote.image` / `avatar` | optional; max. 2 MB; JPEG/PNG/WebP; `finfo` MIME-Check |
| `comment.content` | 1–1000 Zeichen; nicht leer nach `trim()` |

### 9.2 Fehlerdarstellung

- Validierungsfehler am Formular / Flash-Message
- HTTP 422 bei API-JSON; HTML-Flow mit Redirect + Flash
- Keine Stack Traces in Abgabe

### 9.3 Sicherheitscheckliste

- [ ] Prepared Statements überall
- [ ] htmlspecialchars bei User-Output
- [ ] Passwörter gehasht
- [ ] session_regenerate_id() nach Login
- [ ] CSRF für POST, PUT, PATCH, DELETE
- [ ] IDOR-Schutz auf Kommentar-Bearbeitung
- [ ] Upload: MIME, Größe, sichere Dateinamen, Execution blockiert in uploads/

---

## 10. Frontend und UX (Forum / Philosophie-Thema)

### 10.1 Tailwind CSS 4

- Einbindung im Layout via CDN (Play CDN oder offizielles Tailwind v4 CDN — ohne npm).
- Entfernung von `assets/css/app.css` (Bootstrap/Custom) zugunsten Utility-Klassen.
- Optional: wenige `@layer`-Erweiterungen inline im Layout für Zitat-Typografie (Serif für Zitate).

### 10.2 Visuelles Konzept

| Element | Stil |
|---|---|
| Farben | Warmes Dunkel (Stone/Slate), Akzent Gold/Amber (Thron-/Pergament-Anmutung) |
| Zitat-Post | Große Serif-Zitate, dezente Anführungszeichen, Sprecher als Signatur |
| Foren-Feed | Karten-Grid, Hover-Lift, Kommentar-Badge |
| Thread | Linke Einrückung + vertikale Connector-Linien; Avatar links |
| Avatar | Rund; Bild oder Initialen-Kreis auf Gradient |
| Buttons | Primary Amber, Ghost für Antworten, Destructive für Löschen (Fetch) |

### 10.3 Globale Navigation

| Zustand | Navigation |
|---|---|
| Gast | Feed, Login, Registrierung |
| User | Feed, Profil, Logout |
| Admin | Feed, Profil, Admin → Benutzer, Admin → Zitate, Logout |

### 10.4 Zitat-Detail (Wireframe — Forum-Thread)

```
┌──────────────────────────────────────────────────────────┐
│  GoT Quotes · Forum          [Feed] [Profil] [Logout]    │
├──────────────────────────────────────────────────────────┤
│  ┌────────────────────────────────────────────────────┐  │
│  │  [Hero-Bild optional, volle Breite, abgerundet]    │  │
│  │  „When you play the game of thrones …"             │  │
│  │  — Cersei Lannister · S01E07                     │  │
│  └────────────────────────────────────────────────────┘  │
│  Diskussion (12)                                         │
│  ┌─ [avatar] tyrion_fan · 01.03.2026 ─────────────────┐  │
│  │  One of the most iconic lines…    [Antworten][✎][🗑]│  │
│  │    ┌─ [avatar] arya_fan · … ──────────────────────┐ │  │
│  │    │  Absolutely! Cersei was ruthless.  [✎][🗑]   │ │  │
│  │    └──────────────────────────────────────────────┘ │  │
│  └────────────────────────────────────────────────────┘  │
│  [ Neuer Kommentar — nur eingeloggt ]                    │
└──────────────────────────────────────────────────────────┘
```

---

## 11. Dokumentation (Typst)

Quelle: [`doc/main.typ`](../doc/main.typ)

**Anpassungen nach Refactoring:**
- ER-Diagramm: `parent_id`, `avatar_path`, `image_path`
- Architektur: REST-Routen, Router-Methoden, Fetch/DELETE
- Frontend: Tailwind CDN statt Bootstrap
- Testfälle: Thread-Antworten, Bild-Upload, REST-DELETE, Profilbild

---

## 12. Testfälle (Erweiterter Katalog)

### 12.1 Bestehende Tests

T-AUTH-01 … T-SEC-03 bleiben gültig (Routen ggf. anpassen).

### 12.2 Neue / geänderte Tests

| ID | Szenario |
|---|---|
| T-REST-01 | `DELETE /comments/{id}` ohne POST-Form — nur per Fetch; 405/404 bei GET |
| T-REST-02 | Admin `DELETE /admin/quotes/{id}` per Fetch → 204/Redirect |
| T-THREAD-01 | User antwortet auf Kommentar → erscheint eingerückt unter Parent |
| T-THREAD-02 | Löschen Parent mit Replies → CASCADE (Kinder weg) |
| T-IMG-01 | Admin lädt Zitatbild hoch → auf Detail sichtbar |
| T-IMG-02 | Ungültiger Upload (PDF) → Validierungsfehler |
| T-IMG-03 | User lädt Avatar hoch → in Kommentaren sichtbar |
| T-IMG-04 | User ohne Avatar → Initialen-Platzhalter |
| T-UI-01 | Responsives Layout Tailwind — Mobile Feed lesbar |

---

## 13. Entwicklungsumgebung

Unverändert: Document Root `public/`, SQL-Dump `WEB4_PHP_TEAM4.sql`, Doku `doc/`.

---

## 14. Abgabe-Checkliste

Zusätzlich zu bisherigen Punkten:

- [ ] `uploads/` mit `.htaccess` (keine Script-Ausführung)
- [ ] `assets/js/app.js` für DELETE-Fetch
- [ ] Kein veraltetes Bootstrap/CSS
- [ ] SQL-Dump enthält neue Spalten und Thread-Testdaten
- [ ] Typst-Doku aktualisiert (REST, Threads, Uploads, Tailwind)

---

## 15. Implementierungsreihenfolge (Refactoring)

| Phase | Aufgabe |
|---|---|
| **R1** | Spezifikation & SQL-Schema (`parent_id`, Bild-Spalten) |
| **R2** | Router: PUT/PATCH/DELETE; CSRF für non-GET |
| **R3** | UploadService; uploads-Verzeichnis |
| **R4** | Comment-Model: Baum laden/speichern; CASCADE |
| **R5** | REST-Controller anpassen; Fetch-Löschen |
| **R6** | Tailwind-Layout + Forum-Views |
| **R7** | Profil/Avatar; Admin-Zitatbilder |
| **R8** | `app.js`; Delete-Buttons überall |
| **R9** | Typst-Doku + Testfälle + SQL-Dump final |

---

## 16. Offene Entscheidungen (geklärt)

| # | Frage | Entscheidung |
|---|---|---|
| 1 | Auto-Login nach Registrierung? | **Nein** |
| 2 | CSRF | **Ja**, inkl. Fetch-Header `X-CSRF-Token` |
| 3 | Kommentar-Tiefe | Unbegrenzt in DB; UI max. ~5 Einrückungsstufen |
| 4 | Parent-Löschung | **CASCADE** auf `parent_id` |
| 5 | CSS-Framework | **Tailwind CSS 4 CDN** |
| 6 | DELETE-Transport | **Fetch API**, kein `<form method="post">` für Delete |
| 7 | Teammitglieder | Nathalie Sonnleitner, Tim Peko |

---

## 17. Referenzen

- [WEB4_Projekt_Angabe.md](../WEB4_Projekt_Angabe.md)
- [doc/main.typ](../doc/main.typ)
- [Tailwind CSS v4 — CDN](https://tailwindcss.com/docs/installation/play-cdn)

---

*Stand: 17.06.2026 — REST, Forum-Threads, Bild-Uploads, Tailwind CSS 4.*
