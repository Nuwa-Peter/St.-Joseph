<?php
require_once 'config.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: " . login_url());
    exit;
}

require_once 'includes/header.php';
?>

<div class="container mt-4" id="messaging-app" data-user-id="<?php echo htmlspecialchars($_SESSION['id']); ?>" data-user-role="<?php echo htmlspecialchars($_SESSION['role']); ?>">
    <div class="row" style="height: 75vh; border: 1px solid #dee2e6; border-radius: 0.3rem;">
        <!-- Left Column: Conversations List -->
        <div class="col-md-4 border-end d-flex flex-column">
            <div class="p-3 d-flex justify-content-between align-items-center border-bottom">
                <h4 class="mb-0">Conversations</h4>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#new-conversation-modal">
                    <i class="bi bi-pencil-square me-1"></i> New Message
                </button>
            </div>
            <div class="list-group list-group-flush overflow-auto" id="conversation-list">
                <!-- Placeholder for conversations, will be loaded by JS -->
                <div class="list-group-item list-group-item-action text-center p-5">
                    <p>Loading conversations...</p>
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Chat Window -->
        <div class="col-md-8 h-100 d-flex flex-column">
            <div id="chat-window" class="d-none h-100 d-flex flex-column">
                <div class="p-3 border-bottom d-flex align-items-center">
                    <div class="avatar-initials-sm me-2" id="chat-avatar"></div>
                    <h5 class="mb-0" id="chat-header"></h5>
                </div>
                <div class="flex-grow-1 p-3 overflow-auto" id="message-list">
                    <!-- Messages will be loaded here by JS -->
                </div>
                <div class="p-3 bg-light border-top">
                    <div id="reply-context-container" class="d-none alert alert-secondary p-2 mb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-truncate">
                                <small class="text-muted">Replying to:</small>
                                <em id="reply-context-text"></em>
                            </div>
                            <button type="button" class="btn-close btn-sm" id="cancel-reply-btn" aria-label="Close"></button>
                        </div>
                    </div>
                    <form id="message-form">
                        <div class="input-group">
                            <input type="text" id="message-input" class="form-control" placeholder="Type a message..." autocomplete="off" disabled>
                            <button class="btn btn-primary" type="submit" id="send-button" disabled>
                                <i class="bi bi-send-fill"></i> Send
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <div id="no-conversation-selected" class="d-flex flex-grow-1 justify-content-center align-items-center h-100">
                <div class="text-center text-muted">
                    <i class="bi bi-chat-dots-fill" style="font-size: 4rem;"></i>
                    <p class="mt-3">Select a conversation to start chatting.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for New Conversation -->
