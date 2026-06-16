# WEB4 PHP — Game of Thrones Zitate: Arbeits- und Aufgabenspezifikation

**Team:** 4  
**Semester:** SS 2026  
**Abgabetermin:** 23.06.2026, 23:59 Uhr (Moodle)  
**Abgabeformat:** `WEB4_PHP_TEAM4.zip`  
**Dokumentation:** Typst → `WEB4_PHP_TEAM4.pdf` (Unterverzeichnis `doc/`)

---

## 1. Ziel und Vision

Es wird eine **self-contained PHP-Webanwendung** entwickelt, die berühmte **Game-of-Thrones-Zitate** präsentiert. Zu jedem Zitat können Besucher zugehörige **Kommentare** einsehen. Eingeloggte Nutzer können Kommentare verfassen, eigene Kommentare bearbeiten und löschen. Administratoren verwalten Benutzer und können fremde Kommentare löschen — aber nicht bearbeiten.

Die Anwendung erfüllt die Anforderungen der [Projektangabe](../WEB4_Projekt_Angabe.md): MVC-Architektur, PDO-Datenbankzugriff, rollenbasierte Interaktion, vollständiges CRUD für mindestens eine Ressource, serverseitige Validierung sowie XSS-/SQL-Injection-Schutz.

**Leitbild:** Intuitive Bedienung ohne Erklärungstexte; klare Navigation; sofortiges Feedback bei Aktionen und Fehlern.

---

## 2. Abgrenzung und Annahmen

| Thema | Entscheidung |
|---|---|
| Zitate (Quotes) | Werden in der Datenbank gespeichert und initial per SQL-Dump bereitgestellt. Nur Admins dürfen Zitate anlegen, bearbeiten und löschen. |
| Kommentare (Comments) | **Haupt-CRUD-Ressource** für eingeloggte Nutzer (Create, Read, Update, Delete eigener Kommentare). |
| Benutzer (Users) | Registrierung für anonyme Besucher; Admins verwalten Benutzer (Löschen, Admin-Rolle). |
| Bilder | Optional pro Zitat; Dateien liegen statisch unter `public/assets/images/quotes/`. In der DB wird nur der relative Pfad gespeichert. |
| JavaScript | Erlaubt für UX (z. B. Bestätigungsdialoge), **nicht** für Validierung oder Sicherheitslogik. |
| Externe Abhängigkeiten | Kein Composer/npm nötig; Copy auf Standard-XAMPP reicht. Bootstrap o. Ä. als eingebettete statische Dateien ist erlaubt. |
| Authentifizierung | Session-basiert (PHP `$_SESSION`). |

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
| Zitat-Übersicht anzeigen | ✓ | ✓ | ✓ |
| Zitat-Detail inkl. Kommentare anzeigen | ✓ | ✓ | ✓ |
| Registrierung | ✓ | — | — |
| Login / Logout | ✓ / — | ✓ | ✓ |
| Kommentar schreiben | — | ✓ | ✓ |
| Eigenen Kommentar bearbeiten | — | ✓ | ✓ |
| Eigenen Kommentar löschen | — | ✓ | ✓ |
| Fremden Kommentar löschen | — | — | ✓ |
| Fremden Kommentar bearbeiten | — | — | **✗** |
| Zitat anlegen / bearbeiten / löschen | — | — | ✓ |
| Benutzerverwaltung (Liste, Löschen, Rolle ändern) | — | — | ✓ |
| Sich selbst löschen | — | ✓* | ✓* |
| Eigene Admin-Rolle entziehen (Selbst-Demotion) | — | — | **✗** (Schutz) |

\* Selbstlöschung ist optional; falls implementiert, darf der **letzte Admin** nicht gelöscht werden.

---

## 4. Funktionale Anforderungen

### 4.1 Authentifizierung und Registrierung

