document.addEventListener('DOMContentLoaded', function() {
    // Thread-ID aus der URL extrahieren
    const urlParams = new URLSearchParams(window.location.search);
    let threadId = urlParams.get('id');
    
    // Entfernen von Schrägstrichen am Ende der ID
    if (threadId && threadId.endsWith('/')) {
        threadId = threadId.slice(0, -1);
    }
    
    if (!threadId) {
        document.getElementById('thread-title').textContent = 'Fehler: Keine Thread-ID gefunden';
        document.getElementById('thread-content').textContent = 'Bitte wähle einen gültigen Thread aus.';
        return;
    }
    
    loadThread(threadId);
});

function loadThread(threadId) {
    fetch(`/api/thread?id=${threadId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Netzwerkantwort war nicht ok');
            }
            return response.json();
        })
        .then(data => {
            displayThread(data);
        })
        .catch(error => {
            console.error('Fehler beim Laden des Threads:', error);
            document.getElementById('thread-title').textContent = 'Fehler beim Laden des Threads';
            document.getElementById('thread-content').textContent = 'Bitte versuche es später erneut.';
        });
}

function displayThread(data) {
    const thread = data.thread;
    document.title = `${thread.title} - Forum`;
    document.getElementById('thread-title').textContent = thread.title;
    document.getElementById('thread-category').textContent = thread.category;
    document.getElementById('thread-author').textContent = thread.author;
    document.getElementById('thread-date').textContent = thread.date;
    document.getElementById('thread-content').textContent = thread.content;

    const repliesContainer = document.getElementById('replies-container');
    repliesContainer.innerHTML = '';

    if (data.replies && data.replies.length > 0) {
        data.replies.forEach(reply => {
            const replyElement = document.createElement('div');
            replyElement.className = 'post';

            const replyHeader = document.createElement('div');
            replyHeader.className = 'post-header';
            replyHeader.textContent = `${reply.author} schrieb am ${reply.date}:`;

            const replyContent = document.createElement('div');
            replyContent.className = 'post-content';
            replyContent.textContent = reply.content;

            replyElement.appendChild(replyHeader);
            replyElement.appendChild(replyContent);
            repliesContainer.appendChild(replyElement);
        });
    } else {
        repliesContainer.innerHTML = '<p>Noch keine Antworten vorhanden.</p>';
    }
}

f