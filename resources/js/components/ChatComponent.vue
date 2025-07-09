<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">

                <h1 class="text-lg font-semibold mr-2">    friend name:{{ friend.name }}

                    (<span v-if="isUserOnline " >Online</span>)
                </h1>
                <!-- <span :class="isUserOnline ? 'bg-green-500' : 'bg-gray-400'" class="inline-block h-2 w-2 rounded-full"></span> -->
                <div class="card">
                    <div class="card-body">
                        <div class="container bootstrap snippets bootdeys">
                            <div class="col-md-12">
                                <!-- Panel Chat -->
                                <div class="panel" id="chat">

                                    <div class="panel-body">
                                        <div class="chats" style="
                                        height: 400px;
                                        overflow-y: scroll;"
                                            ref="messagesContainer"
                                        >
                                            <div class="chat"
                                            v-for="message in messages"
                                            :key="message.id"
                                            :class="message.sender_id === friend.id ? 'chat-left' : ''"
                                            >

                                                <div class="chat-body">
                                                    <div class="chat-content">
                                                        <h3>
                                                            {{
                                                                message.sender_id === friend.id ? friend.name : 'ME'
                                                             }}:
                                                        </h3>
                                                        <p>
                                                          {{ message.content }}
                                                        </p>

                                                    </div>
                                                </div>
                                            </div>


                                        </div>
                                    </div>
                                    <div class="panel-footer">
                                        <form>
                                            <div class="input-group">
                                                <input
                                                    @keydown="sendTypingEvent"
                                                    @keyup.enter="sendMessage"
                                                    type="text"
                                                    class="form-control"
                                                    placeholder="Say something"
                                                    v-model="newMessage"
                                                />
                                                <span class="input-group-btn">
                                                    <button
                                                        class="btn btn-primary"
                                                        type="button"
                                                        @click="sendMessage"
                                                    >
                                                        Send
                                                    </button>
                                                </span>
                                            </div>
                                        </form>
                                        <small v-if="isFriendTyping" class="text-gray-700">
                                            {{ friend.name }} is typing...
                                        </small>
                                    </div>
                                </div>
                                <!-- End Panel Chat -->
                            </div>
                        </div>
                    </div>
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
            messages: [],
            newMessage:'',
            isFriendTyping:false,
            isFriendTypingTimer:null,
            isUserOnline: false,
        };
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
        axios.get(`/messages/${this.friend.id}`).then((response) => {
            // console.log(response.data);
            this.messages = response.data;
        });

        Echo.private(`chat.${this.currentUser.id}`)
        .listen("MessageSent", (response) => {
            this.messages.push(response.message);

        })
        .listenForWhisper("typing", (response) => {
            this.isFriendTyping = response.userID === this.friend.id;
            console.log('response',response)

            if (this.isFriendTypingTimer) {
                clearTimeout(this.isFriendTypingTimer);
            }

            this.isFriendTypingTimer = setTimeout(() => {
                this.isFriendTyping = false;
            }, 1000);
        });

        //online
        Echo.join(`presence.chat`)
            .here(users => {
                console.log('users',users)
                this.isUserOnline = users.some(user => user.id === this.friend.id);
            })
            .joining(user => {
                if (user.id === this.friend.id) this.isUserOnline = true;
            })
            .leaving(user => {
                if (user.id === this.friend.id) this.isUserOnline = false;
        });
        // console.log("Component mounted.");
    },
    methods:{
        sendTypingEvent(){
            Echo.private(`chat.${this.friend.id}`).whisper("typing", {
                userID: this.currentUser.id,
            });
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
        sendMessage(){
            if(this.newMessage.length > 0){
                axios
                .post(`/messages/${this.friend.id}`, {
                    content: this.newMessage,
                })
                .then((response) => {
                    this.messages.push(response.data);
                    this.newMessage = "";
                });
            }
        }
    }
};
</script>
<style scoped>

body {
    background:#ddd;
    margin-top:10px;
    }

    .chat-box {
        height: 100%;
        width: 100%;
        background-color: #fff;
        overflow: hidden
    }

    .chats {
        padding: 30px 15px
    }

    .chat-avatar {
        float: right
    }

    .chat-avatar .avatar {
        width: 30px;
            -webkit-box-shadow: 0 2px 2px 0 rgba(0,0,0,0.2),0 6px 10px 0 rgba(0,0,0,0.3);
        box-shadow: 0 2px 2px 0 rgba(0,0,0,0.2),0 6px 10px 0 rgba(0,0,0,0.3);
    }

    .chat-body {
        display: block;

        overflow: hidden
    }

    .chat-body:first-child {
        margin-top: 0
    }

    .chat-content {
        position: relative;
        display: block;
        float: right;
        padding: 8px 15px;
        margin: 0 20px 10px 0;
        clear: both;
        color: #fff;
        background-color: #62a8ea;
        border-radius: 4px;
            -webkit-box-shadow: 0 1px 4px 0 rgba(0,0,0,0.37);
        box-shadow: 0 1px 4px 0 rgba(0,0,0,0.37);
    }

    .chat-content:before {
        position: absolute;
        top: 10px;
        right: -10px;
        width: 0;
        height: 0;
        content: '';
        border: 5px solid transparent;
        border-left-color: #62a8ea
    }

    .chat-content>p:last-child {
        margin-bottom: 0
    }

    .chat-content+.chat-content:before {
        border-color: transparent
    }

    .chat-time {
        display: block;
        margin-top: 8px;
        color: rgba(255, 255, 255, .6)
    }

    .chat-left .chat-avatar {
        float: left
    }

    .chat-left .chat-body {
        margin-right: 0;

    }

    .chat-left .chat-content {
        float: left;
        margin: 0 0 10px 20px;
        color: #76838f;
        background-color: #dfe9ef
    }

    .chat-left .chat-content:before {
        right: auto;
        left: -10px;
        border-right-color: #dfe9ef;
        border-left-color: transparent
    }

    .chat-left .chat-content+.chat-content:before {
        border-color: transparent
    }

    .chat-left .chat-time {
        color: #a3afb7
    }

    .panel-footer {
        padding: 0 30px 15px;
        background-color: transparent;
        border-top: 1px solid transparent;
        border-bottom-right-radius: 3px;
        border-bottom-left-radius: 3px;
    }
    .avatar img {
        width: 100%;
        max-width: 100%;
        height: auto;
        border: 0 none;
        border-radius: 1000px;
    }
    .chat-avatar .avatar {
        width: 30px;
    }
    .avatar {
        position: relative;
        display: inline-block;
        width: 40px;
        white-space: nowrap;
        border-radius: 1000px;
        vertical-align: bottom;
    }
</style>