#### Registrierung (`GET/POST /register`)
- Anonyme Besucher können sich mit **Benutzername** und **Passwort** registrieren.
- Nach erfolgreicher Registrierung: Weiterleitung zum Login oder automatisches Einloggen (Entscheidung dokumentieren; empfohlen: Weiterleitung zum Login mit Erfolgsmeldung).
- Fehlerfälle serverseitig behandeln und im Formular anzeigen (z. B. doppelter Benutzername, ungültige Eingaben).

#### Login (`GET/POST /login`)
- Authentifizierung per Benutzername + Passwort.
- Passwort nur als Hash in der DB (`password_hash` / `password_verify`, Algorithmus `PASSWORD_DEFAULT`).
- Bei Erfolg: Session setzen (`user_id`, `username`, `is_admin`).
- Bei Fehler: generische Fehlermeldung („Benutzername oder Passwort ungültig"), ohne preiszugeben, welches Feld falsch war.

#### Logout (`POST /logout`)
- Session invalidieren; Weiterleitung zur Startseite.

### 4.2 Zitate

#### Übersicht (`GET /` oder `GET /quotes`)
- Liste aller Zitate (Paginierung optional, empfohlen ab > 20 Einträgen).
- Anzeige: Zitat-Text (gekürzt), Sprecher, optional Thumbnail.
- Link zur Detailseite.

#### Detail (`GET /quotes/{id}`)
- Vollständiger Zitat-Text, Sprecher, optional Bild, Metadaten (z. B. Staffel/Episode falls vorhanden).
- Darunter: chronologische Kommentarliste (Autor, Zeitstempel, Inhalt).
- Für eingeloggte Nutzer: Formular „Neuer Kommentar".
- Bearbeiten/Löschen-Buttons nur bei berechtigten Kommentaren sichtbar.

#### Admin: Zitat-Verwaltung (`GET/POST /admin/quotes`, CRUD)
- **Create:** Formular für Text, Sprecher, optionales Bild (Upload oder Pfad-Auswahl aus vorhandenen Assets).
- **Read:** Admin-Liste aller Zitate.
- **Update:** Bearbeitungsformular.
- **Delete:** Löschen mit Bestätigung; zugehörige Kommentare per `ON DELETE CASCADE` oder explizit mitlöschen.

### 4.3 Kommentare (CRUD-Ressource)

| Operation | Route (Vorschlag) | Berechtigung |
|---|---|---|
| **Create** | `POST /quotes/{id}/comments` | User, Admin |
| **Read** | implizit auf Zitat-Detail | alle |
| **Update** | `GET/POST /comments/{id}/edit` | Autor des Kommentars |
| **Delete** | `POST /comments/{id}/delete` | Autor oder Admin |

**Regeln:**
- Kommentar-Inhalt ist Pflichtfeld, Länge begrenzt (siehe Abschnitt 7).
- Beim Speichern: `user_id` aus Session, niemals aus Client-Input.
- Bearbeitung setzt `updated_at`; Anzeige „bearbeitet am …" optional.
- Admin sieht bei fremden Kommentaren nur **Löschen**, kein **Bearbeiten**.

### 4.4 Benutzerverwaltung (Admin)

Route-Bereich: `/admin/users`

| Aktion | Beschreibung |
|---|---|
| Liste | Alle Benutzer: ID, Benutzername, Rolle, Registrierungsdatum |
| Rolle ändern | Toggle Admin ja/nein per Formular (`POST /admin/users/{id}/toggle-admin`) |
| Benutzer löschen | `POST /admin/users/{id}/delete` mit Bestätigung |

**Schutzregeln:**
- Admin kann sich nicht selbst die Admin-Rolle entziehen.
- Letzter verbleibender Admin darf nicht gelöscht oder degradiert werden.
- Passwörter werden in der Verwaltung **niemals** im Klartext angezeigt.

---

## 5. Nicht-funktionale Anforderungen

Aus der Projektangabe, konkretisiert für dieses Projekt:

| Anforderung | Umsetzung |
|---|---|
| PHP ≥ 8.x | Strict types wo sinnvoll; moderne Syntax |
| MVC-Trennung | Siehe Abschnitt 6 |
| PDO | Prepared Statements für **alle** DB-Zugriffe mit User-Input |
| SQL-Injection-Schutz | Keine String-Konkatenation in SQL |
| XSS-Schutz | `htmlspecialchars()` (UTF-8, `ENT_QUOTES`) bei jeder HTML-Ausgabe usergenerierter Inhalte |
| Valides HTML5 | W3C-Validator-fähig |
| CSS für Layout | Kein Inline-Styling als Hauptmechanismus |
| Serverseitige Validierung | Jedes Formular; JS nur ergänzend |
| Intuitive UI | Bootstrap o. Ä.; konsistente Navigation; Flash-Messages |
| Self-contained | Lauffähig auf Standard-XAMPP ohne Build-Schritt |

---

## 6. Architektur (MVC)

### 6.1 Verzeichnisstruktur (`public/`)

Apache Document Root = `public/`. Alle Anfragen laufen über `index.php` (Front Controller).

```
public/
├── index.php                 # Front Controller, Bootstrap
├── .htaccess                 # URL-Rewriting → index.php
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
│       └── quotes/           # GoT-Bilder (optional pro Zitat)
├── src/
│   ├── bootstrap.php         # Autoload, Config, Session-Start
│   ├── config.php            # DB-Credentials (aus Umgebung/XAMPP-Defaults)
│   ├── Router.php
│   ├── Controller/
│   │   ├── AuthController.php
│   │   ├── QuoteController.php
│   │   ├── CommentController.php
│   │   └── Admin/
│   │       ├── UserController.php
│   │       └── QuoteController.php
│   ├── Model/
│   │   ├── User.php
│   │   ├── Quote.php
│   │   └── Comment.php
│   ├── Service/              # optional: AuthService, ValidationService
│   └── Middleware/           # optional: RequireAuth, RequireAdmin
└── views/
    ├── layouts/
    │   └── main.php          # Header, Navigation, Flash, Footer
    ├── auth/
    │   ├── login.php
    │   └── register.php
    ├── quotes/
    │   ├── index.php
    │   └── show.php
    ├── comments/
    │   └── edit.php
    └── admin/
        ├── users/
        │   └── index.php
        └── quotes/
            ├── index.php
            ├── create.php
            └── edit.php
```

### 6.2 Schichtenverantwortung

| Schicht | Verantwortung |
|---|---|
| **Controller** | HTTP-Request entgegennehmen, Berechtigung prüfen, Model aufrufen, View rendern oder Redirect |
| **Model** | PDO-Zugriff, Domänenlogik, keine HTML-Ausgabe |
| **View** | HTML-Templates; nur escaped Variablen ausgeben |
| **Router** | URL → Controller::action mappen |

### 6.3 Routing (Beispiel)

| Methode | Pfad | Controller::action |
|---|---|---|
| GET | `/` | `QuoteController::index` |
| GET | `/quotes/{id}` | `QuoteController::show` |
| GET | `/register` | `AuthController::showRegister` |
| POST | `/register` | `AuthController::register` |
| GET | `/login` | `AuthController::showLogin` |
| POST | `/login` | `AuthController::login` |
| POST | `/logout` | `AuthController::logout` |
| POST | `/quotes/{id}/comments` | `CommentController::store` |
| GET | `/comments/{id}/edit` | `CommentController::edit` |
| POST | `/comments/{id}/edit` | `CommentController::update` |
| POST | `/comments/{id}/delete` | `CommentController::delete` |
| GET | `/admin/users` | `Admin\UserController::index` |
| POST | `/admin/users/{id}/toggle-admin` | `Admin\UserController::toggleAdmin` |
| POST | `/admin/users/{id}/delete` | `Admin\UserController::delete` |
| GET | `/admin/quotes` | `Admin\QuoteController::index` |
| GET/POST | `/admin/quotes/create` | `Admin\QuoteController::create` |
| GET/POST | `/admin/quotes/{id}/edit` | `Admin\QuoteController::update` |
| POST | `/admin/quotes/{id}/delete` | `Admin\QuoteController::delete` |

### 6.4 Konfiguration Datenbank

**Entwicklung (mise):** Werte aus [`mise.toml`](../mise.toml):

| Variable | Wert |
|---|---|
| `MYSQL_TCP_PORT` | `33060` |
| `MYSQL_USER` | `fh_webphp` |
| `MYSQL_PASSWORD` | `fh_webphp` |
| `MYSQL_DATABASE` | `team_4` |

**Abgabe / XAMPP:** Standard laut Projektangabe — Port `3306`, gleiche Credentials, DB-Name `team_4`.

`config.php` soll beide Umgebungen unterstützen (z. B. per erkennbarem Dev-Port oder Konstante am Dateianfang).

---

## 7. Datenmodell

### 7.1 ER-Übersicht (logisch)

```
┌─────────────┐       ┌─────────────┐       ┌─────────────┐
│    users    │       │  comments   │       │   quotes    │
├─────────────┤       ├─────────────┤       ├─────────────┤
│ id (PK)     │──┐    │ id (PK)     │    ┌──│ id (PK)     │
│ username    │  └───<│ user_id(FK) │    │  │ text        │
│ password    │       │ quote_id(FK)│>───┘  │ speaker     │
│ is_admin    │       │ content     │       │ image_path  │
│ created_at  │       │ created_at  │       │ season      │
└─────────────┘       │ updated_at  │       │ episode     │
                      └─────────────┘       │ created_at  │
                                            └─────────────┘
```

Beziehungen:
- `comments.user_id` → `users.id` (**ON DELETE CASCADE** oder RESTRICT — dokumentieren; empfohlen: CASCADE bei User-Löschung)
- `comments.quote_id` → `quotes.id` (**ON DELETE CASCADE**)

### 7.2 Tabellendefinitionen

#### `users`

| Spalte | Typ | Constraints |
|---|---|---|
| `id` | `INT UNSIGNED` | PK, AUTO_INCREMENT |
| `username` | `VARCHAR(50)` | NOT NULL, UNIQUE |
| `password_hash` | `VARCHAR(255)` | NOT NULL |
| `is_admin` | `TINYINT(1)` | NOT NULL, DEFAULT 0 |
| `created_at` | `DATETIME` | NOT NULL, DEFAULT CURRENT_TIMESTAMP |

#### `quotes`

| Spalte | Typ | Constraints |
|---|---|---|
| `id` | `INT UNSIGNED` | PK, AUTO_INCREMENT |
| `text` | `TEXT` | NOT NULL |
| `speaker` | `VARCHAR(100)` | NOT NULL |
| `image_path` | `VARCHAR(255)` | NULL (relativ zu `public/`) |
| `season` | `TINYINT UNSIGNED` | NULL |
| `episode` | `TINYINT UNSIGNED` | NULL |
| `created_at` | `DATETIME` | NOT NULL, DEFAULT CURRENT_TIMESTAMP |

#### `comments`

| Spalte | Typ | Constraints |
|---|---|---|
| `id` | `INT UNSIGNED` | PK, AUTO_INCREMENT |
| `quote_id` | `INT UNSIGNED` | NOT NULL, FK → `quotes.id` |
| `user_id` | `INT UNSIGNED` | NOT NULL, FK → `users.id` |
| `content` | `TEXT` | NOT NULL |
| `created_at` | `DATETIME` | NOT NULL, DEFAULT CURRENT_TIMESTAMP |
| `updated_at` | `DATETIME` | NULL ON UPDATE CURRENT_TIMESTAMP |

**Indizes:** `comments.quote_id`, `comments.user_id`

### 7.3 Testdaten (Mindestumfang SQL-Dump)

- ≥ 1 Admin-Benutzer (z. B. `admin`, Passwort in Doku dokumentieren)
- ≥ 2 reguläre Testbenutzer
- ≥ 10 Zitate mit verschiedenen Sprechern
- ≥ 15 Kommentare, verteilt auf mehrere Zitate und Benutzer
- Mindestens 2 Zitate mit `image_path`

---

## 8. Validierung und Fehlerbehandlung

### 8.1 Eingaberegeln

| Feld | Regeln |
|---|---|
| `username` | 3–50 Zeichen; nur `[a-zA-Z0-9_]`; unique |
| `password` (Registrierung) | min. 8 Zeichen; serverseitig prüfen |
| `quote.text` | 1–2000 Zeichen; nicht leer |
| `quote.speaker` | 1–100 Zeichen |
| `quote.image_path` | optional; nur erlaubte Pfade/Extensions (`.jpg`, `.png`, `.webp`) |
| `comment.content` | 1–1000 Zeichen; nicht leer nach `trim()` |

### 8.2 Fehlerdarstellung

- Validierungsfehler am Formularfeld und/oder als Liste oben
- Erfolg: grüne Flash-Message (Session-basiert, einmalig)
- HTTP-Status: 404 bei unbekannter Ressource; 403 bei fehlender Berechtigung
- Keine Stack Traces in Produktion/Abgabe

### 8.3 Sicherheitscheckliste

- [ ] Alle SQL-Queries mit Prepared Statements
- [ ] Alle User-Outputs escaped (`htmlspecialchars`)
- [ ] Passwörter gehasht, nie geloggt
- [ ] Session-Fixation: `session_regenerate_id()` nach Login
- [ ] POST für mutierende Aktionen (Delete, Update, Logout)
- [ ] CSRF-Token optional aber empfohlen für alle POST-Formulare
- [ ] Admin-Routen durch Middleware/guard geschützt
- [ ] IDOR-Schutz: Kommentar-Bearbeitung nur wenn `comment.user_id === session.user_id`

---

## 9. Frontend und UX

### 9.1 Globale Navigation

| Zustand | Navigationselemente |
|---|---|
| Gast | Start, Login, Registrierung |
| User | Start, Logout, ggf. „Mein Profil" (optional) |
| Admin | Start, Logout, Admin → Benutzer, Admin → Zitate |

### 9.2 Seitenlayout

- Responsives Layout (Bootstrap 5 empfohlen, lokal unter `assets/`)
- Einheitlicher Header/Footer via `views/layouts/main.php`
- Flash-Messages prominent unterhalb der Navigation
- Formulare: Labels, Platzhalter, `required`-Attribut nur als UX-Hinweis (echte Prüfung serverseitig)
- Leere Zustände: „Noch keine Kommentare — sei der Erste!" (nur wenn eingeloggt)

### 9.3 Zitat-Detailseite (Wireframe)

```
┌────────────────────────────────────────────┐
│  [Logo/Titel]              Nav: … Login …  │
├────────────────────────────────────────────┤
│  [ optional: Bild ]                        │
│  „Zitat-Text …"                            │
│  — Sprecher (S01E03)                       │
├────────────────────────────────────────────┤
│  Kommentare (3)                            │
│  ┌──────────────────────────────────────┐  │
│  │ user_a · 12.06.2026 14:30            │  │
│  │ Kommentartext …          [✎] [🗑]    │  │
│  └──────────────────────────────────────┘  │
│  ┌──────────────────────────────────────┐  │
│  │ user_b · …              [🗑 Admin]   │  │
│  └──────────────────────────────────────┘  │
│  [ Neuer Kommentar (nur eingeloggt) ]      │
│  ┌──────────────────────────────────────┐  │
│  │ Textarea                             │  │
│  │                        [ Absenden ]  │  │
│  └──────────────────────────────────────┘  │
└────────────────────────────────────────────┘
```

---

## 10. Dokumentation (Typst)

Quelle: [`doc/main.typ`](../doc/main.typ)  
Build: `mise run build:documentation` → `WEB4_PHP_TEAM4.pdf`

### 10.1 Pflichtinhalte laut Projektangabe

1. **Projektmitglieder** (Namen, Matrikelnummern)
2. **Start-URL** der Anwendung (lokal und Abgabe-Kontext)
3. **ER-/UML-Diagramm** des Datenbankmodells (manuell erstellt, nicht auto-generiert)
   - Darstellung via **Graphviz** oder **Pintora** in Typst:
     ```typst
     ```graphviz
     digraph { ... }
     ```
     ```pintora
     erDiagram ... 
     ```
4. **Architekturbeschreibung** (textuell + Skizze: MVC-Schichten, Request-Flow)
5. **Testfälle** — ausführlich, inkl. fehlerhafter Eingaben
   - Vorlage: [`doc/visualization/test_results.typ`](../doc/visualization/test_results.typ)
6. Optional: Code-Metriken via [`doc/visualization/code_metrics.typ`](../doc/visualization/code_metrics.typ)
7. Projektangabe als Anhang (`pdf.attach` — bereits in `main.typ` vorbereitet)

### 10.2 Empfohlene Kapitelstruktur im PDF

1. Deckblatt / Metadaten  
2. Inhaltsverzeichnis  
3. Einleitung & Ziel  
4. Anforderungsübersicht  
5. Datenmodell (Diagramm + Tabellenbeschreibung)  
6. Architektur (MVC, Verzeichnisstruktur, Routing)  
7. Sicherheitskonzept  
8. Testfälle & Ergebnisse  
9. Installations- & Startanleitung  
10. Projektmitglieder & Zeitaufwand  

---

## 11. Testfälle (Mindestkatalog)

Jeder Testfall in der Doku dokumentieren: **ID, Vorbereitung, Schritte, Erwartetes Ergebnis, Ist-Ergebnis, Status**.

### 11.1 Authentifizierung

| ID | Szenario |
|---|---|
| T-AUTH-01 | Registrierung mit gültigen Daten → Erfolg |
| T-AUTH-02 | Registrierung mit duplicate username → Fehler |
| T-AUTH-03 | Registrierung mit zu kurzem Passwort → Fehler |
| T-AUTH-04 | Login mit korrekten Daten → Session aktiv |
| T-AUTH-05 | Login mit falschem Passwort → Fehler, keine Session |
| T-AUTH-06 | Logout → Session beendet, geschützte Aktionen nicht mehr möglich |

### 11.2 Zitate & Kommentare

| ID | Szenario |
|---|---|
| T-QUOTE-01 | Gast sieht Zitatliste und Detail |
| T-CMT-01 | Gast sieht Kommentare, kann nicht schreiben |
| T-CMT-02 | User erstellt Kommentar → erscheint mit Benutzername |
| T-CMT-03 | User bearbeitet eigenen Kommentar → Text aktualisiert |
| T-CMT-04 | User löscht eigenen Kommentar → verschwunden |
| T-CMT-05 | User versucht fremden Kommentar zu bearbeiten → 403 |
| T-CMT-06 | User versucht fremden Kommentar zu löschen → 403 |
| T-CMT-07 | Leerer Kommentar → Validierungsfehler |
| T-CMT-08 | XSS-Payload in Kommentar → escaped in Ausgabe |

### 11.3 Admin

| ID | Szenario |
|---|---|
| T-ADM-01 | Admin löscht fremden Kommentar → Erfolg |
| T-ADM-02 | Admin kann fremden Kommentar nicht bearbeiten (UI + direkte URL) |
| T-ADM-03 | Admin togglet User-Rolle |
| T-ADM-04 | Admin löscht Benutzer |
| T-ADM-05 | Nicht-Admin greift auf `/admin/*` zu → 403/Redirect |
| T-ADM-06 | Letzter Admin kann nicht degradiert werden |
| T-ADM-07 | Admin CRUD Zitate |

### 11.4 Sicherheit & Robustheit

| ID | Szenario |
|---|---|
| T-SEC-01 | SQL-Injection in Login-Feld → kein Bypass |
| T-SEC-02 | Direktaufruf `comments/{fremde_id}/edit` → 403 |
| T-SEC-03 | Ungültige Quote-ID → 404 |

---

## 12. Entwicklungsumgebung und Werkzeuge

| Aufgabe | Befehl / Ort |
|---|---|
| MySQL starten | `mise run run:db` |
| Typst-Doku bauen | `mise run build:documentation` |
| Abgabe-Paket erzeugen | `mise run package` |
| Typst formatieren | `mise run fmt:typst` |
| Apache Document Root | `public/` |
| DB-Socket (Dev) | `data/mysql.sock` (Port `33060`) |

---

## 13. Abgabe-Checkliste

### 13.1 ZIP-Inhalt (`WEB4_PHP_TEAM4.zip`)

- [ ] `public/` mit `index.php` als Einstieg
- [ ] `doc/WEB4_PHP_TEAM4.pdf`
- [ ] SQL-Dump (`doc/team_4.sql` o. ä.) mit:
  - `CREATE DATABASE team_4`
  - Tabellen, Constraints, Indizes
  - Testdaten (Admin, User, Zitate, Kommentare)
- [ ] Keine `.git/`, `data/`, IDE-Dateien, Secrets
- [ ] Auf frischer XAMPP-Installation importierbar und aufrufbar

### 13.2 Vor Abgabe verifizieren

- [ ] Apache: DocumentRoot zeigt auf entpacktes `public/`
- [ ] PHP ≥ 8.x
- [ ] DB-Import ohne Fehler in phpMyAdmin
- [ ] Alle Testfälle grün
- [ ] PDF vollständig und mit Diagrammen
- [ ] Ungenutzte Dateien/Code entfernt

---

## 14. Arbeitspakete (Implementierungsreihenfolge)

| Phase | Aufgabe | Abhängigkeiten |
|---|---|---|
| **1** | Projektgerüst: Front Controller, Router, Layout, Config/PDO | — |
| **2** | DB-Schema + SQL-Dump + Seed-Daten | Phase 1 |
| **3** | User-Model, Registrierung, Login, Session, Logout | Phase 2 |
| **4** | Quote-Model, Liste, Detail (read-only für Nicht-Admins) | Phase 2 |
| **5** | Comment-Model, CRUD inkl. Berechtigungen | Phase 3, 4 |
| **6** | Admin: Benutzerverwaltung | Phase 3 |
| **7** | Admin: Zitat-Verwaltung | Phase 4 |
| **8** | CSS/UX-Feinschliff, Flash-Messages, Fehlerseiten | parallel |
| **9** | Typst-Doku: ER-Diagramm, Architektur, Tests | fortlaufend |
| **10** | End-to-end-Tests, SQL-Dump finalisieren, Package | alle |

---

## 15. Offene Entscheidungen (bei Start klären)

| # | Frage | Vorschlag |
|---|---|---|
| 1 | Automatisches Login nach Registrierung? | Nein → Login-Seite mit Hinweis |
| 2 | CSRF-Tokens? | Ja, einheitlich für POST |
| 3 | Paginierung Zitate/Kommentare? | Ab 20 Einträgen |
| 4 | Bild-Upload vs. statische Pfade für Admin? | Erst statische Pfade; Upload optional |
| 5 | Löschverhalten bei User-Löschung | CASCADE auf Kommentare |
| 6 | Teammitglieder-Namen für Doku | In `doc/main.typ` ergänzen |

---

## 16. Referenzen

- [WEB4_Projekt_Angabe.md](../WEB4_Projekt_Angabe.md)
- [doc/main.typ](../doc/main.typ) — Dokumentations-Einstieg
- [mise.toml](../mise.toml) — DB-Konfiguration Entwicklung
- [mise.toml → tasks.package](../mise.toml) — Abgabe-Automatisierung

---

*Stand: 16.06.2026 — Spezifikation für Team 4, WEB4 PHP Übung SS 2026.*
