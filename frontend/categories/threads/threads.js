document.addEventListener('DOMContentLoaded', function() {
  
    // Kategorie-ID aus der URL extrahieren
    const urlParams = new URLSearchParams(window.location.search);
    const categoryId = urlParams.get('id');
    const categoryName = urlParams.get('name');
    
    if (categoryName) {
        document.getElementById('category-name').textContent = categoryName;
        document.title = `${categoryName} - Forum`;
    }
    
    if (!categoryId) {
        document.querySelector('.threads-table tbody').innerHTML = 
            '<tr><td colspan="4">Fehler: Keine Kategorie-ID gefunden</td></tr>';
        return;
    }
    
    loadThreads(categoryId);
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
    
    if (userData.loggedIn) {
        let adminMenuHtml = '';
        if (userData.isAdmin) {
            adminMenuHtml = '<a href="/admin/">Admin-Bereich</a>';
        }
        
        navElement.innerHTML = `
            <a href="/">Startseite</a>
            <a href="/categories/">Kategorien</a>
            <a href="/latest_posts/">Neueste Beiträge</a>
            <a href="/create_post/">Beitrag erstellen</a>
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
            <a href="/">Startseite</a>
            <a href="/categories/">Kategorien</a>
            <a href="/latest_posts/">Neueste Beiträge</a>
            <a href="/login/">Login</a>
            <a href="/registry/">Registrieren</a>
        `;
    }
}

function loadThreads(categoryId) {
    // Tabellenkörper auswählen
    const tableBody = document.querySelector('.threads-table tbody');
    
    // Ladeindikator anzeigen
    if (tableBody) {
        tableBody.innerHTML = '<tr><td colspan="4">Themen werden geladen...</td></tr>';
    }
    
    // Daten von der API abrufen - WICHTIG: Korrekter API-Endpoint
    fetch(`/api/category_threads?id=${categoryId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Netzwerkantwort war nicht ok');
            }
            return response.json();
        })
        .then(data => {
            displayThreads(data);
        })
        .catch(error => {
            console.error('Fehler beim Laden der Themen:', error);
            if (tableBody) {
                tableBody.innerHTML = '<tr><td colspan="4">Fehler beim Laden der Daten. Bitte versuchen Sie es später erneut.</td></tr>';
            }
        });
}

function displayThreads(threads) {
    const tableBody = document.querySelector('.threads-table tbody');
    
    if (tableBody) {
        tableBody.innerHTML = '';
        
        if (threads && threads.length > 0) {
            threads.forEach(thread => {
                const row = document.createElement('tr');
                
                // Titel mit Link
                const titleCell = document.createElement('td');
                const titleLink = document.createElement('a');
                titleLink.href = `/thread/?id=${thread.id}`;
                titleLink.textContent = thread.title;
                titleCell.appendChild(titleLink);
                row.appendChild(titleCell);
                
                // Autor
                const authorCell = document.createElement('td');
                authorCell.textContent = thread.author;
                row.appendChild(authorCell);
                
                // Datum
                const dateCell = document.createElement('td');
                dateCell.textContent = thread.date;
                row.appendChild(dateCell);
                
                // Antworten
                const replyCountCell = document.createElement('td');
                replyCountCell.textContent = thread.reply_count;
                row.appendChild(replyCountCell);
                
                tableBody.appendChild(row);
            });
        } else {
            tableBody.innerHTML = '<tr><td colspan="4">Keine Themen in dieser Kategorie gefunden.</td></tr>';
        }
    }

    const categoriesLink = document.querySelector('nav a[href="/categories/"]');
    if (categoriesLink) {
        categoriesLink.classList.add('active');
    }
}
