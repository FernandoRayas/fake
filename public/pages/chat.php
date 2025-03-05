<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat en Vivo</title>
    <link rel="stylesheet" href="../styles/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.prod.js"></script>
</head>
<body>
    <div id="app" class="container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2 sidebar">
                <a href="dashboard.php" class="btn btn-danger mb-3">Volver</a>
                <h3>Conversaciones</h3>
                <div>
                    <h4>Docentes</h4>
                    <div v-for="user in masterUsers" :key="user.id" class="user" @click="selectUser(user)">
                        {{ user.username }}
                        <span v-if="user.urgent_count > 0" class="urgent-count">({{ user.urgent_count }} urgentes)</span>
                    </div>
                </div>
                <div>
                    <h4>Estudiantes</h4>
                    <div v-for="user in normalUsers" :key="user.id" class="user" @click="selectUser(user)">
                        {{ user.username }}
                        <span v-if="user.urgent_count > 0" class="urgent-count">({{ user.urgent_count }} urgentes)</span>
                    </div>
                </div>
            </div>
            <div class="col-md-9 col-lg-10">
                <div class="chat-container" v-if="selectedUser">
                    <div class="chat-header">Chat con {{ selectedUser.username }}</div>
                    <div class="chat-body">
                        <div v-if="messages.length === 0">No hay mensajes aún.</div>
                        <div v-for="msg in messages" :key="msg.id" :class="['chat-message', msg.sent ? 'message-sent' : 'message-received']">
                            <div :class="{'urgent-message': msg.priority === 'urgente'}">
                                {{ msg.text }}
                                <span v-if="msg.priority === 'urgente'" class="urgent-flag"> * </span>
                            </div>
                            <div class="message-options">
                                <i :class="{'fas fa-bell': msg.priority !== 'urgente', 'fas fa-bell-slash': msg.priority === 'urgente'}"
                                   @click="toggleUrgency(msg)" class="bell-icon"></i>
                            </div>
                            <div class="status">
                                <span :class="{'text-success': msg.status === 'visto', 'text-danger': msg.status === 'no_leido'}">
                                    {{ msg.status === 'no_leido' ? 'No leído' : msg.status === 'visto' ? 'Visto' : 'En espera' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="chat-footer mt-4">
                        <input type="text" v-model="newMessage" placeholder="Escribe un mensaje...">
                        <button @click="sendMessage">Enviar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
    const { createApp } = Vue;
    createApp({
        data() {
            return {
                users: [],
                messages: [],
                newMessage: '',
                selectedUser: null,
                currentUserId: <?php echo json_encode($user_id); ?>,
            };
        },
        mounted() {
            this.loadUsers();
            setInterval(() => {
                if (this.selectedUser) {
                    this.loadMessages();
                }
            }, 2000);
        },
        computed: {
            masterUsers() {
                return this.users.filter(user => user.role === 'master');
            },
            normalUsers() {
                return this.users.filter(user => user.role === 'user');
            }
        },
        methods: {
            async loadUsers() {
                const response = await fetch("../chat/get_users.php");
                this.users = await response.json();
            },
            async loadMessages() {
                if (!this.selectedUser) return;
                const response = await fetch(`../chat/get_message.php?receiver_id=${this.selectedUser.id}`);
                const data = await response.json();
                this.messages = data.messages.map(msg => ({
                    ...msg,
                    status: msg.status || 'en_espera',
                    sent: msg.sender_id === this.currentUserId,
                    priority: msg.priority || 'normal',
                }));
                await this.markMessagesAsRead();
            },
            async sendMessage() {
                if (this.newMessage.trim() !== '') {
                    let messageData = {
                        message: this.newMessage,
                        receiver_id: this.selectedUser.id
                    };
                    try {
                        const response = await fetch("../chat/chat_backend.php", {
                            method: "POST",
                            body: JSON.stringify(messageData),
                            headers: { "Content-Type": "application/json" }
                        });
                        const result = await response.json();
                        if (result.status === "success") {
                            this.newMessage = '';
                            this.loadMessages();
                        }
                    } catch (error) {
                        console.error("Error enviando mensaje:", error);
                    }
                }
            },
            async selectUser(user) {
                this.selectedUser = user;
                await this.loadMessages();
                try {
                    await fetch("../chat/open_chat.php", {
                        method: "POST",
                        body: JSON.stringify({ receiver_id: user.id }),
                        headers: { "Content-Type": "application/json" }
                    });
                } catch (error) {
                    console.error("Error al abrir el chat:", error);
                }
                await fetch("../chat/update_message_status.php", {
                    method: "POST",
                    body: JSON.stringify({ receiver_id: user.id }),
                    headers: { "Content-Type": "application/json" }
                });
            },
            async markMessagesAsRead() {
                if (!this.selectedUser) return;
                try {
                    await fetch("../chat/update_message_status.php", {
                        method: "POST",
                        body: JSON.stringify({ receiver_id: this.selectedUser.id }),
                        headers: { "Content-Type": "application/json" }
                    });
                } catch (error) {
                    console.error("Error al marcar los mensajes como leídos:", error);
                }
            },
            async toggleUrgency(msg) {
                const newPriority = msg.priority === 'urgente' ? 'normal' : 'urgente';
                try {
                    const response = await fetch("../chat/update_message_priority.php", {
                        method: "POST",
                        body: JSON.stringify({ message_id: msg.id, priority: newPriority }),
                        headers: { "Content-Type": "application/json" }
                    });
                    const result = await response.json();
                    if (result.status === "success") {
                        msg.priority = newPriority;
                    }
                } catch (error) {
                    console.error("Error al cambiar la urgencia:", error);
                }
            }
        }
    }).mount("#app");
    </script>
</body>
</html>
