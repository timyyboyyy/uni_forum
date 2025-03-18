document.addEventListener('DOMContentLoaded', function() {
    const postForm = document.getElementById('post-form');
    const categorySelect = document.getElementById('category');
    
    // Zuerst Login-Status prüfen
    fetch('/api/check-auth')
        .then(response => response.json())
        .then(data => {
            if (!data.loggedIn) {
                // Nicht eingeloggt - zur Login-Seite weiterleiten
                window.location.href = '/login/';
                return;
            }
            
            // Kategorien aus der Datenbank laden
            loadCategories();
            
            // Formular erst anzeigen, wenn Benutzer eingeloggt ist
            if (postForm) {
                postForm.style.display = 'block';
                
                postForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const title = document.getElementById('title').value;
                    const category = document.getElementById('category').value;
                    const content = document.getElementById('content').value;
                    
                    // API-Anfrage ohne user_id senden
                    fetch('/api/posts', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            title: title,
                            category_id: category,
                            content: content
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            if (response.status === 401) {
                                // Nicht autorisiert, zur Login-Seite weiterleiten
                                window.location.href = '/login/';
                                throw new Error('Nicht autorisiert');
                            }
                            throw new Error('Netzwerkantwort war nicht ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        alert('Beitrag erfolgreich erstellt!');
                        window.location.href = '/thread/?id=' + data.thread_id;
                    })
                    .catch(error => {
                        console.error('Fehler:', error);
                        alert('Fehler beim Erstellen des Beitrags');
                    });
                });
            }
        })
        .catch(error => {
            console.error('Fehler beim Prüfen des Login-Status:', error);
            window.location.href = '/login/';
        });
    
    // Funktion zum Laden der Kategorien aus der Datenbank
    function loadCategories() {
        fetch('/api/categories')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Netzwerkantwort war nicht ok');
                }
                return response.json();
            })
            .then(categories => {
                // Kategorien in die Dropdown-Liste einfügen
                populateCategoryDropdown(categories);
            })
            .catch(error => {
                console.error('Fehler beim Laden der Kategorien:', error);
            });
    }
    
    // Funktion zum Befüllen der Dropdown-Liste mit Kategorien
    function populateCategoryDropdown(categories) {
        // Leere die Dropdown-Liste, behalte aber die erste Option "Bitte wählen..."
        while (categorySelect.options.length > 1) {
            categorySelect.remove(1);
        }
        
        // Füge die Kategorien aus der Datenbank hinzu
        categories.forEach(category => {
            const option = document.createElement('option');
            option.value = category.id;
            option.textContent = category.name;
            categorySelect.appendChild(option);
        });
    }
});
