document.addEventListener('DOMContentLoaded', function() {
    loadCategories();
});

// Funktion zum Laden der Kategorien aus der API
function loadCategories() {
    // Lade-Indikator anzeigen
    const categoriesList = document.querySelector('.categories-list');
    if (categoriesList) {
        categoriesList.innerHTML = '<li>Kategorien werden geladen...</li>';
    }
    
    // API-Anfrage senden
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
            console.error('Fehler beim Abrufen der Kategorien:', error);
            if (categoriesList) {
                categoriesList.innerHTML = '<li>Fehler beim Laden der Kategorien. Bitte versuchen Sie es später erneut.</li>';
            }
        });
}

// Funktion zum Anzeigen der Kategorien in der Liste
function displayCategories(categories) {
    const categoriesList = document.querySelector('.categories-list');
    if (!categoriesList) return;
    
    // Liste leeren
    categoriesList.innerHTML = '';
    
    // Kategorien in die Liste einfügen
    if (categories && categories.length > 0) {
        categories.forEach(category => {
            const listItem = document.createElement('li');
            const link = document.createElement('a');
            link.href = '/threads?category=' + encodeURIComponent(category.name);
            link.textContent = category.name;
            listItem.appendChild(link);
            categoriesList.appendChild(listItem);
        });
    } else {
        categoriesList.innerHTML = '<li>Keine Kategorien gefunden</li>';
    }
}
