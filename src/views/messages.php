<?php

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once __DIR__ . '/../../src/includes/header.php';
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

<!-- This script will be created in the next step -->
<script src="assets/js/messaging.js"></script>

<?php
require_once __DIR__ . '/../../src/includes/footer.php';
?>
