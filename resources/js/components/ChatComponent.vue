<template>
    <div class="flex h-screen bg-gray-100">
        <!-- Left Sidebar - Friends List -->
        <div class="w-1/3 bg-white border-r border-gray-200 flex flex-col">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-xl font-semibold text-gray-800">Messages</h2>
                <p class="text-sm text-gray-500 mt-1">{{ onlineUsers.length }} online</p>
            </div>

            <!-- Search Bar -->
            <div class="px-4 py-3 border-b border-gray-200">
                <div class="relative flex items-center">
                    <input
                        type="text"
                        placeholder="  Search conversations..."
                        v-model="searchQuery"
                        class="w-full pl-10 pr-4 py-2 px-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                    <svg class="absolute left-4 top-2.5 h-5 w-5 text-gray-400 pl-" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <!-- Friends List -->
            <div class="flex-1 overflow-y-auto">
                <div
                    v-for="user in filteredUsers"
                    :key="user.id"
                    @click="selectFriend(user)"
                    class="flex items-center px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 transition-colors"
                    :class="{ 'bg-blue-50 border-blue-200': selectedFriend && selectedFriend.id === user.id }"
                >
                    <div class="relative flex-shrink-0">
                        <div class="h-12 w-12 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center">
                            <span class="text-white font-semibold text-lg">{{ user.name.charAt(0).toUpperCase() }}</span>
                        </div>
                        <span
                            class="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-white"
                            :class="isUserOnlineInList(user.id) ? 'bg-green-400' : 'bg-gray-400'"
                        ></span>
                    </div>
                    <div class="ml-3 flex-1 min-w-0">
                        <div class="flex items-center justify-between cursor-pointer">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ user.name }}
                            <span v-if="user.unread_messages_count > 0" class="unread-msg">
                                {{ user.unread_messages_count }}
                            </span>
                            </p>
                            <span v-if="user.lastMessage" class="text-xs text-gray-500">
                                {{ formatTime(user.lastMessage.created_at) }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 truncate">
                            {{ user.lastMessage ? user.lastMessage.content : 'No messages yet' }}
                        </p>
                        <div class="flex items-center mt-1">
                            <span class="text-xs text-gray-400">{{ user.email }}</span>
                            <span
                                v-if="isUserTypingInList(user.id)"
                                class="ml-2 text-xs text-blue-500 animate-pulse"
                            >
                                typing...
                            </span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Right Panel - Chat Area -->
        <div class="flex-1 flex flex-col">
            <!-- Chat Header -->
            <div v-if="selectedFriend" class="px-6 py-6 border-b border-gray-200 bg-white flex items-center justify-between">
                <div class="flex items-center">
                    <div class="relative">
                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center">
                            <span class="text-white font-semibold">{{ selectedFriend.name.charAt(0).toUpperCase() }}</span>
                        </div>
                        <span
                            class="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-white"
                            :class="isUserOnline ? 'bg-green-400' : 'bg-gray-400'"
                        ></span>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-semibold text-gray-900">{{ selectedFriend.name }}</h3>
                        <p class="text-sm text-gray-500">
                            {{ isUserOnline ? 'Online' : 'Last seen recently' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!selectedFriend" class="flex-1 flex items-center justify-center bg-gray-50">
                <div class="text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No conversation selected</h3>
                    <p class="mt-1 text-sm text-gray-500">Choose a conversation from the sidebar to start messaging.</p>
                </div>
            </div>

            <!-- Chat Messages -->
            <div
                v-if="selectedFriend"
                class="flex-1 overflow-y-auto px-6 py-4 bg-gray-50"
                ref="messagesContainer"
            >
                <div class="space-y-4">
                    <div
                        v-for="message in messages"
                        :key="message.id"
                        class="flex"
                        :class="message.sender_id === selectedFriend.id ? 'justify-start' : 'justify-end'"
                    >
                        <div
                            class="max-w-[70%] break-words rounded-2xl px-4 py-2 shadow-sm"
                            :class="message.sender_id === selectedFriend.id ?
                                'left-side-chat rounded-bl-md' :
                                'right-side-chat '"
                        >
                            <p class="text-sm whitespace-pre-wrap">{{ message.content }}</p>
                            <p class="text-xs mt-1 opacity-70">
                                {{ formatMessageTime(message.created_at) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chat Input -->
            <div v-if="selectedFriend" class="border-t bg-white px-6 py-4">
                <div class="relative flex items-end space-x-3">
                    <div class="flex-1 relative">
                        <textarea
                            v-model="newMessage"
                            @keydown="handleKeyDown"
                            @input="sendTypingEvent"
                            placeholder="Type a message..."
                            rows="1"
                            class="w-full resize-none rounded-2xl border border-gray-300 bg-gray-50 px-4 py-3 pr-12 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 text-sm max-h-32"
                            style="min-height: 44px;"
                        ></textarea>
                    </div>
                    <button
                        @click="sendMessage"
                        :disabled="!newMessage.trim()"
                        class="p-3 send-btn rounded-full transition-colors"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                    </button>
                </div>
                <div class="mt-2 h-4">
                    <p v-if="isFriendTyping" class="text-xs text-gray-500 animate-pulse">
                        {{ selectedFriend.name }} is typing...
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";
export default {
    props: ["currentUser", "friend"],
    data() {
        return {
            selectedFriend: null,
            users: [],
            messages: [],
            newMessage: '',
            searchQuery: '',
            isFriendTyping: false,
            isFriendTypingTimer: null,
            onlineUsers: [],
            typingUsers: [],
            presenceChannel: null,
        };
    },
    computed: {
        filteredUsers() {
            if (!this.searchQuery) return this.users;
            return this.users.filter(user =>
                user.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                user.email.toLowerCase().includes(this.searchQuery.toLowerCase())
            );
        },
        isUserOnline() {
            return this.selectedFriend ? this.onlineUsers.some(user => user.id === this.selectedFriend.id) : false;
        }
    },
    watch: {
        messages: {
            handler() {
                this.$nextTick(() => {
                    this.scrollToBottom();
                });
            },
            deep: true,
        },
    },
    mounted() {
        // Set initial friend if passed as prop
        if (this.friend) {
            this.selectedFriend = this.friend;
        }

        // Load all users for the friends list
        this.loadUsers();

        // Setup real-time listeners
        this.setupEchoListeners();

        // Load messages for selected friend
        if (this.selectedFriend) {
            this.loadMessages();
        }
    },
    methods: {
        loadUsers() {
            axios.get('/api/users')
                .then(response => {
                    this.users = response.data.filter(user => user.id !== this.currentUser.id);
                })
                .catch(error => {
                    console.error('Error loading users:', error);
                    if (error.response && error.response.status === 401) {
                        // Authentication error - reload the page to redirect to login
                        window.location.reload();
                    } else if (error.response && error.response.status === 419) {
                        // CSRF token mismatch - reload the page
                        window.location.reload();
                    } else {
                        // Other errors
                        alert('Failed to load users. Please refresh the page.');
                    }
                });
        },

        selectFriend(user) {
            this.selectedFriend = user;
            this.loadMessages();
        },

        loadMessages() {
            if (!this.selectedFriend) return;

            axios.get(`/messages/${this.selectedFriend.id}`)
                .then((response) => {
                    this.messages = response.data;
                })
                .catch(error => {
                    console.error('Error loading messages:', error);
                    if (error.response && (error.response.status === 401 || error.response.status === 419)) {
                        window.location.reload();
                    }
                });
        },

        setupEchoListeners() {
            // Listen for incoming messages on user's personal channel
            Echo.channel(`chat.${this.currentUser.id}`)
                .listen("MessageSent", (response) => {
                    if (this.selectedFriend && response.message.sender_id === this.selectedFriend.id) {
                        this.messages.push(response.message);
                    }
                });

            // Join presence channel for online status and typing indicators
            this.presenceChannel = Echo.join(`presence.chat`);
            this.presenceChannel.here(users => {
                this.onlineUsers = users;
            });
            this.presenceChannel.joining(user => {
                this.onlineUsers.push(user);
            });
            this.presenceChannel.leaving(user => {
                this.onlineUsers = this.onlineUsers.filter(u => u.id !== user.id);
            });
            this.presenceChannel.listenForWhisper("typing", (response) => {
                if (this.selectedFriend && response.userID === this.selectedFriend.id) {
                    this.isFriendTyping = true;

                    if (this.isFriendTypingTimer) {
                        clearTimeout(this.isFriendTypingTimer);
                    }

                    this.isFriendTypingTimer = setTimeout(() => {
                        this.isFriendTyping = false;
                    }, 1000);
                }
            });
        },

        sendTypingEvent() {
            if (this.selectedFriend && this.presenceChannel) {
                // Use presence channel for whisper functionality
                this.presenceChannel.whisper("typing", {
                    userID: this.currentUser.id,
                    targetUserID: this.selectedFriend.id,
                });
            }
        },

        handleKeyDown(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                this.sendMessage();
            }
        },

        scrollToBottom() {
            const messagesContainer = this.$refs.messagesContainer;
            if (messagesContainer) {
                messagesContainer.scrollTo({
                    top: messagesContainer.scrollHeight,
                    behavior: "smooth",
                });
            }
        },

        sendMessage() {
            if (!this.selectedFriend || !this.newMessage.trim()) return;

            axios
                .post(`/messages/${this.selectedFriend.id}`, {
                    content: this.newMessage.trim(),
                })
                .then((response) => {
                    this.messages.push(response.data);
                    this.newMessage = "";
                })
                .catch(error => {
                    console.error('Error sending message:', error);
                    if (error.response && (error.response.status === 401 || error.response.status === 419)) {
                        window.location.reload();
                    } else {
                        alert('Failed to send message. Please try again.');
                    }
                });
        },

        isUserOnlineInList(userId) {
            return this.onlineUsers.some(user => user.id === userId);
        },

        isUserTypingInList(userId) {
            return this.typingUsers.includes(userId);
        },

        formatTime(timestamp) {
            if (!timestamp) return '';
            const date = new Date(timestamp);
            return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        },

        formatMessageTime(timestamp) {
            const date = new Date(timestamp);
            const now = new Date();
            const diffInHours = (now - date) / (1000 * 60 * 60);

            if (diffInHours < 24) {
                return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            } else {
                return date.toLocaleDateString();
            }
        }
    }
};
</script>

<style scoped>
.right-side-chat{
    background-color: skyblue;
    color:black;
    margin-bottom: 10px;
    border-radius: 10px;
}
.left-side-chat{
    background-color: #62f5da;
    color:black;
    margin-bottom: 10px;
    border-radius: 10px;
}
.cursor-pointer{
    cursor: pointer;
}
.unread-msg{
    background-color: red;
    color:white;
    padding: 2px 4px;
    border-radius: 5px;
    font-size: 12px;
    font-weight: bold;
    margin-left: 5px;
}
.send-btn{
    color: white;
    font-size: 25px;
    align-items: center;
    cursor: pointer;
    background-color: green;
    border-radius: 50%;
    padding: 10px;
    width: 40px;
    height: 40px;
    display: flex;
    margin-left: 10px;
    align-items: center;
}
/* Custom scrollbar */
::-webkit-scrollbar {
    width: 6px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: #a1a1a1;
}

/* Auto-resize textarea */
textarea {
    field-sizing: content;
}
</style>
