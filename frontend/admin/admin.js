document.addEventListener('DOMContentLoaded', function() {
    // Admin-Rechte prüfen
    checkAdminAccess();
    
    // Tab-Wechsel
    setupTabs();
    
    // Modal-Close-Buttons
    setupModals();
    
    // Daten für das Dashboard laden
    loadDashboardData();

    // Login Status für die Nav Leiste
    checkLoginStatus();
    
    // Event-Listener für Suchfunktionen
    document.getElementById('user-search-btn').addEventListener('click', searchUsers);
    document.getElementById('thread-search-btn').addEventListener('click', searchThreads);
    document.getElementById('post-search-btn').addEventListener('click', searchPosts);
    
    // Event-Listener für Kategorie hinzufügen
    document.getElementById('add-category-btn').addEventListener('click', showAddCategoryModal);
    
    // Event-Listener für Modal-Formulare
    document.getElementById('edit-user-form').addEventListener('submit', saveUserEdit);
    document.getElementById('edit-category-form').addEventListener('submit', saveCategoryEdit);
    // Lösch-Bestätigungen
    document.getElementById('confirm-delete-btn').addEventListener('click', executeDelete);
    document.getElementById('cancel-delete-btn').addEventListener('click', closeDeleteModal);
    
    // Logout-Funktion
    document.getElementById('logout-link').addEventListener('click', function(e) {
        e.preventDefault();
        fetch('/api/logout', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'}
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '/login/';
            }
        });
    });
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
        
        
        navElement.innerHTML = `
            <a href="/" class="${currentPath === '/' ? 'active' : ''}">Startseite</a>
            <a href="/categories/" class="${currentPath.startsWith('/categories') ? 'active' : ''}">Kategorien</a>
            <a href="/latest_posts/" class="${currentPath.startsWith('/latest_posts') ? 'active' : ''}">Neueste Beiträge</a>
            <a href="/create_post/" class="${currentPath.startsWith('/create_post') ? 'active' : ''}">Beitrag erstellen</a>
            <a href="/admin/" class="${currentPath.startsWith('/admin') ? 'active' : ''}">Admin-Bereich</a>
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

// Prüft, ob der Benutzer Admin-Rechte hat
function checkAdminAccess() {
    fetch('/api/check-auth')
        .then(response => response.json())
        .then(data => {
            if (!data.loggedIn || !data.isAdmin) {
                window.location.href = '/login/';
                return;
            }
            
            // Benutzernamen anzeigen
            document.getElementById('username-display').textContent = data.username;
            
            // Daten für die verschiedenen Tabs laden
            loadUsersData();
            loadCategoriesData();
            loadThreadsData();
            loadPostsData();
        })
        .catch(error => {
            console.error('Fehler beim Prüfen der Admin-Rechte:', error);
            window.location.href = '/login/';
        });
}

// Tab-Navigation einrichten
function setupTabs() {
    const tabLinks = document.querySelectorAll('.admin-menu a');
    
    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Aktiven Tab-Link hervorheben
            tabLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            
            // Alle Tab-Inhalte ausblenden
            document.querySelectorAll('.admin-tab').forEach(tab => {
                tab.style.display = 'none';
            });
            
            // Ausgewählten Tab-Inhalt anzeigen
            const tabId = this.getAttribute('data-tab');
            document.getElementById(`${tabId}-tab`).style.display = 'block';
        });
    });
}

// Modal-Funktionen einrichten
function setupModals() {
    // Close-Buttons für alle Modals
    document.querySelectorAll('.close').forEach(closeBtn => {
        closeBtn.addEventListener('click', function() {
            this.closest('.modal').style.display = 'none';
        });
    });
    
    // Modals schließen bei Klick außerhalb
    window.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    });
}

// Dashboard-Daten laden
function loadDashboardData() {
    // API für Dashboard-Daten aufrufen
    fetch('/api/admin/dashboard')
        .then(response => {
            if (!response.ok) {
                // Fallback, falls API noch nicht implementiert
                return {
                    userCount: 15,
                    categoryCount: 5,
                    threadCount: 48,
                    postCount: 176,
                    recentActivity: [
                        { action: 'Neuer Thread', user: 'MaxMuster', date: '18.03.2025, 15:30', details: 'Wie installiere ich PHP?' },
                        { action: 'Neue Antwort', user: 'CodingGuru', date: '18.03.2025, 14:45', details: 'Re: MySQL-Verbindungsprobleme' },
                        { action: 'Kategorie erstellt', user: 'Admin', date: '17.03.2025, 09:12', details: 'JavaScript-Tipps' },
                        { action: 'Benutzer registriert', user: 'NewUser123', date: '16.03.2025, 18:30', details: '' }
                    ]
                };
            }
            return response.json();
        })
        .then(data => {
            // Statistiken anzeigen
            document.getElementById('user-count').textContent = data.userCount;
            document.getElementById('category-count').textContent = data.categoryCount;
            document.getElementById('thread-count').textContent = data.threadCount;
            document.getElementById('post-count').textContent = data.postCount;
            
            // Aktivitätsliste füllen
            const activityList = document.getElementById('activity-list');
            activityList.innerHTML = '';
            
            data.recentActivity.forEach(activity => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${activity.action}</td>
                    <td>${activity.user}</td>
                    <td>${activity.date}</td>
                    <td>${activity.details}</td>
                `;
                activityList.appendChild(row);
            });
        })
        .catch(error => {
            console.error('Fehler beim Laden der Dashboard-Daten:', error);
            // Fallback-Werte anzeigen
            document.getElementById('user-count').textContent = '?';
            document.getElementById('category-count').textContent = '?';
            document.getElementById('thread-count').textContent = '?';
            document.getElementById('post-count').textContent = '?';
        });
}

