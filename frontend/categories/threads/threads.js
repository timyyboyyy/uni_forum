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
});

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
}
