document.addEventListener('DOMContentLoaded', function() {
    // Prüfe Login-Status
    fetch('/api/check-auth')
        .then(response => response.json())
        .then(data => {
            if (!data.loggedIn) {
                // Nicht eingeloggt - zur Login-Seite weiterleiten
                window.location.href = '/login/';
                return;
            }
            
            // Angemeldeten Benutzer anzeigen
            document.getElementById('username-display').textContent = data.username;
            
            // Benutzerdaten laden
            loadUserData();
            
            // Aktivitäten laden
            loadUserActivity();

            // Login Check für Nav Leiste
            checkLoginStatus();
        })
        .catch(error => {
            console.error('Fehler beim Prüfen des Login-Status:', error);
            window.location.href = '/login/';
        });
    
    // Formulare initialisieren
    const userInfoForm = document.getElementById('user-info-form');
    const passwordForm = document.getElementById('password-form');
    
    if (userInfoForm) {
        userInfoForm.addEventListener('submit', function(e) {
            e.preventDefault();
            updateUserInfo();
        });
    }
    
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            updatePassword();
        });
    }
    
    // Logout-Link-Handler
    const logoutLink = document.getElementById('logout-link');
    if (logoutLink) {
        logoutLink.addEventListener('click', function(e) {
            e.preventDefault();
            fetch('/api/logout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '/';
                }
            })
            .catch(error => {
                console.error('Fehler beim Logout:', error);
            });
        });
    }
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
            <div class="dropdown ${currentPath.startsWith('/user_profile') ? 'active' : ''}">
                <a href="#" class="dropdown-toggle">${userData.username}</a>
                <div class="dropdown-menu">
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
        
        // Updated Logout-Funktion mit Weiterleitung zur Startseite
        document.getElementById('logout-link').addEventListener('click', function(e) {
            e.preventDefault();
            fetch('/api/logout', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'}
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '/'; // Redirect to main page
                }
            })
            .catch(error => {
                console.error('Logout error:', error);
                window.location.href = '/'; // Still redirect even if there's an error
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

// Benutzerdaten laden
function loadUserData() {
    fetch('/api/user-profile')
        .then(response => {
            if (!response.ok) {
                throw new Error('Netzwerkantwort war nicht ok');
            }
            return response.json();
        })
        .then(data => {
            // Formulardaten befüllen
            document.getElementById('username').value = data.username;
            document.getElementById('email').value = data.email;
            document.getElementById('created-at').value = data.created_at;
        })
        .catch(error => {
            console.error('Fehler beim Laden der Benutzerdaten:', error);
            showError('Fehler beim Laden der Benutzerdaten. Bitte versuche es später erneut.');
        });
}

// Benutzeraktivitäten laden
function loadUserActivity() {
    fetch('/api/user-activity')
        .then(response => {
            if (!response.ok) {
                throw new Error('Netzwerkantwort war nicht ok');
            }
            return response.json();
        })
        .then(data => {
            // Aktivitätszähler anzeigen
            document.getElementById('thread-count').textContent = data.thread_count;
            document.getElementById('post-count').textContent = data.post_count;

            // Aktivitätsliste füllen
            const activityList = document.getElementById('activity-list');
            activityList.innerHTML = '';

            if (data.recent_activities && data.recent_activities.length > 0) {
                data.recent_activities.forEach(activity => {
                    const li = document.createElement('li');
                    const link = document.createElement('a');
                    link.href = `/thread/?id=${activity.thread_id}`;
                    link.textContent = activity.title;

                    // Löschbutton hinzufügen
                    const deleteBtn = document.createElement('button');
                    deleteBtn.textContent = 'Löschen';
                    deleteBtn.className = 'delete-btn';

                    // Je nach Aktivitätstyp unterschiedliche Löschlogik
                    if (activity.type === 'Thread erstellt') {
                        deleteBtn.onclick = () => deleteThread(activity.thread_id);
                    } else if (activity.type === 'Antwort geschrieben') {
                        deleteBtn.onclick = () => deletePost(activity.post_id); 
                    }

                    li.appendChild(document.createTextNode(`${activity.type} am ${activity.date}: `));
                    li.appendChild(link);
                    li.appendChild(deleteBtn);
                    activityList.appendChild(li);
                });
            } else {
                activityList.innerHTML = '<li>Keine Aktivitäten gefunden.</li>';
            }
        })
        .catch(error => {
            console.error('Fehler beim Laden der Aktivitäten:', error);
        });
}


// Benutzerinformationen aktualisieren
function updateUserInfo() {
    const username = document.getElementById('username').value;
    const email = document.getElementById('email').value;
    
    // Validierung
    if (!username || !email) {
        showError('Bitte alle Pflichtfelder ausfüllen');
        return;
    }
    
    // API-Anfrage senden
    fetch('/api/update-profile', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            username: username,
            email: email
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess('Benutzerdaten erfolgreich aktualisiert');
            // Username in der Anzeige aktualisieren
            document.getElementById('username-display').textContent = username;
        } else {
            showError(data.message || 'Fehler beim Aktualisieren der Benutzerdaten');
        }
    })
    .catch(error => {
        console.error('Fehler:', error);
        showError('Ein Fehler ist aufgetreten. Bitte versuche es später erneut.');
    });
}

