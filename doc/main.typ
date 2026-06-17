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

Diese Dokumentation beschreibt die Webanwendung *GoT Quotes*, die im Rahmen der Übung WEB4 im Sommersemester 2026 entwickelt wurde. Die Anwendung präsentiert berühmte Game-of-Thrones-Zitate als Forenbeiträge. Besucher können Zitate lesen und sortieren, eingeloggte Nutzer diskutieren in threadartigen Kommentarbäumen, und Administratoren verwalten Benutzer sowie Zitate inklusive optionaler Bild-Uploads.

Die Umsetzung folgt dem MVC-Pattern mit PHP 8.x und PDO. Für mindestens eine Ressource sind alle CRUD-Operationen implementiert: Kommentare bilden die zentrale Ressource für normale Nutzer, Zitate werden vollständig im Admin-Bereich gepflegt. Sämtliche Benutzereingaben werden serverseitig validiert, SQL-Injection und XSS werden durch Prepared Statements und konsequentes Escaping abgewehrt.

*Start-URL (Standard-XAMPP):* `http://localhost/`

Damit die Anwendung erreichbar ist, muss der Apache-Document-Root auf das Verzeichnis `public/` zeigen. Unter unserer Entwicklungsumgebung mit `mise` ist die Anwendung alternativ unter `http://127.0.0.1:80/` erreichbar. Der Einstiegspunkt ist stets `public/index.php`.

= Projektmitglieder

Gemäß Projektangabe werden alle beteiligten Personen aufgeführt:

#table(
  columns: (1fr, 1fr),
  table.header[*Name*][*Matrikelnummer*],
  [Nathalie Sonnleitner], [-],
  [Tim Peko], [s2420458029],
)

= Bezug zur Projektangabe

Die folgende Übersicht ordnet die Pflichtanforderungen aus der Projektangabe der konkreten Umsetzung zu. Jeder Punkt ist im weiteren Verlauf dieser Dokumentation ausführlicher beschrieben.

#table(
  columns: (2fr, 3fr),
  table.header[*Anforderung (Projektangabe)*][*Umsetzung in GoT Quotes*],
  [MVC-Pattern und CRUD für mindestens eine Ressource],
  [Schichten Controller, Model, View; vollständiges CRUD für Kommentare und Admin-CRUD für Zitate],
  [Rollen: Gast, eingeloggter User, Admin],
  [Session mit `is_admin`; unterschiedliche Navigation und Berechtigungen pro Rolle],
  [Registrierung mit eigener Seite],
  [`GET/POST /register`; optionaler Avatar-Upload; Redirect zum Login ohne Auto-Login],
  [Admin-Benutzerverwaltung],
  [`/admin/users`: Benutzer löschen, Admin-Rolle per PATCH toggeln],
  [PHP ≥ 8.x, PDO, saubere Schichtentrennung],
  [Strict Types, eigener Router, Models mit ausschließlich Prepared Statements],
  [Schutz vor SQL-Injection und XSS],
  [PDO-Parameterbindung; `htmlspecialchars()` bei jeder HTML-Ausgabe usergenerierter Inhalte],
  [Serverseitige Validierung aller Eingaben],
  [`ValidationService` prüft Formulare und Uploads; Fehler direkt am Formular oder per Flash],
  [Self-contained ohne Composer/npm],
  [ZIP auf XAMPP kopierbar; Tailwind CSS 4 und httpYac nur optional für Entwicklung],
  [MySQL/MariaDB mit sinnvollem Schema],
  [Normalisierte Tabellen, FK-Constraints, ON DELETE CASCADE bzw. SET NULL],
  [Valides HTML5, Formatierung per CSS],
  [Semantisches HTML5; Tailwind CSS 4 per CDN; kein Inline-Styling als Hauptmechanismus],
  [JavaScript nicht für Validierung],
  [`app.js` nur für Fetch (DELETE/PATCH) und UI-Helfer; Validierung ausschließlich serverseitig],
  [ER-/UML-Diagramm, Architekturbeschreibung, Start-URL, Team],
  [Dieses PDF-Dokument in `doc/`],
  [Ausführliche Tests inkl. fehlerhafter Eingaben],
  [Automatisierte httpYac-Tests plus dokumentierte manuelle UI-Tests],
  [SQL-Dump mit CREATE DATABASE und Testdaten],
  [`WEB4_PHP_TEAM7.sql`; Datenbank `team_7`, User `fh_webphp`],
)

