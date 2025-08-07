<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Paneli</title>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@600&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Fredoka', sans-serif;
        }

        .chat-container {
            display: flex;
            height: 100vh;
            border: 5px solid #ccc;
        }

        .users-panel {
            width: 25%;
            background: #fff;
            border-right: 2px solid #000;
            padding: 20px;
            box-sizing: border-box;
            overflow-y: auto;
        }

        .users-panel .user {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            font-weight: bold;
            cursor: pointer;
        }

        .users-panel .user img {
            width: 30px;
            height: 30px;
        }

        .chat-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 20px;
            background: #fff;
        }

        .messages {
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .message {
            padding: 10px 15px;
            border-radius: 20px;
            max-width: 70%;
            word-break: break-word;
            font-weight: bold;
        }

        .message.left {
            align-self: flex-start;
            background-color: #f0f0f0;
            color: #000;
        }

        .message.right {
            align-self: flex-end;
            background-color: #d1e7ff;
            color: #000;
        }

        .send-box {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .send-box input {
            flex: 1;
            padding: 10px;
            border-radius: 20px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        .send-box button {
            background: dodgerblue;
            color: white;
            padding: 10px 20px;
            border-radius: 20px;
            border: none;
            font-weight: bold;
            cursor: pointer;
            font-size: 16px;
        }
        @media (max-width: 768px) {
            .chat-container {
                flex-direction: column;
            }

            .users-panel {
                width: 100%;
                border-right: none;
                border-bottom: 2px solid #000;
                height: auto;
                display: flex;
                overflow-x: auto;
            }

            .users-panel .user {
                flex: 0 0 auto;
                margin-right: 15px;
            }

            .chat-panel {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
<script>
    localStorage.setItem("id", "<?= $_SESSION['user_id'] ?>");
    localStorage.setItem("username", "<?= $_SESSION['username'] ?>");
    localStorage.setItem("role", "<?= $_SESSION['role'] ?>");
</script>
<div class="chat-container">
    <div class="users-panel" id="users"></div>
    <div class="chat-panel">
        <div class="messages" id="messages"></div>
        <div class="send-box">
            <input type="text" id="messageInput" placeholder="Mesaj...">
            <button onclick="sendMessage()">Gönder</button>
        </div>
    </div>
</div>

<script src="https://cdn.socket.io/4.3.2/socket.io.min.js"></script>
<script>
    const socket = io('http://localhost:3000');
    const userId = parseInt(localStorage.getItem("id"));
    const role = parseInt(localStorage.getItem("role"));
    const username = localStorage.getItem("username");

    let activeReceiver = null;

    socket.emit('join', userId);

    fetch(`http://localhost:3000/users?role=${role}&id=${userId}`)
        .then(res => res.json())
        .then(users => {
            const userList = document.getElementById('users');
            users.forEach(user => {
                const div = document.createElement('div');
                div.classList.add('user');
                div.innerHTML = `<img src="https://cdn-icons-png.flaticon.com/512/1144/1144760.png" alt=""> ${user.username}`;
                div.onclick = () => {
                    activeReceiver = user.id;
                    document.getElementById("messages").innerHTML = "";
                    fetchMessages(user.id);
                };
                userList.appendChild(div);
            });
        });

    function sendMessage() {
        const msg = document.getElementById("messageInput").value;
        if (!msg || !activeReceiver) return;

        socket.emit('send_message', {
            sender_id: userId,
            receiver_id: activeReceiver,
            message: msg
        });

        appendMessage(msg, 'right');
        document.getElementById("messageInput").value = "";
    }

    socket.on('receive_message', (data) => {
        if (parseInt(data.sender_id) === activeReceiver) {
            appendMessage(data.message, 'left');
        }
    });

    function appendMessage(message, side) {
        const div = document.createElement("div");
        div.classList.add("message", side);
        div.textContent = message;
        document.getElementById("messages").appendChild(div);
    }

    function fetchMessages(receiverId) {
        fetch(`http://localhost:3000/messages?user1=${userId}&user2=${receiverId}`)
            .then(res => res.json())
            .then(data => {
                data.forEach(m => {
                    const side = m.sender_id == userId ? 'right' : 'left';
                    appendMessage(m.message, side);
                });
            });
    }
</script>
</body>
</html>