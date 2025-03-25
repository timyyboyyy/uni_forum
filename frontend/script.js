// Am Anfang der script.js, direkt nach dem DOMContentLoaded Event
document.addEventListener('DOMContentLoaded', function() {
    loadCategories();
    checkLoginStatus(); // Neue Funktion aufrufen
});

// Neue Funktion zum Prüfen des Login-Status
function checkLoginStatus() {
    fetch('/api/check-auth', {
        method: 'GET',
        credentials: 'include' // Wichtig für Session-Cookies
    })
    .then(response => response.json())
    .then(data => {
        updateNavigation(data);
    })
    .catch(error => {
        console.error('Fehler beim Prüfen des Login-Status:', error);
    });
}

// Funktion zum Aktualisieren der Navigation
function updateNavigation(userData) {
    const navElement = document.querySelector('nav');
    if (!navElement) return;
    
    // Get current path (e.g., "/categories/")
    const currentPath = window.location.pathname;
    
    if (userData.loggedIn) {
        let adminMenuHtml = '';
        if (userData.isAdmin) {
            adminMenuHtml = '<a href="/admin/">Admin-Bereich</a>';
        }
        
        navElement.innerHTML = `
            <a href="/" class="${currentPath === '/' ? 'active' : ''}">Startseite</a>
            <a href="/categories/" class="${currentPath.startsWith('/categories') ? 'active' : ''}">Kategorien</a>
            <a href="/latest_posts/" class="${currentPath.startsWith('/latest_posts') ? 'active' : ''}">Neueste Beiträge</a>
            <a href="/create_post/" class="${currentPath.startsWith('/create_post') ? 'active' : ''}">Beitrag erstellen</a>
            ${adminMenuHtml}
            <div class="dropdown">
                <a href="#" class="dropdown-toggle">${userData.username}</a>
                <div class="dropdown-menu">
                    <a href="/user_profile/">Profileinstellungen</a>
                    <div class="dropdown-divider"></div>
                    <a href="#" id="logout-link">Ausloggen</a>
                </div>
            </div>
        `;
        
        // Dropdown-Funktionalität hinzufügen
        const dropdownToggle = document.querySelector('.dropdown-toggle');
        const dropdownMenu = document.querySelector('.dropdown-menu');
        
        dropdownToggle.addEventListener('click', function(e) {
            e.preventDefault();
            dropdownMenu.classList.toggle('show');
        });
        
        // Klick außerhalb des Dropdown-Menüs schließt es
        document.addEventListener('click', function(event) {
            if (!event.target.matches('.dropdown-toggle') && !event.target.closest('.dropdown-menu')) {
                dropdownMenu.classList.remove('show');
            }
        });
        
        // Logout-Funktion hinzufügen
        document.getElementById('logout-link').addEventListener('click', function(e) {
            e.preventDefault();
            fetch('/api/logout', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'}
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            });
        });
    } else {
        // Benutzer ist nicht eingeloggt - zeige Login/Register
        navElement.innerHTML = `
            <a href="/" class="${currentPath === '/' ? 'active' : ''}">Startseite</a>
            <a href="/categories/" class="${currentPath.startsWith('/categories') ? 'active' : ''}">Kategorien</a>
            <a href="/latest_posts/" class="${currentPath.startsWith('/latest_posts') ? 'active' : ''}">Neueste Beiträge</a>
            <a href="/login/" class="${currentPath.startsWith('/login') ? 'active' : ''}">Login</a>
            <a href="/registry/" class="${currentPath.startsWith('/registry') ? 'active' : ''}">Registrieren</a>
        `;
    }
}



// Funktion zum Laden der Kategoriedaten
function loadCategories() {
    // Anzeigen eines Ladeindikators
    const tableBody = document.querySelector('table tbody');
    if (tableBody) {
        tableBody.innerHTML = '<tr><td colspan="3">Kategorien werden geladen...</td></tr>';
        
        // API-Anfrage für Top-Kategorien statt aller Kategorien
        fetch('/api/top_categories')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Netzwerkantwort war nicht ok');
                }
                return response.json();
            })
            .then(data => {
                displayCategories(data, tableBody);
            })
            .catch(error => {
                console.error('Fehler beim Laden der Kategorien:', error);
                tableBody.innerHTML = '<tr><td colspan="3">Fehler beim Laden der Daten.</td></tr>';
            });
    }
}


// Funktion zum Anzeigen der Kategorien in der Tabelle
function displayCategories(categories, tableBody) {
    // Tabelle leeren
    tableBody.innerHTML = '';
    
    // Kategorien in die Tabelle einfügen
    categories.forEach(category => {
        const row = document.createElement('tr');
        
        // Kategorie-Name mit Link
        const categoryCell = document.createElement('td');
        const categoryLink = document.createElement('a');
        categoryLink.href = `/categories/threads/?id=${category.id}&name=${encodeURIComponent(category.name)}`;
        categoryLink.textContent = category.name;
        categoryCell.appendChild(categoryLink);
        
        // Anzahl der Themen
        const topicCountCell = document.createElement('td');
        topicCountCell.textContent = category.topic_count;
        
        // Letzter Beitrag - HTML direkt einsetzen
        const lastPostCell = document.createElement('td');
        lastPostCell.innerHTML = category.last_post;
        
        // Zellen zur Zeile hinzufügen
        row.appendChild(categoryCell);
        row.appendChild(topicCountCell);
        row.appendChild(lastPostCell);
        
        // Zeile zur Tabelle hinzufügen
        tableBody.appendChild(row);
    });
}