= Anwendungsüberblick

GoT Quotes verbindet ein Zitate-Archiv mit Forenfunktionen. Jeder Datensatz in der Tabelle `quotes` entspricht einem Forenpost mit Zitattext, Sprecher, optional Staffel und Episode sowie optionalem Beitragsbild. Unter jedem Zitat entsteht eine Diskussion: Top-Level-Kommentare hängen direkt am Zitat, Antworten referenzieren über `parent_id` den jeweiligen Elternkommentar. Dadurch entsteht ein Baum beliebiger Tiefe, der in der Oberfläche eingerückt dargestellt wird.

Eingeloggte Nutzer können Zitate liken, Kommentare bewerten und ihr Profilbild pflegen. Administratoren erhalten zusätzlich Zugriff auf die Benutzerverwaltung und die Zitat-Pflege. Gäste sehen Feed, Detailseiten und öffentliche Profile, dürfen sich registrieren und einloggen, können aber weder kommentieren noch liken.

== Nutzerfluss nach Rolle

Das folgende Aktivitätsdiagramm zeigt typische Pfade durch die Anwendung. Die Navigation passt sich der Session an: Gäste sehen Links zu Login und Registrierung, eingeloggte Nutzer zu Profil und Logout, Admins zusätzlich Einträge in den Admin-Bereich.

```pintora
activityDiagram
  start
  :Seite aufrufen;
  if (Eingeloggt?) then (nein)
    :Feed lesen;
    :Registrieren oder Login;
    stop
  else (ja)
    if (Admin?) then (ja)
      :Feed, Profil oder Admin;
      :Benutzer oder Zitate verwalten;
    else (nein)
      :Feed, Profil, Kommentare;
      :Like, Vote, Antworten;
    endif
    stop
  endif
```

== Rollen und Berechtigungen

Drei Rollen steuern den Zugriff. Gäste sind nicht authentifiziert. Benutzer (`is_admin = 0`) dürfen eigene Kommentare erstellen, bearbeiten und löschen. Administratoren (`is_admin = 1`) verwalten zusätzlich Benutzer und Zitate und dürfen fremde Kommentare löschen, aber nicht bearbeiten.

#table(
  columns: (2fr, 1fr, 1fr, 1fr),
  table.header[*Aktion*][*Gast*][*User*][*Admin*],
  [Zitate und Kommentare lesen], [✓], [✓], [✓],
  [Registrierung und Login], [✓], [✓], [✓],
  [Kommentar schreiben, eigenes bearbeiten und löschen], [-], [✓], [✓],
  [Fremden Kommentar löschen], [-], [-], [✓],
  [Fremden Kommentar bearbeiten], [-], [-], [✗],
  [Zitate verwalten (CRUD)], [-], [-], [✓],
  [Benutzerverwaltung], [-], [-], [✓],
)

Wird ein Benutzer gelöscht, bleiben seine Kommentare in der Datenbank erhalten. Der Fremdschlüssel `user_id` wird per `ON DELETE SET NULL` auf `NULL` gesetzt und in der Benutzeroberfläche als graues `<deleted>` angezeigt. So bleibt der Diskussionsverlauf nachvollziehbar.

= Datenmodell

== ER-Diagramm

Das folgende Entity-Relationship-Diagramm wurde manuell modelliert und visualisiert die Entitäten sowie deren Beziehungen. Es entspricht dem SQL-Schema in `WEB4_PHP_TEAM7.sql`.

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

== Normalisierung und Constraints

Die Datenbank folgt den Grundregeln des relationalen Designs. Jede Entität besitzt einen numerischen Primärschlüssel. Benutzernamen sind eindeutig. Fremdschlüssel sichern referenzielle Integrität: Kommentare und Likes werden mit dem zugehörigen Zitat gelöscht (`CASCADE`), Nutzerreferenzen in Kommentaren und Votes werden bei Nutzerlöschung auf `NULL` gesetzt (`SET NULL`). Antworten referenzieren den Elternkommentar über `parent_id`; beim Löschen eines Kommentars mit Kindern greift `ON DELETE CASCADE` auf die gesamte Unterdiskussion.

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

