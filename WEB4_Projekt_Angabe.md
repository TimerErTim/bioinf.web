# UE

## Übung zu WEB4

- **Abgabetermin:** bis 23.06.2026, 23:59 Uhr, per Moodle 
- **Präsentation:** 24.06.2026 (obsolet, stattdessen nur Abgabe)
- **Projekt:** PHP-Projekt 
- **Semester:** SS 2026 
- **Punkte:** (100 Punkte) 

Erstellen Sie eine Webanwendung, die grundlegende Funktionen wie Benutzerverwaltung und Datenbankzugriffe implementiert. Die Anwendung muss dabei grundsätzlich dem MVC-Pattern folgen und für zumindest eine Ressource alle CRUD Operationen unterstützen.

### Anforderungen

- Benutzer können sich einloggen und je nach Rolle unterschiedlich mit der Anwendung interagieren.
- Es soll unterschiedliche Funktionen für anonyme (nicht eingeloggte) und aktive (eingeloggte) User geben.
- Anonyme User können sich für die Anwendung durch Vergabe von Benutzernamen und Passwort neu registrieren. Dazu soll eine eigene Registrierungsseite vorhanden sein.
- Zudem soll es Administratoren geben, welche Zugriff auf eine Benutzerverwaltung haben, um andere Benutzer löschen oder deren Rolle (Admin ja/nein) ändern zu können.



### Programmierung Serverseitig / Backend

- Umsetzung mittels PHP >= 8.x achten Sie auf einen sauberen Aufbau und eine Trennung der einzelnen Anwendungsschichten.
- Für die Anbindung der Datenquelle benutzen Sie die PDO-Bibliothek.
- Sichern Sie Ihre Anwendung gegen SQL-Injection und XSS-Angriffe durch serverseitige Prüfung der Eingaben und Parameter. Jeglicher textuelle Input, der von Usern kommt muss validiert werden.
- **Hinweis:** Externe Bibliotheken sind erlaubt, jedoch muss das Projekt für sich selbst stehen - heißt copy/paste auf einen Webserver muss ausreichen, ohne der Notwendigkeit eines Dependencymanagers wie Composer.

### Datenbank

Die Datenverwaltung erfolgt mittels MySQL oder MariaDB. Achten Sie auf die Grundregeln des Datenbank-Designs (Datentypen, Normalisierung, sinnvolle Constraints, etc.).

### Client / Frontend

- Die Verwendung von JavaScript ist erlaubt jedoch nicht für Validierungszwecke oder ähnliches.
- Der HTML-Quelltext sollte valides HTML5 sein und sämtliche Formatierungen sollten mit CSS umgesetzt werden.
- Die Benutzeroberfläche sollte intuitiv bedienbar sein und bei neuen Nutzern der Anwendung keinerlei Erklärung erfordern. Achten Sie insbesondere auf Validierung von Eingaben sowie die Fehlerausgabe.
- **Hinweis:** achten Sie auf funktionales Design - wichtig ist die Bedienbarkeit und die logische Führung der Benutzer durch die Website (Navigationselemente, Feedback bei Benutzeraktionen, Formular-Validierungen & -Layout, etc.).
- Aus Gründen der Effizienz wird die Verwendung eines Frontend-Frameworks wie bspw. Twitter Bootstrap o.Ä. empfohlen. Die Verwendung von Javascript Frameworks ist erlaubt, jedoch auch hier self-contained ohne Notwendigkeit eines Dependencymanagers wie npm.

---

### Form der Abgabe

#### Dokumentation

- Erstellen Sie für Ihr Datenbank-Modell ein ER- oder UML-Diagramm (keine autogenerierten), das die Entitäten und Beziehungen visualisiert. Sie können zum Beispiel [http://draw.io](http://draw.io) verwenden um ein ER-/UML-Diagramm zu erstellen.
- Für die Anwendung erstellen Sie bitte eine textuelle Beschreibung und / oder eine dokumentierte Skizze der Architektur.
- Dokumentieren Sie auch ihre Start-URL um auf Ihre Applikation zugreifen zu können! 
- Nennen Sie in der Dokumentation alle Projektmitglieder.
- Erstellen Sie für Dokumentation und Testfälle ein PDF-Dokument! 

#### Testfälle

Führen Sie ausführliche Tests Ihrer Anwendung durch und dokumentieren Sie diese. Testen Sie auch fehlerhafte Eingaben! 

#### Anwendung

**Datenbank:**

- SQL-Dump inkl. Testdaten und CREATE DATABASE Statement (über phpmyadmin Export).
- Datenbankname: `team_<team_nr>` 
- Benutzername: `fh_webphp` 
- Passwort: `fh_webphp` 
- Die Datenbank muss sich ohne Probleme einspielen lassen können (vergewissern Sie sich, dass dies auch funktioniert).

**Code:**

- Zip-Archiv, Startdokument `index.php`, muss auf Standard-XAMPP Installation lauffähig sein.
- Die Dokumentation speichern Sie bitte im Unterverzeichnis „/doc".
- Geben Sie Ihre Anwendung als ein ZIP-Archiv ab. Benennen Sie dieses Archiv im nachfolgenden Schema: `WEB4_PHP_TEAM<team_nr>.zip`.
- Laden Sie Ihre Anwendung als eine Datei im ZIP-Format vor dem definierten Abgabetermin via [https://hagenberg.elearning.fh-ooe.at](https://hagenberg.elearning.fh-ooe.at) bei der betreffenden Kursabgabe hoch. Eine Abgabe pro Termin ist ausreichend! 


### Beurteilung

- Die Beurteilung der Übung erfolgt durch Tutor-innen, die die eingereichten Übungen korrigieren und bewerten.
- Achten Sie auf saubere Dokumentation des Codes sowie auf fehlerfreie Ausführbarkeit des abgegebenen Paketes (Pfade, Datenbank, PHP-Version).
- Bei Wiederverwendung von Teilen der Übung, vergessen Sie nicht, die ungenutzten Teile restlos zu entfernen!.
- Die Anwendung wird unter XAMPP (Windows) und Google Chrome getestet.