// Benutzerdaten laden
function loadUsersData() {
    fetch('/api/admin/users')
        .then(response => {
            if (!response.ok) {
                // Fallback-Daten
                return [
                    { id: 1, username: 'admin', email: 'admin@example.com', role: 'Admin', created_at: '01.01.2025' },
                    { id: 2, username: 'user1', email: 'user1@example.com', role: 'User', created_at: '05.01.2025' },
                    { id: 3, username: 'user2', email: 'user2@example.com', role: 'User', created_at: '10.02.2025' }
                ];
            }
            return response.json();
        })
        .then(users => {
            const usersList = document.getElementById('users-list');
            usersList.innerHTML = '';
            
            users.forEach(user => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${user.id}</td>
                    <td>${user.username}</td>
                    <td>${user.email}</td>
                    <td>${user.role}</td>
                    <td>${user.created_at}</td>
                    <td class="action-buttons">
                        <button class="edit-btn" data-id="${user.id}" onclick="editUser(${user.id})">Bearbeiten</button>
                        <button class="delete-btn" data-id="${user.id}" onclick="confirmDeleteUser(${user.id})">Löschen</button>
                    </td>
                `;
                usersList.appendChild(row);
            });
        })
        .catch(error => {
            console.error('Fehler beim Laden der Benutzerdaten:', error);
        });
}

// Kategoriedaten laden
function loadCategoriesData() {
    fetch('/api/admin/categories')
        .then(response => {
            if (!response.ok) {
                // Fallback-Daten
                return [
                    { id: 1, name: 'Allgemein', description: 'Allgemeine Diskussionen', threads: 15, created_at: '01.01.2025' },
                    { id: 2, name: 'Hilfe', description: 'Hilfeforum', threads: 20, created_at: '01.01.2025' },
                    { id: 3, name: 'Feedback', description: 'Feedback zum Forum', threads: 5, created_at: '01.01.2025' }
                ];
            }
            return response.json();
        })
        .then(categories => {
            const categoriesList = document.getElementById('categories-list');
            categoriesList.innerHTML = '';
            
            categories.forEach(category => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${category.id}</td>
                    <td>${category.name}</td>
                    <td>${category.description}</td>
                    <td>${category.threads}</td>
                    <td>${category.created_at}</td>
                    <td class="action-buttons">
                        <button class="edit-btn" onclick="editCategory(${category.id})">Bearbeiten</button>
                        <button class="delete-btn" onclick="confirmDeleteCategory(${category.id})">Löschen</button>
                    </td>
                `;
                categoriesList.appendChild(row);
            });
        })
        .catch(error => {
            console.error('Fehler beim Laden der Kategoriedaten:', error);
        });
}