<div class="modal fade" id="new-conversation-modal" tabindex="-1" aria-labelledby="newConversationModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="newConversationModalLabel">New Message</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="new-conversation-form">
          <div class="mb-3">
            <label for="user-search-input" class="form-label">Search for a user:</label>
            <input type="text" class="form-control" id="user-search-input" placeholder="Start typing a name...">
            <div id="user-search-results" class="list-group mt-2"></div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // API Endpoints
    const API_URLS = {
        getConversations: '<?php echo url("api/get_conversations"); ?>',
        getMessages: '<?php echo url("api/get_messages"); ?>',
        sendMessage: '<?php echo url("api/send_message"); ?>',
        searchUsers: '<?php echo url("api/live_search"); ?>',
        createConversation: '<?php echo url("api/create_conversation"); ?>',
        deleteMessage: '<?php echo url("api/delete_message"); ?>'
    };

    // DOM Elements
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
    const currentUserRole = messagingApp.dataset.userRole;
    const adminRoles = ['headteacher', 'root', 'director', 'admin'];

    const newUserModal = new bootstrap.Modal(document.getElementById('new-conversation-modal'));
    const userSearchInput = document.getElementById('user-search-input');
    const userSearchResults = document.getElementById('user-search-results');

    const replyContextContainer = document.getElementById('reply-context-container');
    const replyContextText = document.getElementById('reply-context-text');
    const cancelReplyBtn = document.getElementById('cancel-reply-btn');

    // State
    let currentConversationId = null;
    let currentConversationInfo = { name: '', isGroup: false };
    let replyingToMessageId = null;
    let messagePollingInterval = null;

    // --- Main Functions ---

    async function loadConversations() {
        try {
            const response = await fetch(API_URLS.getConversations);
            const conversations = await response.json();

            if (conversations.error) {
                conversationList.innerHTML = `<div class="list-group-item text-danger">${conversations.error}</div>`;
                return;
            }

            conversationList.innerHTML = '';
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
                        selectConversation(convo.conversation_id, convo.display_name, avatarContent, convo.is_group);
                    });
                    conversationList.appendChild(convoItem);
                });
            }
        } catch (error) {
            console.error('Error loading conversations:', error);
            conversationList.innerHTML = '<div class="list-group-item text-danger">Failed to load conversations.</div>';
        }
    }

    function selectConversation(conversationId, displayName, avatarContent, isGroup) {
        currentConversationId = conversationId;
        currentConversationInfo = { name: displayName, isGroup: isGroup };

        chatWindow.classList.remove('d-none');
        noConversationSelected.classList.add('d-none');
        chatHeader.textContent = displayName;
        chatAvatar.innerHTML = avatarContent;
        messageInput.disabled = false;
        sendButton.disabled = false;

        document.querySelectorAll('#conversation-list .list-group-item').forEach(item => {
            item.classList.remove('active');
        });
        const activeItem = document.querySelector(`[data-conversation-id="${conversationId}"]`);
        if(activeItem) activeItem.classList.add('active');

        loadMessages(conversationId);

        if (messagePollingInterval) clearInterval(messagePollingInterval);
        messagePollingInterval = setInterval(() => loadMessages(conversationId, true), 5000);
    }

    async function loadMessages(conversationId, isPolling = false) {
        if (!isPolling) {
            messageList.innerHTML = '<div class="text-center p-5"><div class="spinner-border text-primary" role="status"></div></div>';
        }

        try {
            const response = await fetch(`${API_URLS.getMessages}?conversation_id=${conversationId}`);
            const messages = await response.json();

            if (messages.error) {
                messageList.innerHTML = `<div class="alert alert-danger">${messages.error}</div>`;
                return;
            }

            const currentMessageCount = messageList.querySelectorAll('.message-bubble').length;
            if (messages.length > currentMessageCount || !isPolling) {
                messageList.innerHTML = '';
                messages.forEach(msg => appendMessage(msg));
                scrollToBottom();
            }
        } catch (error) {
            console.error('Error loading messages:', error);
        }
    }

    function appendMessage(message) {
        const messageWrapper = document.createElement('div');
        const isCurrentUser = message.sender_id == currentUserId;
        const isAdmin = adminRoles.includes(currentUserRole);

        messageWrapper.className = `d-flex flex-column mb-3 ${isCurrentUser ? 'align-items-end' : 'align-items-start'}`;
        messageWrapper.dataset.messageId = message.id;

        const replyButtonHtml = `<button class="btn btn-sm btn-outline-secondary ms-2 reply-message-btn" data-message-id="${message.id}" title="Reply"><i class="bi bi-reply-fill"></i></button>`;
        const deleteButtonHtml = isAdmin ? `<button class="btn btn-sm btn-outline-danger ms-1 delete-message-btn" data-message-id="${message.id}" title="Delete"><i class="bi bi-trash-fill"></i></button>` : '';

        const replyContextHtml = message.reply_to_message_id ? `
            <div class="reply-context p-2 mb-1 rounded" style="background-color: #f0f0f0; border-left: 3px solid #0d6efd;">
                <div class="fw-bold small">${message.replied_to_sender_first_name} ${message.replied_to_sender_last_name}</div>
                <div class="text-muted small text-truncate">${message.replied_to_content}</div>
            </div>` : '';

        messageWrapper.innerHTML = `
            ${replyContextHtml}
            <div class="sender-name small text-muted mb-1">${isCurrentUser ? 'You' : `${message.first_name} ${message.last_name}`}</div>
            <div class="d-flex align-items-center">
                <div class="message-bubble p-2 rounded ${isCurrentUser ? 'bg-primary text-white' : 'bg-light'}">
                    <div class="message-content">${message.content}</div>
                    <div class="message-timestamp small text-muted mt-1" style="font-size: 0.75rem;">
                        ${new Date(message.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                    </div>
                </div>
                <div class="message-actions d-flex">${replyButtonHtml}${deleteButtonHtml}</div>
            </div>`;
        messageList.appendChild(messageWrapper);
    }

    async function handleSendMessage(e) {
        e.preventDefault();
        const content = messageInput.value.trim();
        if (!content || !currentConversationId) return;

        const messageData = {
            conversation_id: currentConversationId,
            content: content,
            reply_to_message_id: replyingToMessageId
        };

        try {
            const response = await fetch(API_URLS.sendMessage, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(messageData)
            });
            const result = await response.json();

            if (result.success) {
                appendMessage(result.message);
                scrollToBottom();
                messageInput.value = '';
                replyingToMessageId = null;
                replyContextContainer.classList.add('d-none');
                loadConversations();
            } else {
                alert('Failed to send message: ' + (result.details || result.error));
            }
        } catch (error) {
            alert('A network error occurred while trying to send the message.');
        }
    }

    async function handleUserSearch() {
        const query = userSearchInput.value.trim();
        if (query.length < 2) {
            userSearchResults.innerHTML = '';
            return;
        }

        try {
            const response = await fetch(`${API_URLS.searchUsers}?q=${query}`);
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

    async function startNewConversation(recipientId) {
        try {
            const response = await fetch(API_URLS.createConversation, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ recipient_id: recipientId })
            });
            const result = await response.json();

            if (result.success) {
                newUserModal.hide();
                await loadConversations();
                const participantName = result.conversation.display_name;
                const initials = participantName.split(' ').map(n => n[0]).join('').toUpperCase();
                selectConversation(result.conversation.conversation_id, participantName, initials, result.conversation.is_group);
            } else {
                alert('Error starting conversation: ' + result.error);
            }
        } catch (error) {
            console.error('Error creating conversation:', error);
        }
    }

    async function deleteMessage(messageId, messageElement) {
        try {
            const response = await fetch(API_URLS.deleteMessage, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message_id: messageId })
            });
            const result = await response.json();
            if (result.success) {
                messageElement.remove();
            } else {
                alert('Error deleting message: ' + result.error);
            }
        } catch (error) {
            alert('A network error occurred while trying to delete the message.');
        }
    }

    function scrollToBottom() {
        messageList.scrollTop = messageList.scrollHeight;
    }

    // --- Event Listeners ---
    messageForm.addEventListener('submit', handleSendMessage);
    userSearchInput.addEventListener('input', handleUserSearch);

    messageList.addEventListener('click', function(e) {
        const targetButton = e.target.closest('button');
        if (!targetButton) return;

        const messageId = targetButton.dataset.messageId;
        const messageWrapper = targetButton.closest('.d-flex.flex-column');

        if (targetButton.classList.contains('delete-message-btn')) {
            if (confirm('Are you sure you want to delete this message permanently?')) {
                deleteMessage(messageId, messageWrapper);
            }
        } else if (targetButton.classList.contains('reply-message-btn')) {
            const messageContent = messageWrapper.querySelector('.message-content').textContent;
            replyingToMessageId = messageId;
            replyContextText.textContent = messageContent;
            replyContextContainer.classList.remove('d-none');
            messageInput.focus();
        }
    });

    cancelReplyBtn.addEventListener('click', function() {
        replyingToMessageId = null;
        replyContextContainer.classList.add('d-none');
    });

    // --- Initial Load ---
    loadConversations();
});
</script>

<?php
require_once 'includes/footer.php';
?>
