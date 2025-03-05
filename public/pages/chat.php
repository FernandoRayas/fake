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
    <link rel="stylesheet" href="css/styles.css">
    <!-- Agregamos Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../styles/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.prod.js"></script>
</head>
<body>
    <div id="app" class="container-fluid">
        <div class="row">
            <!-- Sidebar de usuarios -->
            <div class="col-md-3 col-lg-2 sidebar">
                <a href="dashboard.php" class="btn btn-danger mb-3">Volver</a>
                <h3>Usuarios</h3>
                
                <div>
                    <h4>Docentes</h4>
                    <div v-for="user in masterUsers" :key="user.id" class="user" @click="selectUser(user)">
                        {{ user.username }}
                    </div>
                </div>

                <div>
                    <h4>Estudiantes</h4>
                    <div v-for="user in normalUsers" :key="user.id" class="user" @click="selectUser(user)">
                        {{ user.username }}
                    </div>
                </div>
            </div>

            <!-- Contenedor de chat -->
            <div class="col-md-9 col-lg-10">
                <div class="chat-container" v-if="selectedUser">
                    <div class="chat-header">Chat con {{ selectedUser.username }}</div>
                    <div class="chat-body">
                        <div v-if="messages.length === 0">No hay mensajes aún.</div>
                        <div v-for="msg in messages" :key="msg.sender_id + msg.text" :class="['chat-message', msg.sent ? 'message-sent' : 'message-received']">
                            <div :class="{'urgent-message': msg.priority === 'urgente'}">
                                <!-- Mensaje de texto -->
                                {{ msg.text }}
                                <!-- Asterisco y la etiqueta "Urgente" se muestra si el mensaje tiene la prioridad urgente -->
                                <span v-if="msg.priority === 'urgente'" class="urgent-flag"> * </span>
                            </div>

                            <!-- Contenedor para la campana, afuera del mensaje -->
                            <div :class="['message-options', { 'message-sent': msg.sent, 'message-received': !msg.sent }]">
                                <i 
                                    :class="{'fas fa-bell': msg.priority !== 'urgente', 'fas fa-bell-slash': msg.priority === 'urgente'}"
                                    @click="toggleUrgency(msg)"
                                    class="bell-icon"></i>
                            </div>

                            <div class="status">
                                <span :class="{'text-success': msg.status === 'visto', 'text-danger': msg.status === 'no_leido'}">
                                    {{ msg.status === 'no_leido' ? 'No leído' : msg.status === 'visto' ? 'Visto' : msg.status === 'respondido' ? 'Respondido' : 'En espera'}}
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
                    this.markMessagesAsRead();  // Asegúrate de que se ejecute aquí también
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
                const response = await fetch("../../chat/get_users.php");
                this.users = await response.json();
            },
            async openChat() {
                // Verifica si el receptor es el usuario actual
                if (this.selectedUser.id === this.currentUserId) {
                return; // No hacemos nada si el usuario no es el receptor
                }
                
                // Cuando el receptor abre el chat, marcamos los mensajes como leídos
                await this.markMessagesAsRead();
            },
            async loadMessages() {
                if (!this.selectedUser) return;

                // Cargamos los mensajes del backend
                const response = await fetch(`../../chat/get_message.php?receiver_id=${this.selectedUser.id}`);
                const data = await response.json();

                
                // Asignamos los mensajes a la variable messages
                this.messages = data.messages.map(msg => ({
                    ...msg,
                    status: msg.status || 'en_espera', // Asignamos estado "en espera" si no tiene
                    sent: msg.sender_id === this.currentUserId, // Si el sender_id es el mismo que el usuario logueado, es un mensaje enviado
                    priority: msg.priority || 'normal',
                }));
                
                // console.log(this.messages);
                // Marcar los mensajes como leídos cuando se abre el chat
                await this.markMessagesAsRead(); // Llamamos a la función para marcar los mensajes como leídos
            },
            async sendMessage() {
                if (this.newMessage.trim() !== '') {
                    let messageData = {
                        message: this.newMessage,
                        receiver_id: this.selectedUser.id
                    };

                    try {
                        const response = await fetch("../../chat/chat_backend.php", {
                            method: "POST",
                            body: JSON.stringify(messageData),
                            headers: { "Content-Type": "application/json" }
                        });

                        const result = await response.json();

                        if (result.status === "success") {
                            this.newMessage = ''; // Limpiar el campo de texto
                            this.loadMessages(); // Recargar los mensajes
                        }
                    } catch (error) {
                        console.error("Error enviando mensaje:", error);
                    }
                }
            },
            selectUser(user) {
                this.selectedUser = user;
                console.log("Usuario seleccionado:", user);
                this.loadMessages();  // Cargar los mensajes cuando se selecciona un usuario
            },
            async markMessagesAsRead() {
                try {

                // Enviar una solicitud al servidor para actualizar los mensajes como leídos
                const response = await fetch('/chat/update_message_status.php', {
                    method: 'POST',
                    headers: {
                    'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                    receiver_id: this.selectedUser.id, // El receptor
                    }),
                });

                const result = await response.json();
                if (response.ok && result.status === 'success') {
                    this.loadMessages(); // Recargar los mensajes
                } else {
                    // console.log("No se marcaron mensajes como leídos");
                }
                } catch (error) {
                console.error("Error al marcar los mensajes como leídos:", error);
                }
            },
            async toggleUrgency(msg) {
                console.log(msg);
                const newPriority = msg.priority === 'urgente' ? 'normal' : 'urgente';

                if (!msg.id || !msg.priority) {
                    console.error("Mensaje sin id o prioridad.");
                    return;
                }

                try {
                    const response = await fetch("../../chat/update_message_priority.php", {
                        method: "POST",
                        body: JSON.stringify({
                            message_id: msg.id,  // Asegúrate de que 'msg.id' existe
                            priority: newPriority // Asegúrate de que 'newPriority' es 'urgente' o 'normal'
                        }),
                        headers: { "Content-Type": "application/json" }
                    });

                    const result = await response.json();

                    if (result.status === "success") {
                        msg.priority = newPriority;  // Actualizamos localmente el mensaje
                    } else {
                        console.error("No se pudo actualizar la urgencia", result);
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
