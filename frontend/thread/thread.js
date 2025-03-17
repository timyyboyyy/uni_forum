document.addEventListener('DOMContentLoaded', function() {
    // Thread-ID aus der URL extrahieren
    const urlParams = new URLSearchParams(window.location.search);
    const threadId = urlParams.get('id');
    
    if (!threadId) {
        document.getElementById('thread-title').textContent = 'Fehler: Keine Thread-ID gefunden';
        document.getElementById('thread-content').textContent = 'Bitte wähle einen gültigen Thread aus.';
        return;
    }
    
    loadThread(threadId);
    
    // Event-Listener für das Antwortformular
    const replyForm = document.getElementById('reply-form');
    replyForm.addEventListener('submit', function(e) {
        e.preventDefault();
        submitReply(threadId);
    });
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
    // Thread-Daten anzeigen
    const thread = data.thread;
    document.title = thread.title + ' - Forum';
    document.getElementById('thread-title').textContent = thread.title;
    document.getElementById('thread-category').textContent = thread.category;
    document.getElementById('thread-author').textContent = thread.author;
    document.getElementById('thread-date').textContent = thread.date;
    document.getElementById('thread-content').textContent = thread.content;
    
    // Antworten anzeigen
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

function submitReply(threadId) {
    const content = document.getElementById('reply-content').value;
    
    if (!content.trim()) {
        alert('Bitte gib einen Antworttext ein.');
        return;
    }
    
    fetch('/api/reply', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            thread_id: threadId,
            content: content,
            user_id: 1 // HINWEIS: Im echten System sollte die ID des eingeloggten Benutzers verwendet werden
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Netzwerkantwort war nicht ok');
        }
        return response.json();
    })
    .then(data => {
        // Erfolgreiche Erstellung
        document.getElementById('reply-content').value = '';
        loadThread(threadId); // Thread neu laden, um die neue Antwort anzuzeigen
    })
    .catch(error => {
        console.error('Fehler beim Erstellen der Antwort:', error);
        alert('Fehler beim Erstellen der Antwort. Bitte versuche es später erneut.');
    });
}