Passwörter werden ausschließlich als Hash gespeichert (`password_hash` via `password_hash()`). Klartextpasswörter existieren nicht in der Datenbank.

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

Kommentare sind die Haupt-CRUD-Ressource der Anwendung. `parent_id = NULL` kennzeichnet Top-Level-Kommentare, sonst handelt es sich um Thread-Antworten.

=== quote_likes

#table(
  columns: (1fr, 1fr, 2fr),
  table.header[*Spalte*][*Typ*][*Constraints*],
  [user_id], [INT UNSIGNED], [FK → users.id, ON DELETE CASCADE],
  [quote_id], [INT UNSIGNED], [FK → quotes.id, ON DELETE CASCADE],
  [created_at], [DATETIME], [DEFAULT CURRENT_TIMESTAMP],
)

Primärschlüssel ist das Paar `(user_id, quote_id)`. Pro Nutzer und Zitat ist höchstens ein Like möglich.

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

Der SQL-Dump `WEB4_PHP_TEAM7.sql` enthält das `CREATE DATABASE`-Statement, Tabellendefinitionen und repräsentative Testdaten. Nach dem Import stehen folgende Zugangsdaten zur Verfügung:

- Admin: `admin` / `admin`
- Testnutzer: `tyrion_fan`, `arya_fan` (Passwort jeweils `password123`)
- 12 Zitate, 17 Kommentare inklusive verschachtelter Antworten, Likes und Votes

= Architektur

== Schichtenmodell (MVC)

Die Projektangabe verlangt eine saubere Trennung der Anwendungsschichten. GoT Quotes implementiert klassisches MVC: Der Front Controller `index.php` leitet jede HTTP-Anfrage an den Router weiter. Controller orchestrieren Geschäftslogik und Berechtigungen, Models kapseln den PDO-Datenbankzugriff, Views rendern HTML mit escaped Ausgabe.

```graphviz
digraph MVC {
  rankdir=TB;
  node [shape=box, fontname="Roboto"];

  Browser -> index_php [label="HTTP"];
  index_php [label="index.php\nFront Controller"];
  index_php -> Router;
  Router -> Controller [label="dispatch"];
  Controller -> Model [label="Queries"];
  Model -> MySQL [label="PDO"];
  Controller -> View [label="render"];
  View -> Browser [label="HTML"];
}
```

== Komponentenübersicht

Innerhalb von `public/src/` sind die Verantwortlichkeiten klar getrennt. Services wie `AuthService`, `ValidationService` und `UploadService` enthalten querschnittliche Logik, die von mehreren Controllern genutzt wird.

```pintora
componentDiagram
  [Browser] --> [index.php]
  [index.php] --> [Router]
  [Router] --> [Controller]
  [Controller] --> [AuthService]
  [Controller] --> [ValidationService]
  [Controller] --> [UploadService]
  [Controller] --> [Model]
  [Model] --> [MySQL]
  [Controller] --> [View]
  [View] --> [Browser]
```

== Verzeichnisstruktur

```
public/
├── index.php              # Front Controller, REST-Routing, _method-Override
├── .htaccess              # URL Rewriting
├── assets/js/app.js       # Fetch für DELETE/PATCH, Thread-UI
├── uploads/               # Avatare und Zitatbilder (.htaccess ohne Script-Ausführung)
├── src/
│   ├── bootstrap.php
│   ├── config.php
│   ├── Router.php         # GET, POST, PUT, PATCH, DELETE
│   ├── Controller/        # Auth, Profile, Quote, Comment, Admin/*
│   ├── Model/             # User, Quote, Comment, QuoteLike, CommentVote
│   └── Service/           # AuthService, ValidationService, UploadService
└── views/                 # PHP-Templates (Tailwind CDN)
```

Jede Schicht kennt nur die darunterliegende: Views enthalten kein SQL, Models enthalten keine HTTP-Logik. Der Router mappt Pfade und HTTP-Methoden auf Controller-Methoden und extrahiert Platzhalter wie `{id}`.

== Ablauf: Login und Session