// Passwort aktualisieren
function updatePassword() {
    const currentPassword = document.getElementById('current-password').value;
    const newPassword = document.getElementById('new-password').value;
    const confirmPassword = document.getElementById('confirm-password').value;
    
    // Validierung
    if (!currentPassword || !newPassword || !confirmPassword) {
        showError('Bitte alle Passwortfelder ausfüllen');
        return;
    }
    
    if (newPassword !== confirmPassword) {
        showError('Die neuen Passwörter stimmen nicht überein');
        return;
    }
    
    // API-Anfrage senden
    fetch('/api/update-password', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            current_password: currentPassword,
            new_password: newPassword
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess('Passwort erfolgreich geändert');
            // Passwortfelder zurücksetzen
            document.getElementById('current-password').value = '';
            document.getElementById('new-password').value = '';
            document.getElementById('confirm-password').value = '';
        } else {
            showError(data.message || 'Fehler beim Ändern des Passworts');
        }
    })
    .catch(error => {
        console.error('Fehler:', error);
        showError('Ein Fehler ist aufgetreten. Bitte versuche es später erneut.');
    });
}

// Fehlermeldung anzeigen
function showError(message) {
    const errorElement = document.getElementById('error-message');
    const successElement = document.getElementById('success-message');
    
    errorElement.textContent = message;
    errorElement.style.display = 'block';
    successElement.style.display = 'none';
    
    // Nach 5 Sekunden ausblenden
    setTimeout(() => {
        errorElement.style.display = 'none';
    }, 5000);
}

// Erfolgsmeldung anzeigen
function showSuccess(message) {
    const errorElement = document.getElementById('error-message');
    const successElement = document.getElementById('success-message');
    
    successElement.textContent = message;
    successElement.style.display = 'block';
    errorElement.style.display = 'none';
    
    // Nach 5 Sekunden ausblenden
    setTimeout(() => {
        successElement.style.display = 'none';
    }, 5000);
}

// Neue Funktionen hinzufügen
function deletePost(postId) {
    if(confirm('Möchten Sie diesen Beitrag wirklich löschen?')) {
        fetch(`/api/delete-post/${postId}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                loadUserActivity();
                alert('Beitrag erfolgreich gelöscht');
            }
        });
    }
}

function deleteAccount() {
    if(confirm('Möchten Sie Ihr Konto wirklich unwiderruflich löschen?\nDiese Aktion kann nicht rückgängig gemacht werden!')) {
        fetch('/api/delete-account', {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                window.location.href = '/';
            }
        });
    }
}

function deleteThread(threadId) {
    if (confirm('Möchten Sie diesen Thread wirklich löschen?')) {
        fetch(`/api/thread/${threadId}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Thread erfolgreich gelöscht.');
                loadUserActivity(); // Liste neu laden
            } else {
                alert('Fehler beim Löschen des Threads.');
            }
        })
        .catch(error => {
            console.error('Fehler beim Löschen des Threads:', error);
        });
    }
}


function deletePost(postId) {
    if (confirm('Möchten Sie diese Antwort wirklich löschen?')) {
        fetch(`/api/delete-post/${postId}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Antwort erfolgreich gelöscht.');
                loadUserActivity(); // Liste neu laden
            } else {
                alert('Fehler beim Löschen der Antwort.');
            }
        })
        .catch(error => {
            console.error('Fehler beim Löschen der Antwort:', error);
        });
    }
}

