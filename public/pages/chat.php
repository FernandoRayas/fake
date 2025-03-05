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
                <!-- Breadcrumb -->
                <div class="container mt-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-white p-2 rounded">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Chat</li>
                    </ol>
                </nav>
                </div>
                <a href="dashboard.php" class="btn btn-danger mb-3">Volver</a>
                <div class="conversations-header mb-2">
                    <h3>Conversaciones</h3>
                    <!-- Cuadro de búsqueda agregado debajo de "Conversaciones" -->
                    <input type="text" v-model="searchQuery" placeholder="Buscar por nombre..." class="form-control mt-2">
                </div>
                <div>
                    <h4>Docentes</h4>
                    <div v-for="user in filteredMasterUsers" :key="user.id" class="user" @click="selectUser(user)">
                        {{ user.username }}
                        <span v-if="user.urgent_count > 0" class="urgent-count">({{ user.urgent_count }} urgentes)</span>
                    </div>
                </div>

                <div>
                    <h4>Estudiantes</h4>
                    <div v-for="user in filteredNormalUsers" :key="user.id" class="user" @click="selectUser(user)">
                        {{ user.username }}
                        <span v-if="user.urgent_count > 0" class="urgent-count">({{ user.urgent_count }} urgentes)</span>
                    </div>
                </div>
            </div>
            <div class="col-md-9 col-lg-10">
                <div class="chat-container" v-if="selectedUser">
                    <div class="chat-header">Chat con {{ selectedUser.username }}</div>
                    <div class="filters">
                        <select v-model="selectedCategory">
                            <option value="Urgente">Urgente</option>
                            <option value="Importante">Importante</option>
                        </select>
                        <button class="btn btn-primary mx-2 mb-1 btn-sm" @click="applyFilter">Aplicar Filtro</button>
                        <button class="btn btn-secondary ms-2 mb-1 btn-sm" @click="removeFilter">Quitar Filtro</button>
                    </div>
                    <div class="chat-body">
                        <div v-if="messages.length === 0">No hay mensajes aún.</div>
                        <div v-for="msg in filteredMessages" :key="msg.id" :class="['chat-message', msg.sent ? 'message-sent' : 'message-received']">
                            <div :class="{'urgent-message': msg.priority === 'urgente'}">
                                {{ msg.text }}
                                <span v-if="msg.category === 'Importante'" style="color: blue; font-weight: bold;">* </span>
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
                        <select v-model="newCategory">
                            <option value="General">General</option>
                            <option value="Importante">Importante</option>
                        </select>
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
                newCategory: 'General', // Categoría predeterminada
                selectedUser: null,
                conversations: [ /* Lista de tus conversaciones con nombres de personas */ ],
                searchQuery: '',  // Almacena el texto de búsqueda
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
            },
            // Filtrar los usuarios de rol 'master' (docentes) con la búsqueda
            filteredMasterUsers() {
                return this.masterUsers.filter(user => 
                    user.username.toLowerCase().includes(this.searchQuery.toLowerCase())
                );
            },
            // Filtrar los usuarios de rol 'user' (estudiantes) con la búsqueda
            filteredNormalUsers() {
                return this.normalUsers.filter(user => 
                    user.username.toLowerCase().includes(this.searchQuery.toLowerCase())
                );
            },
        },
        methods: {
            async loadUsers() {
                const response = await fetch("../chat/get_users.php");
                const users = await response.json();
                // Filtrar usuarios para excluir a los que tienen rol "admin"
                this.users = users.filter(user => user.role !== 'admin');
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
                    category: msg.category || 'General', // Asegúrate de que la categoría esté presente
                }));
                this.applyFilter(); // Aplica el filtro después de cargar los mensajes
                // Llamar a la función para marcar los mensajes como leídos
                await this.markMessagesAsRead();
            },
            applyFilter() {
                if (this.selectedCategory === 'Urgente') {
                    this.filteredMessages = this.messages.filter(msg => msg.priority === 'urgente');
                } else if (this.selectedCategory === 'General') {
                    this.filteredMessages = this.messages.filter(msg => msg.category === 'General');
                } else if (this.selectedCategory === 'Importante') {
                    this.filteredMessages = this.messages.filter(msg => msg.category === 'Importante');
                } else {
                    this.filteredMessages = this.messages;
                }
                // console.log(this.filteredMessages);
            },
            removeFilter() {
                this.filteredMessages = this.messages;  // Esto reseteará los mensajes a la lista completa
                this.selectedCategory = '';  // Restablecer la categoría seleccionada (opcional)
            },
            async sendMessage() {
                if (this.newMessage.trim() !== '') {
                    let messageData = {
                        message: this.newMessage,
                        receiver_id: this.selectedUser.id,
                        category: this.newCategory,
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
                            this.newCategory = 'General'; // Resetear categoría al enviar
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