Authentifizierung erfolgt sessionbasiert. Nach erfolgreichem Login speichert PHP in `$_SESSION` die Felder `user_id`, `username`, `is_admin` und optional `avatar_path`. `session_regenerate_id()` verhindert Session-Fixation. Flash-Messages informieren einmalig über Erfolg oder Fehler nach Redirects.

```pintora
sequenceDiagram
  participant [<actor> Browser]
  participant [<node> Router]
  participant [<node> AuthController]
  participant [<database> MySQL]

  Browser->>Router: POST /login (username, password, _csrf)
  Router->>AuthController: login()
  AuthController->>AuthController: CSRF prüfen
  AuthController->>MySQL: SELECT user (Prepared Statement)
  alt Zugangsdaten gültig
    AuthController->>AuthController: session_regenerate_id()
    AuthController->>Browser: 302 Redirect /
  else ungültig
    AuthController->>Browser: 200 Formular mit Fehlermeldung
  end
```

== Ablauf: Kommentar anlegen (CRUD Create)

Das Erstellen eines Kommentars verdeutlicht die CRUD-Umsetzung für die Hauptressource. Der Controller prüft Login, CSRF und Validierung, bevor das Model den Datensatz einfügt.

```pintora
sequenceDiagram
  participant [<actor> Browser]
  participant [<node> CommentController]
  participant [<node> ValidationService]
  participant [<database> CommentModel] as "Comment Model"

  Browser->>CommentController: POST /quotes/{id}/comments
  CommentController->>CommentController: requireLogin(), CSRF
  CommentController->>ValidationService: validate content
  alt Inhalt leer oder zu lang
    CommentController->>Browser: 200 Formular mit Fehler
  else gültig
    CommentController->>CommentModel: INSERT (Prepared Statement)
    CommentController->>Browser: 302 Redirect /quotes/{id}
  end
```

== Ablauf: Kommentar löschen per REST

Löschungen nutzen die HTTP-Methode DELETE und werden per JavaScript Fetch ausgelöst, da HTML-Formulare DELETE nicht nativ unterstützen. Der CSRF-Token wird als Header `X-CSRF-Token` mitgesendet.

```pintora
sequenceDiagram
  participant [<actor> Browser]
  participant [<node> AppJs] as "app.js"
  participant [<node> CommentController]
  participant [<database> CommentModel] as "Comment Model"

  Browser->>AppJs: Klick Löschen (bestätigt)
  AppJs->>CommentController: DELETE /comments/{id} + CSRF-Header
  CommentController->>CommentController: Berechtigung (Autor oder Admin)
  alt berechtigt
    CommentController->>CommentModel: DELETE (CASCADE auf Replies)
    CommentController->>Browser: 302 Redirect
  else nicht berechtigt
    CommentController->>Browser: 403 Forbidden
  end
```

== REST-Routing

Die Anwendung verwendet ressourcenorientierte URLs und semantisch korrekte HTTP-Methoden. Es gibt keine Aktions-URLs wie `/comments/delete`. Browser-Formulare ohne Fetch nutzen bei PUT/PATCH/DELETE das versteckte Feld `_method`; `index.php` setzt daraus die effektive Request-Methode.

#table(
  columns: (1fr, 2fr, 2fr),
  table.header[*Methode*][*Pfad*][*Aktion*],
  [GET], [/], [Zitate-Feed mit `?sort=new|top|trending`],
  [GET], [/quotes/{id}], [Zitat-Detail mit Kommentar-Thread],
  [POST/DELETE], [/quotes/{id}/likes], [Zitat liken bzw. Like entfernen],
  [POST/DELETE], [/comments/{id}/votes], [Kommentar bewerten],
  [POST], [/quotes/{id}/comments], [Top-Level-Kommentar erstellen],
  [POST], [/comments/{id}/replies], [Thread-Antwort erstellen],
  [PUT], [/comments/{id}], [Eigenen Kommentar bearbeiten],
  [DELETE], [/comments/{id}], [Kommentar löschen (Autor oder Admin)],
  [GET], [/register], [Registrierungsformular],
  [POST], [/register], [Neuen Benutzer anlegen],
  [GET/POST], [/login], [Login-Formular bzw. Authentifizierung],
  [POST], [/logout], [Session beenden],
  [GET], [/users/{id}], [Öffentliches Profil],
  [GET/PATCH/DELETE], [/admin/users ...], [Benutzerverwaltung],
  [GET/POST/PUT/DELETE], [/admin/quotes ...], [Zitat-CRUD für Admins],
)

