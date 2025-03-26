document.addEventListener('DOMContentLoaded', function() {
    loadLatestPosts();
    checkLoginStatus();
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

function loadLatestPosts() {
    // Tabellenkörper auswählen
    const tableBody = document.querySelector('.latest-posts-table tbody');
    
    // Ladeindikator anzeigen
    if (tableBody) {
        tableBody.innerHTML = '<tr><td colspan="4">Beiträge werden geladen...</td></tr>';
    }
    
    // Daten von der API abrufen
    fetch('/api/latest_posts')
        .then(response => {
            if (!response.ok) {
                throw new Error('Netzwerkantwort war nicht ok');
            }
            return response.json();
        })
        .then(data => {
            displayLatestPosts(data);
        })
        .catch(error => {
            console.error('Fehler beim Laden der neuesten Beiträge:', error);
            if (tableBody) {
                tableBody.innerHTML = '<tr><td colspan="4">Fehler beim Laden der Daten. Bitte versuchen Sie es später erneut.</td></tr>';
            }
        });
}

function displayLatestPosts(posts) {
    const tableBody = document.querySelector('.latest-posts-table tbody');
    
    if (tableBody) {
        tableBody.innerHTML = '';
        
        if (posts && posts.length > 0) {
            posts.forEach(post => {
                const row = document.createElement('tr');
                
                // Titel mit Link zum Thread - WICHTIG: Link im Format /thread/?id=X
                const titleCell = document.createElement('td');
                const titleLink = document.createElement('a');
                titleLink.href = `/thread/?id=${post.id}`;  // Wichtig: Slash vor Fragezeichen
                titleLink.textContent = post.title;
                titleCell.appendChild(titleLink);
                row.appendChild(titleCell);
                
                // Kategorie
                const categoryCell = document.createElement('td');
                categoryCell.textContent = post.category;
                row.appendChild(categoryCell);
                
                // Autor
                const authorCell = document.createElement('td');
                authorCell.textContent = post.author;
                row.appendChild(authorCell);
                
                // Datum
                const dateCell = document.createElement('td');
                dateCell.textContent = post.date;
                row.appendChild(dateCell);
                
                tableBody.appendChild(row);
            });
        } else {
            tableBody.innerHTML = '<tr><td colspan="4">Keine Beiträge gefunden.</td></tr>';
        }
    }
}
