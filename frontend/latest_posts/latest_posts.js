document.addEventListener('DOMContentLoaded', function() {
    loadLatestPosts();
});

function loadLatestPosts() {
    // Tabellenkörper auswählen
    const tableBody = document.querySelector('.latest-posts-table tbody');
    
    // Ladeindikator anzeigen
    if (tableBody) {
        tableBody.innerHTML = '<tr><td colspan="4">Beiträge werden geladen...</td></tr>';
    }
    
    // Daten von der API abrufen
    fetch('/api/latest_posts')
        .then(response => {
            if (!response.ok) {
                throw new Error('Netzwerkantwort war nicht ok');
            }
            return response.json();
        })
        .then(data => {
            displayLatestPosts(data);
        })
        .catch(error => {
            console.error('Fehler beim Laden der neuesten Beiträge:', error);
            if (tableBody) {
                tableBody.innerHTML = '<tr><td colspan="4">Fehler beim Laden der Daten. Bitte versuchen Sie es später erneut.</td></tr>';
            }
        });
}

function displayLatestPosts(posts) {
    const tableBody = document.querySelector('.latest-posts-table tbody');
    
    if (tableBody) {
        tableBody.innerHTML = '';
        
        if (posts && posts.length > 0) {
            posts.forEach(post => {
                const row = document.createElement('tr');
                
                // Titel mit Link
                const titleCell = document.createElement('td');
                const titleLink = document.createElement('a');
                titleLink.href = `/thread?id=${post.id}`;
                titleLink.textContent = post.title;
                titleCell.appendChild(titleLink);
                row.appendChild(titleCell);
                
                // Kategorie
                const categoryCell = document.createElement('td');
                categoryCell.textContent = post.category;
                row.appendChild(categoryCell);
                
                // Autor
                const authorCell = document.createElement('td');
                authorCell.textContent = post.author;
                row.appendChild(authorCell);
                
                // Datum
                const dateCell = document.createElement('td');
                dateCell.textContent = post.date;
                row.appendChild(dateCell);
                
                tableBody.appendChild(row);
            });
        } else {
            tableBody.innerHTML = '<tr><td colspan="4">Keine Beiträge gefunden.</td></tr>';
        }
    }
}