// Thread-Daten laden
function loadThreadsData() {
    fetch('/api/admin/threads')
        .then(response => {
            if (!response.ok) {
                // Fallback-Daten
                return [
                    { id: 1, title: 'Willkommen im Forum', author: 'admin', category: 'Allgemein', created_at: '01.01.2025' },
                    { id: 2, title: 'Hilfe bei PHP', author: 'user1', category: 'Hilfe', created_at: '05.01.2025' },
                    { id: 3, title: 'Feedback zur Seite', author: 'user2', category: 'Feedback', created_at: '10.02.2025' }
                ];
            }
            return response.json();
        })
        .then(threads => {
            const threadsList = document.getElementById('threads-list');
            threadsList.innerHTML = '';
            
            threads.forEach(thread => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${thread.id}</td>
                    <td>${thread.title}</td>
                    <td>${thread.author}</td>
                    <td>${thread.category}</td>
                    <td>${thread.created_at}</td>
                    <td class="action-buttons">
                        <button class="delete-btn" onclick="confirmDeleteThread(${thread.id})">Löschen</button>
                    </td>
                `;
                threadsList.appendChild(row);
            });
        })
        .catch(error => {
            console.error('Fehler beim Laden der Thread-Daten:', error);
        });
}

// Beitragsdaten laden
function loadPostsData() {
    fetch('/api/admin/posts')
        .then(response => {
            if (!response.ok) {
                // Fallback-Daten
                return [
                    { id: 1, content: 'Willkommen im Forum!', author: 'admin', thread: 'Willkommen im Forum', created_at: '01.01.2025' },
                    { id: 2, content: 'Ich brauche Hilfe mit PHP...', author: 'user1', thread: 'Hilfe bei PHP', created_at: '05.01.2025' },
                    { id: 3, content: 'Das Forum gefällt mir gut!', author: 'user2', thread: 'Feedback zur Seite', created_at: '10.02.2025' }
                ];
            }
            return response.json();
        })
        .then(posts => {
            const postsList = document.getElementById('posts-list');
            postsList.innerHTML = '';
            
            posts.forEach(post => {
                const row = document.createElement('tr');
                // Inhalt auf 50 Zeichen kürzen
                const shortContent = post.content.length > 50 ? post.content.substring(0, 50) + '...' : post.content;
                
                row.innerHTML = `
                    <td>${post.id}</td>
                    <td>${shortContent}</td>
                    <td>${post.author}</td>
                    <td>${post.thread}</td>
                    <td>${post.created_at}</td>
                    <td class="action-buttons">
                        <button class="delete-btn" onclick="confirmDeletePost(${post.id})">Löschen</button>
                    </td>
                `;
                postsList.appendChild(row);
            });
        })
        .catch(error => {
            console.error('Fehler beim Laden der Beitragsdaten:', error);
        });
}

// Benutzersuche
function searchUsers() {
    const searchTerm = document.getElementById('user-search').value.toLowerCase();
    const rows = document.querySelectorAll('#users-list tr');
    
    rows.forEach(row => {
        const username = row.children[1].textContent.toLowerCase();
        const email = row.children[2].textContent.toLowerCase();
        
        if (username.includes(searchTerm) || email.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Thread-Suche
function searchThreads() {
    const searchTerm = document.getElementById('thread-search').value.toLowerCase();
    const rows = document.querySelectorAll('#threads-list tr');
    
    rows.forEach(row => {
        const title = row.children[1].textContent.toLowerCase();
        const author = row.children[2].textContent.toLowerCase();
        const category = row.children[3].textContent.toLowerCase();
        
        if (title.includes(searchTerm) || author.includes(searchTerm) || category.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Beitragssuche
function searchPosts() {
    const searchTerm = document.getElementById('post-search').value.toLowerCase();
    const rows = document.querySelectorAll('#posts-list tr');
    
    rows.forEach(row => {
        const content = row.children[1].textContent.toLowerCase();
        const author = row.children[2].textContent.toLowerCase();
        const thread = row.children[3].textContent.toLowerCase();
        
        if (content.includes(searchTerm) || author.includes(searchTerm) || thread.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Benutzer bearbeiten
function editUser(userId) {
    // API-Anfrage für Benutzerdaten
    fetch(`/api/admin/users/${userId}`)
        .then(response => {
            if (!response.ok) {
                // Fallback für Testdaten
                return { 
                    id: userId, 
                    username: 'Benutzer ' + userId, 
                    email: `user${userId}@example.com`, 
                    role_id: userId === 1 ? 1 : 2 
                };
            }
            return response.json();
        })
        .then(user => {
            // Modal-Felder befüllen
            document.getElementById('edit-user-id').value = user.id;
            document.getElementById('edit-username').value = user.username;
            document.getElementById('edit-email').value = user.email;
            document.getElementById('edit-role').value = user.role_id;
            
            // Modal anzeigen
            document.getElementById('edit-user-modal').style.display = 'block';
        })
        .catch(error => {
            console.error('Fehler beim Laden der Benutzerdaten:', error);
        });
}

// Kategorie bearbeiten
function editCategory(categoryId) {
    // API-Anfrage für Kategoriedaten
    fetch(`/api/admin/category/${categoryId}`)
        .then(response => {
            if (!response.ok) {
                // Fallback für Testdaten
                return { 
                    id: categoryId, 
                    name: 'Kategorie ' + categoryId, 
                    description: 'Beschreibung der Kategorie ' + categoryId 
                };
            }
            return response.json();
        })
        .then(category => {
            // Modal-Felder befüllen
            document.getElementById('edit-category-id').value = category.id;
            document.getElementById('edit-category-name').value = category.name;
            document.getElementById('edit-category-description').value = category.description;
            
            // Modal anzeigen
            document.getElementById('edit-category-modal').style.display = 'block';
        })
        .catch(error => {
            console.error('Fehler beim Laden der Kategoriedaten:', error);
        });
}

// Kategorie hinzufügen
function showAddCategoryModal() {
    // Modal-Felder leeren
    document.getElementById('edit-category-id').value = '';
    document.getElementById('edit-category-name').value = '';
    document.getElementById('edit-category-description').value = '';
    
    // Modal-Titel ändern
    document.querySelector('#edit-category-modal h3').textContent = 'Neue Kategorie erstellen';
    
    // Modal anzeigen
    document.getElementById('edit-category-modal').style.display = 'block';
}



// Benutzer-Bearbeitung speichern
function saveUserEdit(e) {
    e.preventDefault();
    
    const userId = document.getElementById('edit-user-id').value;
    const username = document.getElementById('edit-username').value;
    const email = document.getElementById('edit-email').value;
    const roleId = document.getElementById('edit-role').value;
    
    fetch(`/api/admin/user/${userId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            username: username,
            email: email,
            role_id: roleId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Modal schließen
            document.getElementById('edit-user-modal').style.display = 'none';
            // Benutzerdaten neu laden
            loadUsersData();
        } else {
            alert('Fehler beim Speichern: ' + (data.message || 'Unbekannter Fehler'));
        }
    })
    .catch(error => {
        console.error('Fehler beim Speichern:', error);
        alert('Fehler beim Speichern der Änderungen');
    });
}

// Kategorie-Bearbeitung speichern
function saveCategoryEdit(e) {
    e.preventDefault();
    
    const categoryId = document.getElementById('edit-category-id').value;
    const name = document.getElementById('edit-category-name').value;
    const description = document.getElementById('edit-category-description').value;
    
    const isNewCategory = categoryId === '';
    const url = isNewCategory ? '/api/admin/categories' : `/api/admin/category/${categoryId}`;
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            name: name,
            description: description
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Modal schließen
            document.getElementById('edit-category-modal').style.display = 'none';
            // Kategoriedaten neu laden
            loadCategoriesData();
        } else {
            alert('Fehler beim Speichern: ' + (data.message || 'Unbekannter Fehler'));
        }
    })
    .catch(error => {
        console.error('Fehler beim Speichern:', error);
        alert('Fehler beim Speichern der Änderungen');
    });
}


