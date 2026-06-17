#import "deps.typ": *
#import "libs.typ": *
#import "template.typ": documentation-template
#import "visualization/test_results.typ": table-test-results-overview, table-test-results-detailed

#set document(
  author: ("Nathalie Sonnleitner", "Tim Peko"),
  title: "WEB4 PHP - Game of Thrones Quotes",
)
#show: documentation-template.with()

#pdf.attach(
  "../WEB4_Projekt_Angabe.pdf",
  mime-type: "application/pdf",
  relationship: "source",
  description: "Projektangabe",
)

#show raw.where(lang: "pintora"): it => pintora-diagram(it.text)
#show raw.where(lang: "graphviz"): diagraph.raw-render.with()

= Einleitung

Diese Dokumentation beschreibt die Webanwendung *GoT Quotes*. Es ist eine PHP-Anwendung im MVC-Pattern, die berühmte Game-of-Thrones-Zitate als *Foren-Posts* präsentiert. Eingeloggte Nutzer diskutieren in *threadartigen Kommentarbäumen* (Antworten auf Antworten). Administratoren verwalten Benutzer, Zitate inkl. Bild-Uploads; Nutzer können optionale Profilbilder setzen.

*Start-URL (XAMPP):* `http://localhost/`. Document Root muss auf das Verzeichnis `public/` zeigen.

= Projektmitglieder

#table(
  columns: (1fr, 1fr),
  table.header[*Name*][*Matrikelnummer*],
  [Nathalie Sonnleitner], [-],
  [Tim Peko], [s2420458029],
)

= Anforderungsübersicht

Die Anwendung erfüllt die Pflichtanforderungen der Projektangabe:

- MVC-Architektur mit PHP 8.x und PDO
- Rollenbasierte Interaktion (Gast, User, Admin)
- Registrierung und Login (Session-basiert, kein Auto-Login nach Registrierung)
- Vollständiges CRUD für Kommentare (Haupt-Ressource) und Zitate (Admin)
- Benutzerverwaltung für Admins (Löschen, Rolle ändern)
- Serverseitige Validierung, SQL-Injection- und XSS-Schutz
- Valides HTML5, Tailwind CSS 4 (CDN), JavaScript nur für REST-DELETE/PATCH (Fetch)
- REST-konforme HTTP-Methoden (GET/POST/PUT/PATCH/DELETE)
- Thread-Kommentare, Bild-Uploads (Zitate/Avatare)

== Rollenmodell

#table(
  columns: (2fr, 1fr, 1fr, 1fr),
  table.header[*Aktion*][*Gast*][*User*][*Admin*],
  [Zitate & Kommentare lesen], [✓], [✓], [✓],
  [Kommentar schreiben / eigenes bearbeiten & löschen], [-], [✓], [✓],
  [Fremden Kommentar löschen], [-], [-], [✓],
  [Fremden Kommentar bearbeiten], [-], [-], [✗],
  [Zitate verwalten (CRUD)], [-], [-], [✓],
  [Benutzerverwaltung], [-], [-], [✓],
)

Bei gelöschten Benutzern bleiben Kommentare erhalten; `user_id` wird auf `NULL` gesetzt und in der UI als graues `<deleted>` angezeigt.

= Datenmodell

== ER-Diagramm

```pintora
erDiagram
  users {
    int id PK
    string username UK
    string password_hash
    bool is_admin
    string avatar_path "NULL"
    datetime created_at
  }
  quotes {
    int id PK
    text text
    string speaker
    int season
    int episode
    string image_path "NULL"
    datetime created_at
  }
  comments {
    int id PK
    int quote_id FK
    int user_id FK "NULL, ON DELETE SET NULL"
    int parent_id FK "NULL, self, ON DELETE CASCADE"
    text content
    datetime created_at
    datetime updated_at
  }
  users ||--o{ comments : "writes"
  quotes ||--o{ comments : "has CASCADE"
  comments ||--o{ comments : "replies CASCADE"
```

== Tabellen

=== users

#table(
  columns: (1fr, 1fr, 2fr),
  table.header[*Spalte*][*Typ*][*Constraints*],
  [id], [INT UNSIGNED], [PK, AUTO_INCREMENT],
  [username], [VARCHAR(50)], [NOT NULL, UNIQUE],
  [password_hash], [VARCHAR(255)], [NOT NULL],
  [is_admin], [TINYINT(1)], [NOT NULL, DEFAULT 0],
  [avatar_path], [VARCHAR(255)], [NULL, Profilbild unter /uploads/avatars/],
  [created_at], [DATETIME], [DEFAULT CURRENT_TIMESTAMP],
)

=== quotes

#table(
  columns: (1fr, 1fr, 2fr),
  table.header[*Spalte*][*Typ*][*Constraints*],
  [id], [INT UNSIGNED], [PK, AUTO_INCREMENT],
  [text], [TEXT], [NOT NULL],
  [speaker], [VARCHAR(100)], [NOT NULL],
  [season / episode], [TINYINT UNSIGNED], [NULL, optional],
  [image_path], [VARCHAR(255)], [NULL, Beitragsbild unter /uploads/quotes/],
  [created_at], [DATETIME], [DEFAULT CURRENT_TIMESTAMP],
)

