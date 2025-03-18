document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('login-form');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            
            // Einfache Validierung
            if (!username || !password) {
                showError('Bitte Benutzername und Passwort eingeben');
                return;
            }
            
            // API-Anfrage senden
            fetch('/api/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    username: username,
                    password: password
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Erfolgreich eingeloggt
                    window.location.href = '/';
                } else {
                    showError(data.message || 'Login fehlgeschlagen');
                }
            })
            .catch(error => {
                console.error('Fehler:', error);
                showError('Ein Fehler ist aufgetreten. Bitte versuche es später erneut.');
            });
        });
    }
    
    function showError(message) {
        const errorElement = document.getElementById('error-message');
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        } else {
            alert(message);
        }
    }
});
