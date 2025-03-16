// forum.js - Datenbankintegration für das Forum
document.addEventListener('DOMContentLoaded', function() {
    loadCategories();
});

// Funktion zum Laden der Kategoriedaten
function loadCategories() {
    // Anzeigen eines Ladeindikators
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
        
        // Kategoriename - MIT LINK FORMATIEREN wie in statischen Daten
        const nameCell = document.createElement('td');
        const nameLink = document.createElement('a');
        nameLink.href = '#' + (category.name || category.kategorie);
        nameLink.textContent = category.name || category.kategorie;
        nameLink.style.color = '#007BFF'; // Blau wie in den statischen Daten
        nameCell.appendChild(nameLink);
        row.appendChild(nameCell);
        
        // Anzahl Themen
        const countCell = document.createElement('td');
        countCell.textContent = category.topic_count || category.themen_anzahl || 0;
        row.appendChild(countCell);
        
        // Letzter Beitrag - FORMATIERUNG WIE STATISCHE DATEN
        const lastPostCell = document.createElement('td');
        lastPostCell.textContent = category.last_post || category.letzter_beitrag || 'Keine Beiträge';
        row.appendChild(lastPostCell);
        
        tableBody.appendChild(row);
    });
}
