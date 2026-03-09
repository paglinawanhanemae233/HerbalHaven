/**
 * Chat Interface JavaScript
 * Handles real-time chat with AI assistant
 */

console.log('chat.js loaded');

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded fired');
    const chatForm = document.getElementById('chatForm');
    const chatInput = document.getElementById('chatInput');
    const chatMessages = document.getElementById('chatMessages');
    const sendBtn = document.getElementById('sendBtn');
    
    console.log('Elements found:', {
        chatForm: !!chatForm,
        chatInput: !!chatInput,
        chatMessages: !!chatMessages,
        sendBtn: !!sendBtn
    });
    // Get initialHerb from global scope (set by PHP in chat-assistant.php)
    // Safely get the value, defaulting to empty string if not set
    const initialHerb = (function() {
        if (typeof window !== 'undefined' && window.hasOwnProperty('initialHerb')) {
            return window.initialHerb || '';
        }
        return '';
    })();

    let isProcessing = false;

    // Focus input on load
    if (chatInput) {
        chatInput.focus();
    }

    // Handle form submission
    if (chatForm) {
        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Form submitted, preventing default');
            sendMessage();
            return false;
        });
    } else {
        console.error('Chat form not found!');
    }

    // Handle suggestion chips
    const suggestionButtons = document.querySelectorAll('.suggestion-chip');
    suggestionButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const question = this.getAttribute('data-question');
            if (question && chatInput) {
                chatInput.value = question;
                sendMessage();
            }
        });
    });

    // Auto-resize textarea (if chat input becomes textarea)
    if (chatInput && chatInput.tagName === 'TEXTAREA') {
        chatInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    }

    // Send message function
    function sendMessage() {
        if (!chatInput) {
            console.error('Chat input not found!');
            return;
        }
        
        const message = chatInput.value.trim();
        console.log('Sending message:', message);
        
        if (!message || isProcessing) {
            console.log('Message empty or already processing');
            return;
        }

        // Add user message to chat
        addMessage(message, 'user');
        chatInput.value = '';
        chatInput.style.height = 'auto';
        
        // Show loading indicator
        const loadingId = addLoadingMessage();
        isProcessing = true;
        sendBtn.disabled = true;
        sendBtn.textContent = 'Sending...';

        // Send to server
        const formData = new FormData();
        formData.append('action', 'send_message');
        formData.append('message', message);
        if (initialHerb) {
            formData.append('initial_herb', initialHerb);
        }

        // Use current page URL for the fetch request
        const chatUrl = window.location.href;
        
        console.log('Sending message to:', chatUrl);
        
        fetch(chatUrl, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers.get('content-type'));
            
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('Error response:', text);
                    throw new Error(`HTTP error! status: ${response.status}, body: ${text.substring(0, 200)}`);
                });
            }
            
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                return response.text().then(text => {
                    console.error('Non-JSON response:', text.substring(0, 500));
                    throw new Error('Server returned non-JSON response. Check console for details.');
                });
            }
            
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            removeLoadingMessage(loadingId);
            
            if (data && data.success) {
                addMessage(data.response, 'ai');
            } else {
                addMessage('I apologize, but I encountered an error: ' + (data?.error || 'Unknown error'), 'ai');
                console.error('API error:', data?.error);
            }
        })
        .catch(error => {
            removeLoadingMessage(loadingId);
            const errorMsg = error.message || 'Unknown error';
            addMessage('I apologize, but there was a connection error: ' + errorMsg + '. Please check the console for details.', 'ai');
            console.error('Chat error:', error);
        })
        .finally(() => {
            isProcessing = false;
            sendBtn.disabled = false;
            sendBtn.textContent = 'Send';
            chatInput.focus();
            scrollToBottom();
        });
    }

    // Add message to chat
    function addMessage(text, type) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${type}`;
        
        const bubble = document.createElement('div');
        bubble.className = 'message-bubble';
        
        // Clean up asterisks and format text
        let cleanedText = text
            .replace(/\*\*([^*]+)\*\*/g, '$1') // Remove **bold**
            .replace(/\*([^*]+)\*/g, '$1') // Remove *italic*
            .replace(/\*{3,}/g, '') // Remove multiple asterisks
            .replace(/^\s*\*\s+/gm, '• ') // Convert * to bullet
            .replace(/\s+\*\s+/g, ' '); // Remove standalone asterisks
        
        // Format text with line breaks
        const formattedText = cleanedText.split('\n').map(line => {
            const trimmed = line.trim();
            if (trimmed === '') {
                return '<br>';
            }
            // Check if line starts with bullet
            if (trimmed.startsWith('•') || trimmed.startsWith('-')) {
                return `<p style="margin: 0.25rem 0;">${escapeHtml(trimmed)}</p>`;
            }
            return `<p>${escapeHtml(trimmed)}</p>`;
        }).join('');
        
        bubble.innerHTML = formattedText;
        messageDiv.appendChild(bubble);
        
        chatMessages.appendChild(messageDiv);
        scrollToBottom();
    }

    // Add loading indicator
    function addLoadingMessage() {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'chat-message ai';
        messageDiv.id = 'loading-message';
        
        const bubble = document.createElement('div');
        bubble.className = 'message-bubble';
        bubble.innerHTML = '<div class="loading-indicator"><span class="spinner"></span> Thinking...</div>';
        
        messageDiv.appendChild(bubble);
        chatMessages.appendChild(messageDiv);
        scrollToBottom();
        
        return 'loading-message';
    }

    // Remove loading indicator
    function removeLoadingMessage(id) {
        const loadingMsg = document.getElementById(id);
        if (loadingMsg) {
            loadingMsg.remove();
        }
    }

    // Scroll to bottom of chat
    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Allow Enter to send (Shift+Enter for new line if textarea)
    if (chatInput) {
        chatInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
    }

    // Initial scroll
    scrollToBottom();
});

