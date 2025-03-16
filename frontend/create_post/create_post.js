document.addEventListener('DOMContentLoaded', function() {
    const postForm = document.getElementById('post-form');
    
    postForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Formulardaten sammeln
        const title = document.getElementById('title').value;
        const category = document.getElementById('category').value;
        const content = document.getElementById('content').value;
        
        // Daten für die API vorbereiten
        const postData = {
            title: title,
            category_id: category,
            content: content,
            user_id: 1  // Normalerweise würde hier die ID des eingeloggten Benutzers stehen
        };
        
        // API-Anfrage senden
        fetch('/api/posts', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(postData)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Netzwerkantwort war nicht ok');
            }
            return response.json();
        })
        .then(data => {
            // Erfolgreiche Erstellung
            alert('Beitrag erfolgreich erstellt!');
            window.location.href = 'http://localhost'; // Zurück zur Hauptseite
        })
        .catch(error => {
            // Fehlerbehandlung
            console.error('Fehler beim Erstellen des Beitrags:', error);
            alert('Fehler beim Erstellen des Beitrags. Bitte versuchen Sie es später erneut.');
        });
    });
});