= Backend und Sicherheit

== PDO und Prepared Statements

Sämtliche Datenbankzugriffe laufen über PDO mit Prepared Statements. User-Input wird nie per String-Konkatenation in SQL eingebettet. Die Model-Klassen kapseln alle Queries und geben gebundene Parameter an PDO weiter. Damit ist die Anwendung gegen SQL-Injection abgesichert.

== Validierung

Jeder textuelle Input aus Formularen oder Uploads wird serverseitig geprüft. Der `ValidationService` setzt unter anderem folgende Regeln durch:

#table(
  columns: (1.5fr, 3fr),
  table.header[*Feld*][*Regeln*],
  [username], [3 bis 50 Zeichen, nur `[a-zA-Z0-9_]`, eindeutig],
  [password], [mindestens 8 Zeichen],
  [comment.content], [1 bis 1000 Zeichen, nicht leer nach trim()],
  [quote.text], [1 bis 2000 Zeichen],
  [quote.speaker], [1 bis 100 Zeichen],
  [Bild-Uploads], [optional, max. 2 MB, JPEG/PNG/WebP, MIME-Check via finfo],
)

Fehlerhafte Eingaben führen nicht zu stillschweigendem Verwerfen: Das Formular wird erneut angezeigt, Fehlermeldungen erscheinen am betroffenen Feld oder als Flash-Message. Auch in den automatisierten Tests (`cmtEmptyRejected`, `authLoginFail`) werden fehlerhafte Eingaben explizit geprüft.

== Schutzmaßnahmen im Überblick

#table(
  columns: (1.5fr, 3fr),
  table.header[*Bedrohung*][*Maßnahme*],
  [SQL Injection], [PDO Prepared Statements für alle Queries mit User-Input],
  [XSS], [`htmlspecialchars()` bei jeder Ausgabe usergenerierter Inhalte],
  [Session Fixation], [`session_regenerate_id()` nach Login],
  [CSRF], [Token in POST-Formularen und Fetch-Header `X-CSRF-Token`],
  [IDOR], [Bearbeitung nur für Kommentar-Autor; Admin darf fremde Kommentare nur löschen],
  [Passwörter], [`password_hash()` / `password_verify()`, PASSWORD_DEFAULT],
  [Uploads], [MIME-Check, Größenlimit, zufällige Dateinamen, `.htaccess` ohne Script-Ausführung],
)

= Frontend und Benutzeroberfläche

== HTML, CSS und JavaScript

Die Oberfläche besteht aus validem HTML5. Formatierungen erfolgen ausschließlich über CSS, konkret Tailwind CSS 4 per CDN im Layout-Template. Die Navigation führt Gäste zu Feed, Login und Registrierung, eingeloggte Nutzer zusätzlich zu Profil und Logout, Administratoren zu den Admin-Bereichen Benutzer und Zitate.

JavaScript ist bewusst auf Transport- und Komfortfunktionen beschränkt. Die Datei `public/assets/js/app.js` sendet per Fetch API DELETE- und PATCH-Anfragen, übergibt CSRF-Token aus dem Meta-Tag und blendet Antwort-Formulare im Thread ein. Validierung, Berechtigungsprüfung und Escaping bleiben vollständig serverseitig, wie von der Projektangabe gefordert.

== Foren- und Thread-Darstellung

Zitate erscheinen im Feed als Karten mit Ausschnitt, Sprecher, optional Thumbnail, Kommentar- und Like-Zähler. Auf der Detailseite zeigt ein Hero-Bild (falls vorhanden) das vollständige Zitat. Kommentare werden rekursiv in `comment-tree.php` gerendert: Avatare links, Einrückung und Verbindungslinien visualisieren die Thread-Struktur. Sortierung ist sowohl im Feed (`?sort=`) als auch bei Kommentaren (`?csort=`) wählbar.

#image(
  
)

= Testfälle

