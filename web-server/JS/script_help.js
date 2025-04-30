// script_help.js
// Acordeón de artículos
document.querySelectorAll('.accordion-header').forEach(header => {
    header.addEventListener('click', function() {
        const item = this.parentElement;
        const isActive = item.classList.contains('active');
        
        // Cerrar todos los acordeones primero
        document.querySelectorAll('.accordion-item').forEach(accordion => {
            accordion.classList.remove('active');
        });
        
        // Abrir el actual si no estaba activo
        if (!isActive) {
            item.classList.add('active');
        }
    });
});

// Búsqueda en el centro de ayuda
const helpSearch = document.getElementById('helpSearch');
if (helpSearch) {
    helpSearch.addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
            searchHelp(this.value);
        }
    });
    
    document.querySelector('.help-search .search-button').addEventListener('click', function() {
        searchHelp(helpSearch.value);
    });
}

function searchHelp(query) {
    if (!query.trim()) return;
    
    // Simular búsqueda (en un caso real sería una petición AJAX)
    console.log(`Buscando ayuda para: ${query}`);
    alert(`Búsqueda de ayuda realizada: "${query}". Redirigiendo a resultados...`);
    
    // Aquí iría la lógica real de búsqueda
}

// Chat en vivo
const liveChatButton = document.getElementById('liveChatButton');
if (liveChatButton) {
    liveChatButton.addEventListener('click', function() {
        // Simular inicio de chat
        console.log('Iniciando chat en vivo...');
        
        // Mostrar modal de chat
        const chatModal = document.createElement('div');
        chatModal.className = 'chat-modal';
        chatModal.innerHTML = `
            <div class="chat-container">
                <div class="chat-header">
                    <h3>Chat con soporte</h3>
                    <button class="close-chat">&times;</button>
                </div>
                <div class="chat-messages">
                    <div class="message agent">
                        <p>¡Hola! Soy María del equipo de soporte. ¿En qué puedo ayudarte hoy?</p>
                    </div>
                </div>
                <div class="chat-input">
                    <input type="text" placeholder="Escribe tu mensaje...">
                    <button class="send-button">Enviar</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(chatModal);
        
        // Cerrar chat
        chatModal.querySelector('.close-chat').addEventListener('click', function() {
            document.body.removeChild(chatModal);
        });
        
        // Enviar mensaje
        chatModal.querySelector('.send-button').addEventListener('click', function() {
            const input = chatModal.querySelector('input');
            const message = input.value.trim();
            
            if (message) {
                const messageDiv = document.createElement('div');
                messageDiv.className = 'message user';
                messageDiv.innerHTML = `<p>${message}</p>`;
                chatModal.querySelector('.chat-messages').appendChild(messageDiv);
                
                input.value = '';
                
                // Simular respuesta
                setTimeout(() => {
                    const responseDiv = document.createElement('div');
                    responseDiv.className = 'message agent';
                    responseDiv.innerHTML = '<p>Gracias por tu mensaje. Estoy revisando tu consulta...</p>';
                    chatModal.querySelector('.chat-messages').appendChild(responseDiv);
                }, 1500);
            }
        });
    });
}

// Estilos dinámicos para el chat
const chatStyles = document.createElement('style');
chatStyles.textContent = `
    .chat-modal {
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 350px;
        max-width: 90%;
        background: white;
        border-radius: var(--border-radius);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        z-index: 1000;
        overflow: hidden;
    }
    
    .chat-container {
        display: flex;
        flex-direction: column;
        height: 400px;
    }
    
    .chat-header {
        padding: 1rem;
        background: var(--primary);
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .chat-header h3 {
        margin: 0;
    }
    
    .close-chat {
        background: none;
        border: none;
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
    }
    
    .chat-messages {
        flex: 1;
        padding: 1rem;
        overflow-y: auto;
    }
    
    .message {
        margin-bottom: 1rem;
        max-width: 80%;
        padding: 0.8rem;
        border-radius: var(--border-radius);
    }
    
    .message.agent {
        background: var(--primary-light);
        align-self: flex-start;
    }
    
    .message.user {
        background: var(--primary);
        color: white;
        align-self: flex-end;
    }
    
    .chat-input {
        display: flex;
        padding: 1rem;
        border-top: 1px solid #eee;
    }
    
    .chat-input input {
        flex: 1;
        padding: 0.6rem;
        border: 1px solid #ddd;
        border-radius: var(--border-radius) 0 0 var(--border-radius);
    }
    
    .send-button {
        padding: 0 1rem;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 0 var(--border-radius) var(--border-radius) 0;
        cursor: pointer;
    }
`;
document.head.appendChild(chatStyles);