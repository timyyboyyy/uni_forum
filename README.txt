Uni-Forum Projekt
Eine webbasierte Forumplattform für Hochschulen, entwickelt mit PHP, MySQL und nginx in einer Docker-Umgebung.

Projektbeschreibung
Uni-Forum ist eine moderne Webapplikation, die es Studierenden und Fakultätsmitgliedern ermöglicht, in einem strukturierten Online-Forum zu kommunizieren. Die Plattform bietet Funktionen zum Erstellen von Kategorien, Threads und Beiträgen, sowie Benutzerprofile und Administrationsfunktionen.

Technologie-Stack
Frontend: HTML, CSS, JavaScript

Backend: PHP

Datenbank: MySQL

Webserver: nginx

Containerisierung: Docker und Docker Compose

Versionskontrolle: Git

Projektstruktur
text
uni_forum/
├── api/                      # Backend API
│   ├── config/               # Datenbankonfiguration
│   ├── controllers/          # API Controller
│   ├── models/               # Datenmodelle
│   ├── services/             # Geschäftslogik
│   └── index.php             # API-Eintrittspunkt
├── frontend/                 # Frontend-Komponenten
│   ├── admin/                # Admin-Bereich
│   ├── categories/           # Kategorien-Ansicht
│   │   └── threads/          # Threads-Ansicht
│   ├── create_post/          # Beitrag erstellen
│   ├── latest_posts/         # Neueste Beiträge
│   ├── login/                # Login-Bereich
│   ├── registry/             # Registrierung
│   ├── thread/               # Thread-Ansicht
│   ├── user_profile/         # Benutzerprofile
│   ├── index.html            # Hauptseite
│   ├── script.js             # Hauptjavascript
│   └── style.css             # Hauptstylesheet
├── docker/                   # Docker-Konfiguration
│   ├── mysql/                # MySQL-Container-Konfiguration
│   │   ├── init.sql          # Initialisierungs-SQL
│   │   └── input.sql         # Beispieldaten
│   ├── nginx/                # Nginx-Container-Konfiguration
│   │   └── default.conf      # Nginx-Konfiguration
│   └── php/                  # PHP-Container-Konfiguration
│       └── Dockerfile        # PHP-Dockerfile
├── .env                      # Umgebungsvariablen
├── docker-compose.yml        # Docker-Compose-Konfiguration
└── README.md                 # Diese Dokumentation
Installation und Setup
Voraussetzungen
Docker und Docker Compose installiert

Git (optional für Klonen des Repositories)

Installationsschritte
Repository klonen oder herunterladen:

bash
git clone [repository-url]
cd uni_forum
Umgebungsvariablen konfigurieren:

bash
cp .env.example .env
# Bearbeite die .env-Datei mit deinen eigenen Einstellungen
Container starten:

bash
docker-compose up -d
Das Forum ist nun unter http://localhost erreichbar.

Funktionen
Benutzerregistrierung und -anmeldung: Sicheres Benutzerauthentifizierungssystem

Kategorien und Threads: Hierarchische Organisation von Diskussionen

Beitragserstellung: Einfache Möglichkeit, neue Beiträge zu verfassen

Benutzerprofil: Anpassbare Benutzerprofile

Admin-Bereich: Verwaltungswerkzeuge für Administratoren

Responsive Design: Optimiert für verschiedene Geräte

Entwicklung
Lokale Entwicklungsumgebung
Container mit Entwicklungsoptionen starten:

bash
docker-compose -f docker-compose.yml -f docker-compose.dev.yml up -d
Änderungen werden automatisch erkannt und angewendet.

Codestruktur
Frontend: Modulare Struktur mit separaten JavaScript- und CSS-Dateien für jede Komponente

Backend: API-basierte Architektur mit klarer Trennung von Controllern, Modellen und Services

Datenbank-Schema
Die Datenbank wird bei der ersten Ausführung automatisch initialisiert. Das Schema umfasst Tabellen für Benutzer, Kategorien, Threads und Beiträge.

Beitrag zum Projekt
Fork des Repositories erstellen

Feature-Branch erstellen (git checkout -b feature/deine-feature)

Änderungen committen (git commit -m 'Feature hinzufügen')

Branch pushen (git push origin feature/deine-feature)

Pull Request erstellen