// Löschbestätigungsfunktionen
let currentDeleteAction = null;
let currentDeleteId = null;

function confirmDeleteUser(userId) {
    document.getElementById('confirm-delete-message').textContent = `Möchtest du den Benutzer mit ID ${userId} wirklich löschen?`;
    document.getElementById('confirm-delete-modal').style.display = 'block';
    currentDeleteAction = 'user';
    currentDeleteId = userId;
}

function confirmDeleteCategory(categoryId) {
    document.getElementById('confirm-delete-message').textContent = `Möchtest du die Kategorie mit ID ${categoryId} wirklich löschen?`;
    document.getElementById('confirm-delete-modal').style.display = 'block';
    currentDeleteAction = 'category';
    currentDeleteId = categoryId;
}

function confirmDeleteThread(threadId) {
    document.getElementById('confirm-delete-message').textContent = `Möchtest du den Thread mit ID ${threadId} wirklich löschen?`;
    document.getElementById('confirm-delete-modal').style.display = 'block';
    currentDeleteAction = 'thread';
    currentDeleteId = threadId;
}

function confirmDeletePost(postId) {
    document.getElementById('confirm-delete-message').textContent = `Möchtest du die Antwort mit ID ${postId} wirklich löschen?`;
    document.getElementById('confirm-delete-modal').style.display = 'block';
    currentDeleteAction = 'post';
    currentDeleteId = postId;
}

function closeDeleteModal() {
    document.getElementById('confirm-delete-modal').style.display = 'none';
}

function executeDelete() {
    if (!currentDeleteAction || !currentDeleteId) {
        closeDeleteModal();
        return;
    }
    
    const endpoint = `/api/admin/${currentDeleteAction}/${currentDeleteId}`;
    
    fetch(endpoint, {
        method: 'DELETE'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Modal schließen
            closeDeleteModal();
            
            // Daten neu laden
            switch (currentDeleteAction) {
                case 'user':
                    loadUsersData();
                    break;
                case 'category':
                    loadCategoriesData();
                    break;
                case 'thread':
                    loadThreadsData();
                    break;
                case 'post':
                    loadPostsData();
                    break;
            }
        } else {
            alert('Fehler beim Löschen: ' + (data.message || 'Unbekannter Fehler'));
        }
    })
    .catch(error => {
        console.error('Fehler beim Löschen:', error);
        alert('Fehler beim Löschen');
    })
    .finally(() => {
        currentDeleteAction = null;
        currentDeleteId = null;
    });
}
