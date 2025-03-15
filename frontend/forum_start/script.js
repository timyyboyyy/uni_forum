// forum.js - Datenbankintegration für das Forum
document.addEventListener('DOMContentLoaded', function() {
    loadCategories();
});

// Funktion zum Laden der Kategoriedaten
function loadCategories() {
    // Anzeigen eines Ladeindikatiors
    const tableBody = document.querySelector('table tbody');
    if (tableBody) {
        tableBody.innerHTML = '<tr><td colspan="3">Daten werden geladen...</td></tr>';
    }
    
    // API-Anfrage an das PHP-Backend
    fetch('/api/categories')
        .then(response => {
            if (!response.ok) {
                throw new Error('Netzwerkantwort war nicht ok');
            }
            return response.json();
        })
        .then(data => {
            displayCategories(data);
        })
        .catch(error => {
            console.error('Fehler beim Abrufen der Kategoriedaten:', error);
            if (tableBody) {
                tableBody.innerHTML = '<tr><td colspan="3">Fehler beim Laden der Daten. Bitte später erneut versuchen.</td></tr>';
            }
        });
}

// Funktion zur Anzeige der Kategoriedaten in der Tabelle
function displayCategories(categories) {
    const tableBody = document.querySelector('table tbody');
    if (!tableBody) return;
    
    // Tabelle leeren
    tableBody.innerHTML = '';
    
    // Daten in Tabelle einfügen
    categories.forEach(category => {
        const row = document.createElement('tr');
        
        // Kategoriename
        const nameCell = document.createElement('td');
        nameCell.textContent = category.name;
        row.appendChild(nameCell);
        
        // Anzahl Themen
        const countCell = document.createElement('td');
        countCell.textContent = category.topic_count;
        row.appendChild(countCell);
        
        // Letzter Beitrag
        const lastPostCell = document.createElement('td');
        lastPostCell.textContent = formatDate(category.last_post_date) + ' von ' + category.last_post_user;
        row.appendChild(lastPostCell);
        
        tableBody.appendChild(row);
    });
}

// Hilfsfunktion zur Formatierung des Datums
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('de-DE') + ', ' + 
           date.getHours().toString().padStart(2, '0') + ':' + 
           date.getMinutes().toString().padStart(2, '0') + ' Uhr';
}
