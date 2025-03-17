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
    categoriesList.innerHTML = '';
    
    categories.forEach(category => {
        const li = document.createElement('li');
        const a = document.createElement('a');
        // URL zum Unterordner "threads" innerhalb von "categories"
        a.href = `/categories/threads/?id=${category.id}&name=${encodeURIComponent(category.name)}`;
        a.textContent = category.name;
        li.appendChild(a);
        categoriesList.appendChild(li);
    });
}


