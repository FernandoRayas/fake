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
    <style>
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(to top, #BDFFF3, #4AC29A);
            display: flex;
            height: 100vh;
        }
        .sidebar {
            background: #2C3E50;
            color: white;
            padding: 20px;
            height: 100vh;
            overflow-y: auto;
            z-index: 10;
        }
        .sidebar h3 {
            text-align: center;
            margin-bottom: 20px;
        }
        .user {
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid #34495E;
        }
        .user:hover {
            background: #34495E;
        }
        .chat-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            height: 100%;
            overflow: hidden;
            padding: 20px;
        }
        .chat-header {
            font-size: 1.5em;
            margin-bottom: 20px;
        }
        .chat-body {
            height: calc(100vh - 200px); /* Ajusta la altura total */
            overflow-y: auto; /* Scroll cuando haya muchos mensajes */
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: white;
            box-sizing: border-box; /* Asegura que padding y border se cuenten */
            display: flex; /* Flexbox para controlar el layout */
            flex-direction: column; /* Apila los mensajes verticalmente */
        }
        .chat-footer {
            display: flex;
            justify-content: space-between;
        }
        .chat-message {
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
            word-wrap: break-word; /* Para manejar palabras largas */
            display: block; /* Los mensajes se apilan uno encima del otro */
            max-width: 70%; /* Limita el ancho del mensaje al 70% */
            white-space: pre-wrap; /* Mantiene los saltos de línea en el mensaje */
        }
        .message-sent {
            background: #007bff;
            color: white;
            text-align: right; /* Alinea el mensaje a la derecha */
            margin-left: auto; /* Mueve el mensaje a la derecha */
        }
        .message-received {
            background: #f1f1f1;
            text-align: left; /* Alinea el mensaje a la izquierda */
            margin-right: auto; /* Mueve el mensaje a la izquierda */
        }
        input[type="text"] {
            width: 80%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        button {
            padding: 10px;
            border-radius: 5px;
            background: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.prod.js"></script>
</head>
<body>
    <div id="app" class="container-fluid">
        <div class="row">
            <!-- Sidebar de usuarios -->
            <div class="col-md-3 col-lg-2 sidebar">
                <a href="dashboard.php" class="btn btn-danger mb-3">Volver</a>
                <h3>Usuarios</h3>
                <div v-for="user in users" :key="user.id" class="user" @click="selectUser(user)">
                    {{ user.username }}
                </div>
            </div>

            <!-- Contenedor de chat -->
            <div class="col-md-9 col-lg-10">
                <div class="chat-container" v-if="selectedUser">
                    <div class="chat-header">Chat con {{ selectedUser.username }}</div>
                    <div class="chat-body">
                        <div v-if="messages.length === 0">No hay mensajes aún.</div>
                        <div v-for="msg in messages" :key="msg.sender_id + msg.text" :class="['chat-message', msg.sent ? 'message-sent' : 'message-received']">
                            <div v-if="!msg.sent"></div>{{ msg.text }}
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
            currentUserId: <?php echo json_encode($user_id); ?>
        };
    },
    mounted() {
        this.loadUsers();
        setInterval(() => {
            if (this.selectedUser) this.loadMessages();
        }, 2000);
    },
    methods: {
        async loadUsers() {
            const response = await fetch("../../chat/get_users.php");
            this.users = await response.json();
        },
        async loadMessages() {
            if (!this.selectedUser) return;
            const response = await fetch(`../../chat/get_message.php?receiver_id=${this.selectedUser.id}`);
            const data = await response.json();
            // console.log(data);  // Verifica que el formato sea el esperado
            this.messages = [...data.messages];  // Usar spread operator para forzar la actualización
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
                    console.log("Respuesta del servidor:", result);

                    if (result.status === "success") {
                        this.newMessage = ''; // Limpia el campo de texto
                        setTimeout(() => this.loadMessages(), 200); // Espera un momento y recarga
                    }
                } catch (error) {
                    console.error("Error enviando mensaje:", error);
                }
            }
        },
        selectUser(user) {
            this.selectedUser = user;
            this.loadMessages();
        }
    }
}).mount("#app");
</script>

</body>
</html>
