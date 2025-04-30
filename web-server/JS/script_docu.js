// script_doc.js
// Búsqueda en documentación
const docSearch = document.getElementById('docSearch');
if (docSearch) {
    docSearch.addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
            searchDocumentation(this.value);
        }
    });
    
    document.querySelector('.search-button').addEventListener('click', function() {
        searchDocumentation(docSearch.value);
    });
}

function searchDocumentation(query) {
    if (!query.trim()) return;
    
    // Simular búsqueda (en un caso real sería una petición AJAX)
    console.log(`Buscando: ${query}`);
    alert(`Búsqueda realizada: "${query}". Redirigiendo a resultados...`);
    
    // Aquí iría la lógica real de búsqueda
}

// Generar API Key
document.querySelector('.api-key-button')?.addEventListener('click', function() {
    const apiKey = generateApiKey();
    alert(`Tu nueva API Key es:\n\n${apiKey}\n\nGuárdala en un lugar seguro.`);
});

function generateApiKey() {
    const chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    let result = 'itk_';
    for (let i = 0; i < 32; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return result;
}

// Integración con Slack - Ejemplo de código interactivo
document.querySelectorAll('.integration-card').forEach(card => {
    const header = card.querySelector('.integration-header');
    if (header) {
        header.addEventListener('click', function() {
            const content = this.nextElementSibling;
            content.style.display = content.style.display === 'none' ? 'block' : 'none';
        });
    }
});

// Copiar ejemplos de código
document.querySelectorAll('pre code').forEach(codeBlock => {
    codeBlock.addEventListener('click', function() {
        const range = document.createRange();
        range.selectNode(this);
        window.getSelection().removeAllRanges();
        window.getSelection().addRange(range);
        document.execCommand('copy');
        window.getSelection().removeAllRanges();
        
        // Feedback visual
        const originalText = this.textContent;
        this.textContent = '¡Copiado!';
        setTimeout(() => {
            this.textContent = originalText;
        }, 2000);
    });
});

// Juego "Atrapa los tickets"
const gameButton = document.querySelector('.game-button');
if (gameButton) {
    gameButton.addEventListener('click', function() {
        const gameScreen = document.querySelector('.game-screen');
        const scoreElement = document.querySelector('.game-score span');
        let score = 0;
        
        // Reset game
        gameScreen.innerHTML = '';
        scoreElement.textContent = '0';
        
        // Create tickets
        for (let i = 0; i < 3; i++) {
            const ticket = document.createElement('div');
            ticket.className = 'ticket';
            ticket.textContent = 'TICKET';
            ticket.id = `ticket${i+1}`;
            
            // Random position
            const randomLeft = Math.random() * 80;
            const randomTop = Math.random() * 80;
            ticket.style.left = `${randomLeft}%`;
            ticket.style.top = `${randomTop}%`;
            
            // Click event
            ticket.addEventListener('click', function() {
                score++;
                scoreElement.textContent = score;
                this.style.display = 'none';
                
                // Create new ticket
                setTimeout(() => {
                    const newTicket = document.createElement('div');
                    newTicket.className = 'ticket';
                    newTicket.textContent = 'TICKET';
                    newTicket.style.left = `${Math.random() * 80}%`;
                    newTicket.style.top = `${Math.random() * 80}%`;
                    
                    newTicket.addEventListener('click', function() {
                        score++;
                        scoreElement.textContent = score;
                        this.style.display = 'none';
                    });
                    
                    gameScreen.appendChild(newTicket);
                }, 500);
            });
            
            gameScreen.appendChild(ticket);
        }
        
        this.textContent = '¡A atrapar!';
    });
}

// Feedback buttons
document.querySelectorAll('.feedback-btn').forEach(button => {
    button.addEventListener('click', function() {
        alert('¡Gracias por tu feedback! Lo tendremos en cuenta para mejorar.');
    });
});

// Easter egg for clicking logo multiple times
let logoClickCount = 0;
const logo = document.querySelector('.logo');
if (logo) {
    logo.addEventListener('click', function() {
        logoClickCount++;
        
        if (logoClickCount >= 5) {
            const confetti = document.createElement('div');
            confetti.textContent = '🎉';
            confetti.style.position = 'fixed';
            confetti.style.fontSize = '2rem';
            confetti.style.left = `${Math.random() * window.innerWidth}px`;
            confetti.style.top = '0';
            confetti.style.animation = 'fall 3s linear forwards';
            
            document.body.appendChild(confetti);
            
            setTimeout(() => {
                confetti.remove();
            }, 3000);
            
            logoClickCount = 0;
        }
    });
}

// Add animation for confetti
const style = document.createElement('style');
style.textContent = `
    @keyframes fall {
        to { transform: translateY(${window.innerHeight}px) rotate(360deg); }
    }
`;
document.head.appendChild(style);