=== comments

#table(
  columns: (1fr, 1fr, 2fr),
  table.header[*Spalte*][*Typ*][*Constraints*],
  [id], [INT UNSIGNED], [PK, AUTO_INCREMENT],
  [quote_id], [INT UNSIGNED], [FK → quotes.id, ON DELETE CASCADE],
  [user_id], [INT UNSIGNED], [FK → users.id, NULL, ON DELETE SET NULL],
  [parent_id], [INT UNSIGNED], [FK → comments.id, NULL, ON DELETE CASCADE],
  [content], [TEXT], [NOT NULL],
  [created_at], [DATETIME], [DEFAULT CURRENT_TIMESTAMP],
  [updated_at], [DATETIME], [NULL ON UPDATE],
)

== Testdaten

SQL-Dump: `sql/WEB4_PHP_TEAM4.sql`

- Admin: `admin` / `admin`
- Testuser: `tyrion_fan`, `arya_fan` (Passwort: `password123`)
- 12 Zitate, 17 Kommentare (inkl. verschachtelter Antworten)

= Session und Login

Nach dem Login speichern wir den eingeloggten User in der *PHP-Session*. Dafür braucht man zwei Dinge:

+ `session_start()` in `bootstrap.php` startet die Session und macht `$_SESSION` verfügbar
+ Werte in `$_SESSION` schreiben/lesen, z.B. `$_SESSION['user_id']`, `$_SESSION['username']`, `$_SESSION['is_admin']`

`AuthService` kapselt das: beim Login setzen wir die Session-Werte, beim Logout werden sie gelöscht. Auf jeder geschützten Seite prüfen wir mit `AuthService::check()` bzw. `requireLogin()`, ob jemand eingeloggt ist.

Flash-Messages (Erfolg/Fehler nach Redirect) liegen ebenfalls in `$_SESSION` unter `_flash` und werden nach dem Anzeigen entfernt.

== Verwendete PHP-Konzepte

Im Projekt kommen vor allem folgende Themen aus der Übung zum Einsatz:

- *PDO*: Datenbankzugriff mit Prepared Statements (`prepare`, `execute`, Platzhalter `:name`)
- *Sessions*: Login-Status in `$_SESSION` (siehe oben)
- *password\_hash / password\_verify*: Passwörter sicher speichern
- *htmlspecialchars*: User-Input beim Ausgeben escapen (XSS)
- *include/require*: Views und Config einbinden
- *GET/POST*: Formulare und `$_POST`, Links und `$_GET`

= Architektur

== MVC-Schichten

```graphviz
digraph MVC {
  rankdir=TB;
  node [shape=box, fontname="Roboto"];

  Browser -> index_php [label="HTTP"];
  index_php [label="index.php"];
  index_php -> Router;
  Router -> Controller;
  Controller -> Model;
  Model -> MySQL [label="PDO"];
  Controller -> View;
  View -> Browser [label="HTML"];
}
```

== Verzeichnisstruktur

```
public/
├── index.php              # Front Controller, REST-Routing
├── .htaccess              # URL Rewriting
├── assets/js/app.js       # Fetch DELETE/PATCH
├── uploads/               # Avatare & Zitatbilder
├── src/
│   ├── bootstrap.php
│   ├── config.php
│   ├── Router.php         # GET, POST, PUT, PATCH, DELETE
│   ├── Controller/        # Auth, Profile, Quote, Comment, Admin/*
│   ├── Model/             # User, Quote, Comment
│   └── Service/           # AuthService, ValidationService, UploadService
└── views/                 # PHP HTML-Templates (Tailwind CDN)
```

== Request-Flow (Beispiel: Kommentar löschen)

1. `DELETE /comments/{id}` → Router (Fetch aus `app.js`)
2. `CommentController::destroy`: CSRF via `X-CSRF-Token`, Login, Berechtigung
3. `Comment::delete`: Prepared Statement; CASCADE löscht Antworten
4. Redirect zur Zitat-Detailseite mit Flash-Message

== REST-Routing (Auszug)

#table(
  columns: (1fr, 2fr, 2fr),
  table.header[*Methode*][*Pfad*][*Aktion*],
  [GET], [/], [Zitate-Feed],
  [POST], [/quotes/{id}/comments], [Top-Level-Kommentar],
  [POST], [/comments/{id}/replies], [Thread-Antwort],
  [DELETE], [/comments/{id}], [Kommentar löschen (Fetch)],
  [DELETE], [/admin/quotes/{id}], [Zitat löschen (Fetch)],
  [PATCH], [/admin/users/{id}/admin], [Admin-Rolle toggeln (Fetch)],
)

= Sicherheitskonzept

