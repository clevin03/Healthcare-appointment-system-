const chatMessages = document.getElementById('chatMessages');
const userInput = document.getElementById('userInput');
const chatForm = document.getElementById('chatForm');

let conversationHistory = [];
const MAX_HISTORY = 10;

function handleFormSubmit(event) {
    event.preventDefault();
    const message = userInput.value.trim();
    if (message) {
        sendMessage(message);
        userInput.value = '';
        userInput.focus(); 
    }
}

async function sendMessage(message) {

    addMessage(message, 'user');
    
    showTypingIndicator();
    
    try {
        const response = await fetch('api/chatbot_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                message: message,
                patient_id: patientId,
                history: limitHistory(conversationHistory)
            })
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();
        removeTypingIndicator();
        
        if (data.success) {
            addMessage(data.response, 'bot', data.actions);

            conversationHistory.push({
                user: message,
                bot: data.response,
                timestamp: new Date()
            });
        } else {
            console.error('API Error:', data);
            addMessage(data.error || 'Sorry, I encountered an error. Please try again.', 'bot');
        }
    } catch (error) {
        console.error('Network Error:', error);
        removeTypingIndicator();
        addMessage('Sorry, I\'m having trouble connecting. Please check your internet and try again.', 'bot');
    }
}

function limitHistory(history) {
    return history.slice(-MAX_HISTORY);
}

function addMessage(text, sender, actions = null) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${sender}-message`;
    
    const avatar = document.createElement('div');
    avatar.className = 'message-avatar';
    avatar.innerHTML = sender === 'bot' ? '<i class="fas fa-robot"></i>' : '<i class="fas fa-user"></i>';
    
    const content = document.createElement('div');
    content.className = 'message-content';

    if (text.includes('<table>') || text.includes('<div class=')) {
        content.innerHTML = text;
    } else {
        const paragraphs = text.split('\n').filter(p => p.trim());
        paragraphs.forEach(para => {
            const p = document.createElement('p');
            p.textContent = para;
            content.appendChild(p);
        });
    }

    if (actions && actions.length > 0) {
        const optionsDiv = document.createElement('div');
        optionsDiv.className = 'message-options';
        
        actions.forEach(action => {
            const btn = document.createElement('button');
            btn.className = 'option-btn';
            btn.textContent = action.label;
            btn.onclick = (e) => {
                e.preventDefault();
                sendMessage(action.action);
            };
            optionsDiv.appendChild(btn);
        });
        
        content.appendChild(optionsDiv);
    }
    
    messageDiv.appendChild(avatar);
    messageDiv.appendChild(content);
    
    chatMessages.appendChild(messageDiv);

    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function showTypingIndicator() {
    const messageDiv = document.createElement('div');
    messageDiv.className = 'message bot-message';
    messageDiv.id = 'typingIndicator';
    
    const avatar = document.createElement('div');
    avatar.className = 'message-avatar';
    avatar.innerHTML = '<i class="fas fa-robot"></i>';
    
    const content = document.createElement('div');
    content.className = 'message-content typing-indicator';
    content.innerHTML = '<div class="typing-dot"></div><div class="typing-dot"></div><div class="typing-dot"></div>';
    
    messageDiv.appendChild(avatar);
    messageDiv.appendChild(content);
    
    chatMessages.appendChild(messageDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function removeTypingIndicator() {
    const typing = document.getElementById('typingIndicator');
    if (typing) {
        typing.remove();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    userInput.focus();
    console.log('Healthcare Assistant Chatbot loaded');
});
