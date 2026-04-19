const chatMessages = document.getElementById('chatMessages');
const userInput = document.getElementById('userInput');
const chatForm = document.getElementById('chatForm');

let conversationHistory = [];
const MAX_HISTORY = 10;
let currentRiskLevel = 'none';
let consentModalTimer = null;

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
        const response = await fetch('MentalAI/chatbot_handler.php', {
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

        const contentType = response.headers.get('content-type') || '';
        let data = null;

        if (contentType.includes('application/json')) {
            data = await response.json();
        } else {
            const rawText = await response.text();
            const snippet = rawText ? rawText.slice(0, 160).replace(/\s+/g, ' ').trim() : '';
            throw new Error(`Server returned invalid response format (HTTP ${response.status}). ${snippet}`.trim());
        }

        if (!response.ok) {
            const serverError = data && data.error ? data.error : `HTTP ${response.status}`;
            throw new Error(serverError);
        }

        removeTypingIndicator();
        
        if (data.success) {
            if (data.source && data.source !== 'openai') {
                console.warn('Chatbot fallback response used instead of OpenAI.');
            }
            if (data.debug) {
                console.warn('OpenAI debug:', data.debug);
            }
            
            // Handle risk level and crisis mode
            if (data.risk) {
                currentRiskLevel = data.risk.level;
                handleRiskLevel(data.risk.level);
            }
            
            addMessage(data.response, 'bot', data.actions, data.risk);
            handleMentalHealthActions(data.actions);

            conversationHistory.push({
                user: message,
                bot: data.response,
                timestamp: new Date(),
                riskLevel: data.risk ? data.risk.level : 'none'
            });
        } else {
            console.error('API Error:', data);
            addMessage(data.error || 'Sorry, I encountered an error. Please try again.', 'bot');
        }
    } catch (error) {
        console.error('Network Error:', error);
        removeTypingIndicator();

        const messageText = (error && error.message) ? error.message : '';
        const isLikelyNetworkIssue = error instanceof TypeError;

        if (isLikelyNetworkIssue) {
            addMessage('Unable to reach the chatbot service right now. Please check if the site server is running and try again.', 'bot');
        } else {
            addMessage(`Chatbot service error: ${messageText}`, 'bot');
        }
    }
}

function limitHistory(history) {
    return history.slice(-MAX_HISTORY);
}

function addMessage(text, sender, actions = null, risk = null) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${sender}-message`;
    
    // Add risk level class for styling
    if (risk) {
        messageDiv.classList.add(`risk-${risk.level}`);
    }
    
    const avatar = document.createElement('div');
    avatar.className = 'message-avatar';
    avatar.innerHTML = sender === 'bot' ? '<i class="fas fa-robot"></i>' : '<i class="fas fa-user"></i>';
    
    // Add risk badge to bot messages with mental health content
    if (sender === 'bot' && risk && risk.level !== 'none') {
        const riskBadge = document.createElement('div');
        riskBadge.className = `risk-badge risk-${risk.level}`;
        const badgeIcons = { 'high': '🔴', 'moderate': '🟡', 'low': '🟢' };
        riskBadge.textContent = badgeIcons[risk.level] || '';
        avatar.appendChild(riskBadge);
    }
    
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
            if (action.action.includes('Emergency') || action.action.includes('emergency') || action.action.includes('urgent')) {
                btn.classList.add('emergency-btn');
            }
            btn.textContent = action.label;
            btn.onclick = (e) => {
                e.preventDefault();
                handleActionClick(action.action);
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
    content.className = 'typing-indicator';
    
    for (let i = 0; i < 3; i++) {
        const dot = document.createElement('div');
        dot.className = 'typing-dot';
        content.appendChild(dot);
    }

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

function handleRiskLevel(level) {
    const container = document.querySelector('.chatbot-container');

    if (!container) {
        return;
    }

    container.classList.remove('risk-moderate');
    currentRiskLevel = level;

    if (level === 'high') {
        showCrisisModal();
    } else if (level === 'moderate') {
        container.classList.add('risk-moderate');
    }
}

function handleMentalHealthActions(actions) {
    if (!actions || actions.length === 0) {
        return;
    }

    if (currentRiskLevel === 'high') {
        return;
    }

    if (consentModalTimer) {
        clearTimeout(consentModalTimer);
        consentModalTimer = null;
    }

    const hasMentalHealthAction = actions.some(action => 
        action.label.includes('Psychiatrist') || 
        action.label.includes('Counseling') || 
        action.label.includes('Mental') ||
        action.label.includes('Self-Care')
    );

    if (hasMentalHealthAction && !sessionStorage.getItem('consent_asked')) {
        consentModalTimer = setTimeout(() => {
            showMemoryConsentModal();
            consentModalTimer = null;
        }, 2000);
        sessionStorage.setItem('consent_asked', 'true');
    }
}

function openBookingPage() {
    window.location.href = 'book_appointment.php';
}

function handleActionClick(action) {
    if (action === 'Show emergency contacts') {
        showCrisisModal();
    } else if (action === 'I want to call a trusted person') {
        alert('Please reach out to a trusted family member or friend right now.\n\nYou can also call:\n- 911 (Emergency)\n- 988 (Suicide & Crisis Lifeline)\n- 741741 (Text to Crisis Text Line)');
    } else if (typeof action === 'string' && action.toLowerCase().includes('book') && action.toLowerCase().includes('appointment')) {
        openBookingPage();
    } else {
        sendMessage(action);
    }
}

function showCrisisModal() {
    const modal = document.getElementById('crisisModal');
    const consentModal = document.getElementById('memoryConsentModal');
    if (!modal) {
        return;
    }

    if (consentModal) {
        consentModal.classList.add('hidden');
        consentModal.setAttribute('hidden', 'hidden');
    }

    if (consentModalTimer) {
        clearTimeout(consentModalTimer);
        consentModalTimer = null;
    }

    modal.classList.remove('hidden');
    modal.removeAttribute('hidden');
    currentRiskLevel = 'high';
    document.body.style.overflow = 'hidden';
}

function closeCrisisModal() {
    const modal = document.getElementById('crisisModal');
    if (!modal) {
        return;
    }

    modal.classList.add('hidden');
    modal.setAttribute('hidden', 'hidden');
    currentRiskLevel = 'none';
    document.body.style.overflow = 'auto';
}

function showMemoryConsentModal() {
    const modal = document.getElementById('memoryConsentModal');
    if (!modal) {
        return;
    }

    if (currentRiskLevel === 'high' || !document.getElementById('crisisModal').classList.contains('hidden')) {
        return;
    }

    modal.classList.remove('hidden');
    modal.removeAttribute('hidden');
}

function setMemoryConsent(consent) {
    const modal = document.getElementById('memoryConsentModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.setAttribute('hidden', 'hidden');
    }

    if (consentModalTimer) {
        clearTimeout(consentModalTimer);
        consentModalTimer = null;
    }

    localStorage.setItem('memoryConsent', consent ? 'yes' : 'no');
    if (consent) {
        addMessage('✓ Great! I\'ll track your progress to provide better support.', 'bot');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    userInput.focus();
    console.log('Mental Health Driven Healthcare Assistant loaded');

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeCrisisModal();
        }
    });
});
