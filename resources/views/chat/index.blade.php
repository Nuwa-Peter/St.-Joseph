<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Real-time Chat') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="flex" style="height: 75vh;">
                    <!-- User List -->
                    <div class="w-1/3 border-r border-gray-200 overflow-y-auto p-4">
                        <h3 class="text-lg font-semibold mb-4">Conversations</h3>
                        <ul>
                            <!-- Group Chat Link -->
                            <li class="mb-2">
                                <a href="#" class="flex items-center p-2 text-gray-700 rounded-md hover:bg-gray-100 chat-contact" data-channel-type="group" data-channel-name="Teacher Chat" data-receiver-id="null">
                                    <x-heroicon-o-users class="w-6 h-6 mr-3" />
                                    <span>Teacher Chat</span>
                                </a>
                            </li>
                            <hr class="my-2">
                            <!-- DM Links -->
                            @foreach($users as $user)
                            <li class="mb-2">
                                <a href="#" class="flex items-center p-2 text-gray-700 rounded-md hover:bg-gray-100 chat-contact" data-channel-type="dm" data-channel-name="Chat with {{ $user->name }}" data-receiver-id="{{ $user->id }}">
                                    <img class="h-8 w-8 rounded-full object-cover mr-3" src="{{ $user->photo ? asset('storage/' . $user->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&color=7F9CF5&background=EBF4FF' }}" alt="{{ $user->name }}">
                                    <span>{{ $user->name }}</span>
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Chat Area -->
                    <div class="w-2/3 flex flex-col p-4">
                        <h3 id="chat-title" class="text-lg font-semibold mb-4 border-b pb-2">Select a conversation</h3>
                        <div id="chat-window" class="flex-1 overflow-y-auto border border-gray-300 rounded p-4 mb-4 bg-gray-50">
                            <!-- Messages will be loaded here by JS -->
                            <div class="text-center text-gray-500 pt-10">Please select a conversation to start chatting.</div>
                        </div>
                        <form id="chat-form" class="hidden">
                            @csrf
                            <div class="flex">
                                <input type="text" id="message" name="message" class="w-full border-gray-300 rounded-l-md focus:ring-indigo-500 focus:border-indigo-500" placeholder="Type your message..." autocomplete="off" disabled>
                                <button type="submit" class="bg-indigo-500 text-white px-4 py-2 rounded-r-md hover:bg-indigo-600 disabled:bg-indigo-300" disabled>Send</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo/dist/echo.iife.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const authUserId = {{ auth()->id() }};
            let activeChannel = null;
            let activeReceiverId = null;

            const chatWindow = document.getElementById('chat-window');
            const chatTitle = document.getElementById('chat-title');
            const chatForm = document.getElementById('chat-form');
            const messageInput = document.getElementById('message');
            const sendButton = chatForm.querySelector('button[type="submit"]');

            window.Echo = new Echo({
                broadcaster: 'pusher',
                key: '{{ env('VITE_PUSHER_APP_KEY') }}',
                cluster: '{{ env('VITE_PUSHER_APP_CLUSTER') }}',
                forceTLS: true,
                authEndpoint: '/broadcasting/auth'
            });

            function getDmChannelName(receiverId) {
                const ids = [authUserId, parseInt(receiverId)].sort((a, b) => a - b);
                return `dm.${ids[0]}-${ids[1]}`;
            }

            function renderMessages(messages) {
                chatWindow.innerHTML = '';
                messages.forEach(message => {
                    appendMessage(message);
                });
                chatWindow.scrollTop = chatWindow.scrollHeight;
            }

            function appendMessage(message) {
                const messageElement = document.createElement('div');
                messageElement.classList.add('mb-2');
                messageElement.id = `message-${message.id}`;

                const alignmentClass = message.sender.id === authUserId ? 'text-right' : 'text-left';
                const bgColor = message.sender.id === authUserId ? 'bg-indigo-100' : 'bg-gray-200';

                const date = new Date(message.created_at);
                const formattedDate = date.toLocaleString('default', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });

                let deleteButton = '';
                if (message.sender.id === authUserId) {
                    deleteButton = `
                        <button class="delete-message-btn text-gray-400 hover:text-red-500 ml-2" data-message-id="${message.id}" title="Delete Message">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                        </button>
                    `;
                }

                messageElement.innerHTML = `
                    <div class="${alignmentClass}">
                        <div class="inline-flex items-center p-2 rounded-lg ${bgColor}">
                            <div>
                                <strong class="font-bold">${message.sender.first_name} ${message.sender.last_name}:</strong>
                                <span class="message-content">${message.content}</span>
                                <div class="text-xs text-gray-500 mt-1">${formattedDate}</div>
                            </div>
                            ${deleteButton}
                        </div>
                    </div>`;
                chatWindow.appendChild(messageElement);
                chatWindow.scrollTop = chatWindow.scrollHeight;
            }

            function loadConversation(type, name, receiverId) {
                if (activeChannel) {
                    window.Echo.leave(activeChannel);
                }

                chatTitle.textContent = name;
                chatWindow.innerHTML = '<div class="text-center text-gray-500 pt-10">Loading messages...</div>';
                chatForm.classList.remove('hidden');
                messageInput.disabled = false;
                sendButton.disabled = false;
                activeReceiverId = receiverId;

                let fetchUrl = '';
                if (type === 'group') {
                    activeChannel = 'chat';
                    fetchUrl = '{{ route('chat.group-messages') }}';
                } else { // dm
                    activeChannel = getDmChannelName(receiverId);
                    fetchUrl = `/dm/${receiverId}`;
                }

                axios.get(fetchUrl).then(response => {
                    renderMessages(response.data);
                });

                window.Echo.private(activeChannel)
                    .listen('MessageSent', (e) => {
                        const incomingChannel = e.message.channel;
                        if (incomingChannel === activeChannel) {
                             appendMessage(e.message);
                        }
                    });
            }

            document.querySelectorAll('.chat-contact').forEach(contact => {
                contact.addEventListener('click', function(e) {
                    e.preventDefault();
                    const type = this.dataset.channelType;
                    const name = this.dataset.channelName;
                    const receiverId = this.dataset.receiverId !== 'null' ? parseInt(this.dataset.receiverId) : null;

                    document.querySelectorAll('.chat-contact').forEach(c => c.classList.remove('bg-gray-200'));
                    this.classList.add('bg-gray-200');

                    loadConversation(type, name, receiverId);
                });
            });

            chatForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const messageContent = messageInput.value;
                if (messageContent.trim() === '') return;

                let postUrl = '';
                if (activeReceiverId) {
                    postUrl = `/dm/${activeReceiverId}`;
                } else {
                    postUrl = '{{ route('teacher.chat.send') }}';
                }

                axios.post(postUrl, { message: messageContent })
                    .then(response => {
                        messageInput.value = '';
                        if (response.data.message) {
                           appendMessage(response.data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error sending message:', error);
                        alert('There was an error sending your message.');
                    });
            });

            chatWindow.addEventListener('click', function(e) {
                const deleteBtn = e.target.closest('.delete-message-btn');
                if (deleteBtn) {
                    e.preventDefault();
                    const messageId = deleteBtn.dataset.messageId;
                    if (confirm('Are you sure you want to delete this message?')) {
                        axios.delete(`/messages/${messageId}`)
                            .then(response => {
                                const messageDiv = document.getElementById(`message-${messageId}`);
                                if (messageDiv) {
                                    messageDiv.querySelector('.message-content').innerHTML = '<em class="text-gray-500">This message was deleted.</em>';
                                    deleteBtn.remove();
                                }
                            })
                            .catch(error => {
                                console.error('Error deleting message:', error);
                                alert('Could not delete the message.');
                            });
                    }
                }
            });

            // Load group chat by default
            const groupChatLink = document.querySelector('.chat-contact[data-channel-type="group"]');
            if (groupChatLink) {
                groupChatLink.click();
            }
        });
    </script>
    @endpush
</x-app-layout>
