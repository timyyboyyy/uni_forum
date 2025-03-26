# Forum Projekt

Eine webbasierte Forumplattform, entwickelt mit **PHP**, **MySQL** und **nginx** in einer **Docker-Umgebung**.

## 📖 Projektbeschreibung

**Forum** ist eine moderne Webapplikation, die ermöglicht, in einem strukturierten Online-Forum zu kommunizieren. Die Plattform bietet:

- Erstellen von Kategorien, Threads und Beiträgen
- Benutzerprofile mit individuellen Anpassungsmöglichkeiten
- Administrationsfunktionen für Moderation und Verwaltung

## 🛠 Technologie-Stack

- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP
- **Datenbank:** MySQL
- **Webserver:** nginx
- **Containerisierung:** Docker & Docker Compose
- **Versionskontrolle:** Git

## 📂 Projektstruktur

```plaintext
forum/
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
│   ├── script.js             # Haupt-JavaScript
│   └── style.css             # Haupt-Stylesheet
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
```

## 🚀 Installation und Setup

### 🔧 Voraussetzungen

- **Docker** und **Docker Compose** installiert
- **Git** (optional für das Klonen des Repositories)

### 📥 Installationsschritte

1. **Repository klonen oder herunterladen:**
   ```bash
   git clone [repository-url]
   cd uni_forum
   ```

2. **Umgebungsvariablen konfigurieren:**
   WICHTIG Die aktuelle .env-Datei wurde auf die Parameter der hier initialisierten MySQL Datenbank eingestellt und muss somit nicht verändert werden
   ```bash
   cp .env.example .env
   # Bearbeite die .env-Datei mit deinen eigenen Einstellungen

   ```

3. **Container starten:**
   ```bash
   docker-compose up -d
   ```
   Das Forum ist nun unter **http://localhost** erreichbar.

## 🔥 Funktionen

✅ **Benutzerregistrierung und -anmeldung:** Sicheres Authentifizierungssystem  
✅ **Kategorien und Threads:** Strukturierte Diskussionsorganisation  
✅ **Beitragserstellung:** Einfache Erstellung neuer Inhalte  
✅ **Benutzerprofile:** Anpassbare Profile  
✅ **Admin-Bereich:** Verwaltungswerkzeuge für Moderatoren  
✅ **Responsive Design:** Optimiert für verschiedene Geräte  

## ⚙ Entwicklung

### Codestruktur

- **Frontend:** Modulare Struktur mit separaten JavaScript- und CSS-Dateien für jede Komponente
- **Backend:** API-basierte Architektur mit klarer Trennung von Controllern, Modellen und Services

## 📊 Datenbank-Schema

Die Datenbank wird bei der ersten Ausführung automatisch initialisiert. Das Schema umfasst Tabellen für **Benutzer, Kategorien, Threads und Beiträge**.

---

📌 **Lizenz**: Dieses Projekt steht unter der [MIT-Lizenz](LICENSE.md).