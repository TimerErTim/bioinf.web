#import "deps.typ": *
#import "libs.typ": *
#import "template.typ": documentation-template
#import "visualization/test_results.typ": table-test-results-overview, table-test-results-detailed

#set document(
  author: ("Nathalie Sonnleitner", "Tim Peko"),
  title: "WEB4 PHP — Game of Thrones Quotes",
)
#show: documentation-template.with(
  semester-term: "SS 2026",
  aufwand-in-h: "10",
  author: "Nathalie Sonnleitner, Tim Peko",
  student-id: "2420458029",
)
#pdf.attach(
  "../WEB4_Projekt_Angabe.pdf",
  mime-type: "application/pdf",
  relationship: "source",
  description: "Projektangabe",
)

#show raw.where(lang: "pintora"): it => pintora-diagram(it.text)
#show raw.where(lang: "graphviz"): diagraph.raw-render.with()

= Einleitung

Diese Dokumentation beschreibt die Webanwendung *GoT Quotes* — eine PHP-Anwendung im MVC-Pattern, die berühmte Game-of-Thrones-Zitate präsentiert und es eingeloggten Nutzern ermöglicht, Kommentare zu verfassen, zu bearbeiten und zu löschen. Administratoren verwalten Benutzer und Zitate.

*Start-URL (XAMPP):* `http://localhost/public/` — Document Root muss auf das Verzeichnis `public/` zeigen.

*Start-URL (Entwicklung mit mise):* Apache auf `public/` mit MySQL Port `33060` (siehe `mise.toml`).

= Projektmitglieder

#table(
  columns: (1fr, 1fr),
  table.header[*Name*][*Matrikelnummer*],
  [Nathalie Sonnleitner], [—],
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
- Valides HTML5, CSS-Layout, keine JS-Validierung

== Rollenmodell

#table(
  columns: (2fr, 1fr, 1fr, 1fr),
  table.header[*Aktion*][*Gast*][*User*][*Admin*],
  [Zitate & Kommentare lesen], [✓], [✓], [✓],
  [Kommentar schreiben / eigenes bearbeiten & löschen], [—], [✓], [✓],
  [Fremden Kommentar löschen], [—], [—], [✓],
  [Fremden Kommentar bearbeiten], [—], [—], [✗],
  [Zitate verwalten (CRUD)], [—], [—], [✓],
  [Benutzerverwaltung], [—], [—], [✓],
)

Bei gelöschten Benutzern bleiben Kommentare erhalten; `user_id` wird auf `NULL` gesetzt und in der UI als graues `<deleted>` angezeigt.

= Datenmodell

== ER-Diagramm

```graphviz
digraph ER {
  rankdir=LR;
  node [shape=record, fontname="Roboto"];

  users [label="{users|id (PK)\lusername (UNIQUE)\lpassword_hash\lis_admin\lcreated_at\l}"];
  quotes [label="{quotes|id (PK)\ltext\lspeaker\limage_path\lseason\lepisode\lcreated_at\l}"];
  comments [label="{comments|id (PK)\lquote_id (FK)\luser_id (FK, NULL)\lcontent\lcreated_at\lupdated_at\l}"];

  users -> comments [label="1 : N\nON DELETE SET NULL"];
  quotes -> comments [label="1 : N\nON DELETE CASCADE"];
}
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
  [created_at], [DATETIME], [DEFAULT CURRENT_TIMESTAMP],
)

=== quotes

#table(
  columns: (1fr, 1fr, 2fr),
  table.header[*Spalte*][*Typ*][*Constraints*],
  [id], [INT UNSIGNED], [PK, AUTO_INCREMENT],
  [text], [TEXT], [NOT NULL],
  [speaker], [VARCHAR(100)], [NOT NULL],
  [image_path], [VARCHAR(255)], [NULL — optional, derzeit ungenutzt],
  [season / episode], [TINYINT UNSIGNED], [NULL — optional],
  [created_at], [DATETIME], [DEFAULT CURRENT_TIMESTAMP],
)

=== comments

#table(
  columns: (1fr, 1fr, 2fr),
  table.header[*Spalte*][*Typ*][*Constraints*],
  [id], [INT UNSIGNED], [PK, AUTO_INCREMENT],
  [quote_id], [INT UNSIGNED], [FK → quotes.id, ON DELETE CASCADE],
  [user_id], [INT UNSIGNED], [FK → users.id, NULL, ON DELETE SET NULL],
  [content], [TEXT], [NOT NULL],
  [created_at], [DATETIME], [DEFAULT CURRENT_TIMESTAMP],
  [updated_at], [DATETIME], [NULL ON UPDATE],
)

== Testdaten

SQL-Dump: `sql/WEB4_PHP_TEAM4.sql`

- Admin: `admin` / `admin`
- Testuser: `tyrion_fan`, `arya_fan` — Passwort: `password123`
- 12 Zitate, 15 Kommentare (ohne Bilder)

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
├── index.php              # Front Controller
├── .htaccess              # URL Rewriting
├── assets/css/app.css
├── src/
│   ├── bootstrap.php
│   ├── config.php
│   ├── Router.php
│   ├── Controller/        # Auth, Quote, Comment, Admin/*
│   ├── Model/             # User, Quote, Comment
│   └── Service/           # AuthService, ValidationService
└── views/                 # PHP HTML-Templates
```

== Request-Flow (Beispiel: Kommentar erstellen)

1. `POST /quotes/{id}/comments` → Router
2. `CommentController::store` — CSRF prüfen, Login erzwingen
3. `ValidationService::commentContent` — serverseitige Validierung
4. `Comment::create` — Prepared Statement mit Session-`user_id`
5. Redirect zur Zitat-Detailseite mit Flash-Message

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
- Optional: mise für lokale Entwicklung

== Schritte

+ ZIP entpacken
+ In phpMyAdmin `sql/WEB4_PHP_TEAM4.sql` importieren
+ Apache DocumentRoot auf `public/` setzen
+ Anwendung unter `http://localhost/` aufrufen
+ Mit `admin` / `admin` einloggen

== Entwicklung mit mise

```bash
mise run run:db          # MySQL starten (Port 33060)
mise run build:documentation
mise run package         # Abgabe-ZIP erzeugen
```

Datenbank-Credentials: siehe `mise.toml` — DB `team_4`, User `fh_webphp`.
