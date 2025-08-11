<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Chat Administration') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="flex" style="height: 75vh;">
                    <!-- Conversation List -->
                    <div class="w-1/3 border-r border-gray-200 overflow-y-auto p-4">
                        <h3 class="text-lg font-semibold mb-4">All Conversations</h3>
                        <ul>
                            @foreach($conversations as $convo)
                            <li class="mb-2">
                                <a href="#" class="flex items-center p-2 text-gray-700 rounded-md hover:bg-gray-100 admin-chat-contact" data-channel="{{ $convo['channel'] }}" data-channel-name="{{ $convo['name'] }}">
                                    <x-heroicon-o-chat-bubble-left-right class="w-6 h-6 mr-3" />
                                    <span>{{ $convo['name'] }}</span>
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Chat Area -->
                    <div class="w-2/3 flex flex-col p-4">
                        <h3 id="chat-title" class="text-lg font-semibold mb-4 border-b pb-2">Select a conversation to view</h3>
                        <div id="chat-window" class="flex-1 overflow-y-auto border border-gray-300 rounded p-4 mb-4 bg-gray-50">
                            <!-- Messages will be loaded here by JS -->
                            <div class="text-center text-gray-500 pt-10">Please select a conversation.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chatWindow = document.getElementById('chat-window');
            const chatTitle = document.getElementById('chat-title');

            function renderMessages(messages) {
                chatWindow.innerHTML = '';
                messages.forEach(message => {
                    const messageElement = document.createElement('div');
                    messageElement.classList.add('mb-2');
                    messageElement.id = `message-${message.id}`;

                    const bgColor = message.deleted_at ? 'bg-red-100' : 'bg-gray-100';

                    const date = new Date(message.created_at);
                    const formattedDate = date.toLocaleString('default', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });

                    const deleteStatus = message.deleted_at ? `<em class="text-red-500">(Soft Deleted)</em>` : '';
                    const content = message.deleted_at ? `<del>${message.content}</del>` : message.content;

                    messageElement.innerHTML = `
                        <div class="p-2 rounded-lg ${bgColor}">
                            <div class="flex justify-between items-center">
                                <strong class="font-bold">${message.sender.name}</strong>
                                <button class="force-delete-btn text-red-500 hover:text-red-700 font-bold" data-message-id="${message.id}" title="Permanently Delete">
                                    &times;
                                </button>
                            </div>
                            <p class="message-content">${content} ${deleteStatus}</p>
                            <div class="text-xs text-gray-500 mt-1">${formattedDate}</div>
                        </div>`;
                    chatWindow.appendChild(messageElement);
                });
                chatWindow.scrollTop = chatWindow.scrollHeight;
            }

            document.querySelectorAll('.admin-chat-contact').forEach(contact => {
                contact.addEventListener('click', function(e) {
                    e.preventDefault();
                    const channel = this.dataset.channel;
                    const channelName = this.dataset.channelName;

                    document.querySelectorAll('.admin-chat-contact').forEach(c => c.classList.remove('bg-gray-200'));
                    this.classList.add('bg-gray-200');

                    chatTitle.textContent = `Viewing: ${channelName}`;
                    chatWindow.innerHTML = '<div class="text-center text-gray-500 pt-10">Loading messages...</div>';

                    axios.get(`/admin/chat/${channel}`)
                        .then(response => {
                            renderMessages(response.data);
                        })
                        .catch(error => {
                            console.error('Error fetching conversation:', error);
                            chatWindow.innerHTML = '<div class="text-center text-red-500 pt-10">Could not load messages.</div>';
                        });
                });
            });

            chatWindow.addEventListener('click', function(e) {
                const deleteBtn = e.target.closest('.force-delete-btn');
                if (deleteBtn) {
                    e.preventDefault();
                    const messageId = deleteBtn.dataset.messageId;
                    if (confirm('Are you sure you want to PERMANENTLY delete this message? This cannot be undone.')) {
                        axios.delete(`/admin/chat/messages/${messageId}`)
                            .then(response => {
                                const messageDiv = document.getElementById(`message-${messageId}`);
                                if (messageDiv) {
                                    messageDiv.remove();
                                }
                            })
                            .catch(error => {
                                console.error('Error deleting message:', error);
                                alert('Could not permanently delete the message.');
                            });
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
