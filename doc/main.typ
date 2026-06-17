#import "deps.typ": *
#import "libs.typ": *
#import "template.typ": documentation-template
#import "visualization/test_results.typ": table-test-results-overview, table-test-results-detailed
#import "visualization/httpyac_test_results.typ": parse-httpyac-junit, table-httpyac-overview, table-httpyac-detailed

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
- Thread-Kommentare, Bild-Uploads (Zitate/Avatare), Likes und Votes
- Feed-Sortierung (neu/top/trending), öffentliche Nutzerprofile

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
  quote_likes {
    int user_id FK
    int quote_id FK
    datetime created_at
  }
  comment_votes {
    int id PK
    int comment_id FK
    int user_id FK "NULL, ON DELETE SET NULL"
    int vote "1 or -1"
    datetime created_at
  }
  users ||--o{ comments : "writes"
  users ||--o{ quote_likes : "likes"
  quotes ||--o{ quote_likes : "liked by"
  quotes ||--o{ comments : "has CASCADE"
  comments ||--o{ comments : "replies CASCADE"
  comments ||--o{ comment_votes : "votes CASCADE"
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

=== quote_likes

#table(
  columns: (1fr, 1fr, 2fr),
  table.header[*Spalte*][*Typ*][*Constraints*],
  [user_id], [INT UNSIGNED], [FK → users.id, ON DELETE CASCADE],
  [quote_id], [INT UNSIGNED], [FK → quotes.id, ON DELETE CASCADE],
  [created_at], [DATETIME], [DEFAULT CURRENT_TIMESTAMP],
)

Primärschlüssel: `(user_id, quote_id)` — ein Like pro Nutzer und Zitat.

=== comment_votes

#table(
  columns: (1fr, 1fr, 2fr),
  table.header[*Spalte*][*Typ*][*Constraints*],
  [id], [INT UNSIGNED], [PK, AUTO_INCREMENT],
  [comment_id], [INT UNSIGNED], [FK → comments.id, ON DELETE CASCADE],
  [user_id], [INT UNSIGNED], [FK → users.id, NULL, ON DELETE SET NULL],
  [vote], [TINYINT], [NOT NULL, Werte 1 oder -1],
  [created_at], [DATETIME], [DEFAULT CURRENT_TIMESTAMP],
)

== Testdaten

SQL-Dump: `WEB4_PHP_TEAM7.sql`

- Admin: `admin` / `admin`
- Testuser: `tyrion_fan`, `arya_fan` (Passwort: `password123`)
- 12 Zitate, 17 Kommentare (inkl. verschachtelter Antworten), Likes und Votes

= Session und Login

Login-Status liegt in der PHP-Session (`$_SESSION`: `user_id`, `username`, `is_admin`, `avatar_path`). `AuthService` kapselt Login, Logout und Berechtigungsprüfungen. Flash-Messages werden nach Redirect einmalig angezeigt.

Verwendete PHP-Konzepte: PDO mit Prepared Statements, Sessions, `password_hash`/`password_verify`, `htmlspecialchars`, MVC mit `include`/`require`.

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
│   ├── Model/             # User, Quote, Comment, QuoteLike, CommentVote
│   └── Service/           # AuthService, ValidationService, UploadService
└── views/                 # PHP-Templates (Tailwind CDN)
```

== Request-Flow (Beispiel: Kommentar löschen)

1. `DELETE /comments/{id}` → Router (Fetch aus `app.js`)
2. `CommentController::destroy`: CSRF, Login, Berechtigung
3. `Comment::delete` mit CASCADE auf Antworten
4. Redirect zur Zitat-Detailseite

== REST-Routing (Auszug)

#table(
  columns: (1fr, 2fr, 2fr),
  table.header[*Methode*][*Pfad*][*Aktion*],
  [GET], [/], [Zitate-Feed (`?sort=new|top|trending`)],
  [POST/DELETE], [/quotes/{id}/likes], [Zitat liken / Like entfernen],
  [POST/DELETE], [/comments/{id}/votes], [Kommentar bewerten],
  [GET], [/users/{id}], [Öffentliches Profil],
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

Die REST-Schnittstelle wird automatisiert mit *httpYac* getestet. httpYac ist ein CLI-Runner für `.rest`-Dateien (kompatibel mit IntelliJ HTTP Client / VS Code REST Client). Jede Anfrage enthält Assertions (`?? status == 200`, `?? body includes …`), die Statuscode und Response-Body prüfen.

== Test-Runner

#table(
  columns: (1.2fr, 2.8fr),
  table.header[*Tool*][*Verwendung*],
  [httpYac CLI], [`npm install -g httpyac` oder via `mise install`],
  [Tests ausführen], [`mise run test:rest`],
  [JUnit-Report], [`doc/test-results/httpyac-junit.xml`],
  [Testdateien], [`tests/rest/*.rest`],
)

Voraussetzungen: PHP-Server und MySQL laufen bereits (z. B. `mise run run:app` und `mise run run:db`), SQL-Dump `WEB4_PHP_TEAM7.sql` importiert. Standard-URL: `http://127.0.0.1:8080`.

== Automatisierte REST-Tests (httpYac)

#let rest-junit-path = "../test-results/httpyac-junit.xml"
#let rest-data = parse-httpyac-junit(rest-junit-path)

#if rest-data != none [
  Die folgenden Tabellen wurden aus dem JUnit-Report generiert. Spalte *Erwartet* stammt aus der Assertion in der `.rest`-Datei; *Beobachtet* ist der tatsächliche Wert (bei Fehlern aus der httpYac-Fehlermeldung).

  === Übersicht

  #table-httpyac-overview(rest-data)

  === Erwartete und beobachtete Werte

  #table-httpyac-detailed(rest-data)
] else [
  _Kein JUnit-Report gefunden._ Ausführen mit: `mise run test:rest`
]

== Manuelle UI-Tests

Zusätzlich wurden folgende Szenarien manuell in Google Chrome geprüft (Uploads, responsives Layout, Thread-Darstellung):

#let test-data = (
  tests: 32,
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
      name: "Forum & Interaktion",
      tests: 6,
      failures: 0,
      errors: 0,
      disabled: 0,
      testsuite: (
        (name: "T-FORUM-01: Feed-Sortierung top/trending", status: "passed", time: [manuell]),
        (name: "T-FORUM-02: Zitat liken und entliken", status: "passed", time: [manuell]),
        (name: "T-FORUM-03: Kommentar up-/downvoten", status: "passed", time: [manuell]),
        (name: "T-FORUM-04: Kommentar-Sortierung auf Detailseite", status: "passed", time: [manuell]),
        (name: "T-FORUM-05: Öffentliches Profil mit Kommentaren und Likes", status: "passed", time: [manuell]),
        (name: "T-FORUM-06: Thread-Antworten und CASCADE-Löschung", status: "passed", time: [manuell]),
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
+ In phpMyAdmin `WEB4_PHP_TEAM7.sql` importieren
+ Apache DocumentRoot auf `public/` setzen
+ Anwendung unter `http://localhost/` aufrufen
+ Mit `admin` / `admin` einloggen

Datenbank: `team_7`, User `fh_webphp`, Passwort `fh_webphp`.