Die Projektangabe verlangt ausführliche Tests der Anwendung, einschließlich fehlerhafter Eingaben. Wir unterscheiden automatisierte REST-Tests und manuelle UI-Tests in Google Chrome unter XAMPP.

== Automatisierter REST-Testlauf

Die REST-Schnittstelle wird mit #link("https://httpyac.github.io/", "httpYac") getestet. Jede `.rest`-Datei beschreibt HTTP-Anfragen mit Assertions (`?? status == 200`, `?? body includes …`). Der Lauf erzeugt einen JUnit-Report.

#table(
  columns: (1.2fr, 2.8fr),
  table.header[*Schritt*][*Befehl bzw. Pfad*],
  [Tests ausführen], [`httpyac send "tests/rest/*.rest" --all -e dev`],
  [Testdateien], [`tests/rest/*`],
  [Voraussetzung], [App unter `http://127.0.0.1:80`, SQL-Dump importiert],
)

Die Dateien folgen einem einheitlichen Namensschema und Fehlerfälle sind darin enthalten, etwa Login mit falschem Passwort, leerer Kommentar, Zugriff auf Admin ohne Rolle und DELETE ohne CSRF.

#let rest-junit-path = "test-results/httpyac-junit.xml"
#let rest-data = parse-httpyac-junit(rest-junit-path)
#pdf.attach(
  rest-junit-path,
  mime-type: "application/xml",
  relationship: "source",
  description: "JUnit-Report",
)

#if rest-data != none [
  Die folgenden Tabellen wurden aus dem JUnit-Report generiert. Spalte *Prüfung* stammt aus der Assertion in der `.rest`-Datei, *Beobachtet* ist der tatsächliche Wert.

  === Übersicht REST-Tests

  #table-httpyac-overview(rest-data)

  === Erwartete und beobachtete Werte

  #table-httpyac-detailed(rest-data)
]

== Manuelle UI-Tests

Zusätzlich zu den automatisierten Tests wurden Szenarien manuell geprüft. Dazu gehören Registrierung und Validierung, CRUD an Kommentaren, Admin-Funktionen, Foren-Interaktionen (Likes, Votes, Sortierung), Bild-Uploads, responsives Layout und Sicherheitsfälle wie XSS-Escaping und IDOR-Schutz.

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

=== Übersicht manuelle Tests

#table-test-results-overview(test-data)

=== Detailergebnisse manuelle Tests

#table-test-results-detailed(test-data)

= Installation und Abgabe

== Voraussetzungen

Die Anwendung ist für eine Standard-XAMPP-Installation unter Windows vorgesehen (Apache, PHP 8.x, MySQL/MariaDB, Google Chrome als Testbrowser). Es ist kein Composer, npm oder Build-Schritt nötig: Das entpackte ZIP genügt zum Betrieb.

== Installationsschritte

+ ZIP-Archiv `WEB4_PHP_TEAM7.zip` entpacken
+ In phpMyAdmin den SQL-Dump `WEB4_PHP_TEAM7.sql` importieren (enthält `CREATE DATABASE`)
+ Apache DocumentRoot auf `public/` setzen
+ Anwendung unter `http://localhost/` aufrufen
+ Optional mit `admin` / `admin` einloggen und Admin-Bereich prüfen

== Datenbankzugang

#table(
  columns: (1.5fr, 2fr),
  table.header[*Parameter*][*Wert*],
  [Datenbankname], [`team_7`],
  [Benutzername], [`fh_webphp`],
  [Passwort], [`fh_webphp`],
  [SQL-Dump], [`WEB4_PHP_TEAM7.sql`],
)

Die Konfiguration liegt in `public/src/config.php` bzw. optional `config.local.php`. Vor der Abgabe wurde geprüft, dass der Dump ohne Fehler importiert werden kann und die Anwendung danach unter der dokumentierten Start-URL erreichbar ist.

== Abgabeformat

Gemäß Projektangabe wird ein ZIP-Archiv im Schema `WEB4_PHP_TEAM7.zip` abgegeben. Enthalten sind der Anwendungscode unter `public/`, die Dokumentation als PDF unter `doc/`, der SQL-Dump und die REST-Testdateien unter `tests/rest/`.