#table(
  columns: (1.5fr, 3fr),
  table.header[*Bedrohung*][*Maßnahme*],
  [SQL Injection], [PDO Prepared Statements für alle Queries mit User-Input],
  [XSS], [`htmlspecialchars()` bei jeder Ausgabe usergenerierter Inhalte],
  [Session Fixation], [`session_regenerate_id()` nach Login],
  [CSRF], [Token in allen POST-Formularen, Validierung in Controllern],
  [IDOR], [Berechtigungsprüfung: Kommentar-Bearbeitung nur für Autor],
  [Passwörter], [`password_hash()` / `password_verify()`, PASSWORD_DEFAULT],
  [REST / JS], [DELETE und PATCH per Fetch + CSRF-Header; Validierung nur serverseitig],
  [Uploads], [MIME-Check via finfo, Größenlimit, `/uploads/.htaccess` ohne Script-Ausführung],
)

= Testfälle

Alle Tests wurden manuell in Google Chrome unter XAMPP durchgeführt.

#let test-data = (
  tests: 26,
  failures: 0,
  errors: 0,
  disabled: 0,
  testsuites: (
    (
      name: "Authentifizierung",
      tests: 6,
      failures: 0,
      errors: 0,
      disabled: 0,
      testsuite: (
        (name: "T-AUTH-01: Registrierung gültig → Erfolg, Redirect Login", status: "passed", time: [manuell]),
        (name: "T-AUTH-02: Duplicate Username → Fehler", status: "passed", time: [manuell]),
        (name: "T-AUTH-03: Passwort < 8 Zeichen → Fehler", status: "passed", time: [manuell]),
        (name: "T-AUTH-04: Login korrekt → Session aktiv", status: "passed", time: [manuell]),
        (name: "T-AUTH-05: Login falsch → generische Fehlermeldung", status: "passed", time: [manuell]),
        (name: "T-AUTH-06: Logout → Session beendet", status: "passed", time: [manuell]),
      ),
    ),
    (
      name: "Zitate & Kommentare",
      tests: 9,
      failures: 0,
      errors: 0,
      disabled: 0,
      testsuite: (
        (name: "T-QUOTE-01: Gast sieht Liste und Detail", status: "passed", time: [manuell]),
        (name: "T-CMT-01: Gast sieht Kommentare, kein Formular", status: "passed", time: [manuell]),
        (name: "T-CMT-02: User erstellt Kommentar mit Username", status: "passed", time: [manuell]),
        (name: "T-CMT-03: User bearbeitet eigenen Kommentar", status: "passed", time: [manuell]),
        (name: "T-CMT-04: User löscht eigenen Kommentar", status: "passed", time: [manuell]),
        (name: "T-CMT-05: Fremdes Edit → 403", status: "passed", time: [manuell]),
        (name: "T-CMT-06: Fremdes Delete → 403", status: "passed", time: [manuell]),
        (name: "T-CMT-07: Leerer Kommentar → Validierungsfehler", status: "passed", time: [manuell]),
        (name: "T-CMT-08: XSS-Payload → escaped in Ausgabe", status: "passed", time: [manuell]),
      ),
    ),
    (
      name: "Administration",
      tests: 8,
      failures: 0,
      errors: 0,
      disabled: 0,
      testsuite: (
        (name: "T-ADM-01: Admin löscht fremden Kommentar", status: "passed", time: [manuell]),
        (name: "T-ADM-02: Admin kann fremden Kommentar nicht bearbeiten", status: "passed", time: [manuell]),
        (name: "T-ADM-03: Admin togglet User-Rolle", status: "passed", time: [manuell]),
        (name: "T-ADM-04: Admin löscht User → Kommentare zeigen <deleted>", status: "passed", time: [manuell]),
        (name: "T-ADM-05: Nicht-Admin auf /admin → 403", status: "passed", time: [manuell]),
        (name: "T-ADM-06: Letzter Admin nicht degradierbar", status: "passed", time: [manuell]),
        (name: "T-ADM-07: Admin CRUD Zitate", status: "passed", time: [manuell]),
        (name: "T-ADM-08: Admin kann eigene Rolle nicht entziehen", status: "passed", time: [manuell]),
      ),
    ),
    (
      name: "Sicherheit",
      tests: 3,
      failures: 0,
      errors: 0,
      disabled: 0,
      testsuite: (
        (name: "T-SEC-01: SQL-Injection Login → kein Bypass", status: "passed", time: [manuell]),
        (name: "T-SEC-02: Direktaufruf fremdes Edit → 403", status: "passed", time: [manuell]),
        (name: "T-SEC-03: Ungültige Quote-ID → 404", status: "passed", time: [manuell]),
      ),
    ),
  ),
)

== Übersicht

#table-test-results-overview(test-data)

== Detailergebnisse

#table-test-results-detailed(test-data)

= Installation

== Voraussetzungen

- XAMPP (Apache + PHP 8.x + MySQL/MariaDB)

== Schritte

+ ZIP entpacken
+ In phpMyAdmin `sql/WEB4_PHP_TEAM4.sql` importieren
+ Apache DocumentRoot auf `public/` setzen
+ Anwendung unter `http://localhost/` aufrufen
+ Mit `admin` / `admin` einloggen

Datenbank laut Projektangabe: `team_4`, User `fh_webphp`, Passwort `fh_webphp`.
