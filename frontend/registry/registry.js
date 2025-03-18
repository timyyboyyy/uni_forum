document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            
            // Einfache Validierung
            if (!username || !email || !password) {
                showError('Alle Felder müssen ausgefüllt sein');
                return;
            }
            
            if (password !== confirmPassword) {
                showError('Passwörter stimmen nicht überein');
                return;
            }
            
            // E-Mail-Format überprüfen
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showError('Ungültige E-Mail-Adresse');
                return;
            }
            
            // API-Anfrage senden
            fetch('/api/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    username: username,
                    email: email,
                    password: password
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Erfolgreich registriert
                    window.location.href = '/';
                } else {
                    showError(data.message || 'Registrierung fehlgeschlagen');
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
}); // Diese Klammer fehlte
