document.addEventListener('DOMContentLoaded', function() {
    const conversationList = document.getElementById('conversation-list');
    const messageList = document.getElementById('message-list');
    const messageForm = document.getElementById('message-form');
    const messageInput = document.getElementById('message-input');
    const sendButton = document.getElementById('send-button');
    const chatWindow = document.getElementById('chat-window');
    const noConversationSelected = document.getElementById('no-conversation-selected');
    const chatHeader = document.getElementById('chat-header');
    const chatAvatar = document.getElementById('chat-avatar');
    const messagingApp = document.getElementById('messaging-app');
    const currentUserId = messagingApp.dataset.userId;

    const newUserModal = new bootstrap.Modal(document.getElementById('new-conversation-modal'));
    const userSearchInput = document.getElementById('user-search-input');
    const userSearchResults = document.getElementById('user-search-results');

    let currentConversationId = null;
    let messagePollingInterval = null;

    // --- Main Functions ---

    /**
     * Ensures the Staff Members group chat exists.
     */
    async function ensureStaffGroupExists() {
        try {
            await fetch('api_get_or_create_staff_group.php');
        } catch (error) {
            console.error('Error ensuring staff group exists:', error);
        }
    }

    /**
     * Fetches conversations and renders them in the side panel.
     */
    async function loadConversations() {
        try {
            const response = await fetch('api_get_conversations.php');
            const conversations = await response.json();

            if (conversations.error) {
                conversationList.innerHTML = `<div class="list-group-item text-danger">${conversations.error}</div>`;
                return;
            }

            conversationList.innerHTML = ''; // Clear placeholder
            if (conversations.length === 0) {
                conversationList.innerHTML = '<div class="list-group-item text-muted">No conversations yet.</div>';
            } else {
                conversations.forEach(convo => {
                    const convoItem = document.createElement('a');
                    convoItem.href = '#';
                    convoItem.className = 'list-group-item list-group-item-action';
                    convoItem.dataset.conversationId = convo.conversation_id;
                    convoItem.dataset.displayName = convo.display_name;

                    let initials;
                    if (convo.is_group) {
                        initials = convo.display_name.split(' ').map(n => n[0]).join('').toUpperCase();
                    } else {
                        // For 1-on-1, display_name is the full name.
                        const nameParts = convo.display_name.split(' ');
                        initials = (nameParts[0].charAt(0) + (nameParts[1] ? nameParts[1].charAt(0) : '')).toUpperCase();
                    }

                    convoItem.innerHTML = `
                        <div class="d-flex w-100 justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="avatar-initials-sm me-3">${convo.is_group ? '<i class="bi bi-people-fill"></i>' : initials}</div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">${convo.display_name}</h6>
                                    <small class="text-muted text-truncate" style="max-width: 200px;">${convo.last_message || 'No messages yet'}</small>
                                </div>
                            </div>
                            ${convo.unread_count > 0 ? `<span class="badge bg-primary rounded-pill">${convo.unread_count}</span>` : ''}
                        </div>
                    `;
                    convoItem.addEventListener('click', (e) => {
                        e.preventDefault();
                        const avatarContent = convo.is_group ? '<i class="bi bi-people-fill"></i>' : initials;
                        selectConversation(convo.conversation_id, convo.display_name, avatarContent);
                    });
                    conversationList.appendChild(convoItem);
                });
            }
        } catch (error) {
            console.error('Error loading conversations:', error);
            conversationList.innerHTML = '<div class="list-group-item text-danger">Failed to load conversations.</div>';
        }
    }

    /**
     * Selects a conversation, loads its messages, and displays the chat window.
     * @param {number} conversationId
     * @param {string} displayName
     * @param {string} avatarContent - Can be initials or an icon's HTML
     */
    function selectConversation(conversationId, displayName, avatarContent) {
        currentConversationId = conversationId;

        // UI updates
        chatWindow.classList.remove('d-none');
        noConversationSelected.classList.add('d-none');
        chatHeader.textContent = displayName;
        chatAvatar.innerHTML = avatarContent;
        messageInput.disabled = false;
        sendButton.disabled = false;

        // Highlight active conversation
        document.querySelectorAll('#conversation-list .list-group-item').forEach(item => {
            item.classList.remove('active');
        });
        document.querySelector(`[data-conversation-id="${conversationId}"]`).classList.add('active');

        loadMessages(conversationId);

        // Start polling for new messages
        if (messagePollingInterval) clearInterval(messagePollingInterval);
        messagePollingInterval = setInterval(() => loadMessages(conversationId, true), 5000); // Poll every 5 seconds
    }

    /**
     * Fetches and displays messages for a given conversation.
     * @param {number} conversationId
     * @param {boolean} isPolling - If true, won't show loading spinner.
     */
    async function loadMessages(conversationId, isPolling = false) {
        if (!isPolling) {
            messageList.innerHTML = '<div class="text-center p-5"><div class="spinner-border text-primary" role="status"></div></div>';
        }

        try {
            const response = await fetch(`api_get_messages.php?conversation_id=${conversationId}`);
            const messages = await response.json();

            if (messages.error) {
                messageList.innerHTML = `<div class="alert alert-danger">${messages.error}</div>`;
                return;
            }

            const currentMessageCount = messageList.querySelectorAll('.message-bubble').length;
            if (messages.length > currentMessageCount || !isPolling) {
                messageList.innerHTML = '';
                messages.forEach(msg => {
                    appendMessage(msg);
                });
                scrollToBottom();
            }
        } catch (error) {
            console.error('Error loading messages:', error);
        }
    }

    /**
     * Appends a single message to the chat window.
     * @param {object} message
     */
    function appendMessage(message) {
        const messageWrapper = document.createElement('div');
        const isCurrentUser = message.sender_id == currentUserId;

        messageWrapper.className = `d-flex mb-3 ${isCurrentUser ? 'justify-content-end' : 'justify-content-start'}`;
        messageWrapper.innerHTML = `
            <div class="message-bubble p-2 rounded ${isCurrentUser ? 'bg-primary text-white' : 'bg-light'}">
                <div class="message-content">${message.content}</div>
                <div class="message-timestamp small text-muted mt-1" style="font-size: 0.75rem;">
                    ${new Date(message.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                </div>
            </div>
        `;
        messageList.appendChild(messageWrapper);
    }

    /**
     * Handles the submission of the message form.
     */
    async function handleSendMessage(e) {
        e.preventDefault();
        const content = messageInput.value.trim();
        if (!content || !currentConversationId) return;

        const messageData = {
            conversation_id: currentConversationId,
            content: content
        };

        try {
            const response = await fetch('api_send_message.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(messageData)
            });
            const result = await response.json();

            if (result.success) {
                appendMessage(result.message);
                scrollToBottom();
                messageInput.value = '';
                loadConversations(); // Reload convos to show updated last message
            } else {
                console.error('Error sending message:', result.error);
                alert('Failed to send message.');
            }
        } catch (error) {
            console.error('Error sending message:', error);
        }
    }

    /**
     * Handles user search in the new conversation modal.
     */
    async function handleUserSearch() {
        const query = userSearchInput.value.trim();
        if (query.length < 2) {
            userSearchResults.innerHTML = '';
            return;
        }

        try {
            // Using the existing live search API
            const response = await fetch(`api_live_search.php?q=${query}`);
            const users = await response.json();

            userSearchResults.innerHTML = '';
            if (users.length > 0) {
                users.forEach(user => {
                    const userItem = document.createElement('a');
                    userItem.href = '#';
                    userItem.className = 'list-group-item list-group-item-action';
                    userItem.textContent = `${user.first_name} ${user.last_name} (${user.role})`;
                    userItem.addEventListener('click', (e) => {
                        e.preventDefault();
                        startNewConversation(user.id);
                    });
                    userSearchResults.appendChild(userItem);
                });
            } else {
                userSearchResults.innerHTML = '<div class="list-group-item">No users found.</div>';
            }
        } catch (error) {
            console.error('Error searching for users:', error);
        }
    }

    /**
     * Creates or finds a conversation with a given user.
     * @param {number} recipientId
     */
    async function startNewConversation(recipientId) {
        try {
            const response = await fetch('api_create_conversation.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ recipient_id: recipientId })
            });
            const result = await response.json();

            if (result.success) {
                newUserModal.hide();
                await loadConversations(); // Reload the list
                const participantName = userSearchInput.value; // A bit of a cheat to get the name
                const initials = participantName.split(' ').map(n => n[0]).join('').toUpperCase();
                selectConversation(result.conversation_id, participantName, initials);
            } else {
                alert('Error starting conversation: ' + result.error);
            }
        } catch (error) {
            console.error('Error creating conversation:', error);
        }
    }

    /**
     * Scrolls the message list to the bottom.
     */
    function scrollToBottom() {
        messageList.scrollTop = messageList.scrollHeight;
    }


    // --- Event Listeners ---
    messageForm.addEventListener('submit', handleSendMessage);
    userSearchInput.addEventListener('input', handleUserSearch);


    // --- Initial Load ---
    async function initialize() {
        await ensureStaffGroupExists();
        loadConversations();
    }

    initialize();
});
