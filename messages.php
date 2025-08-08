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
          display: flex;
          height: 100vh;
          font-family: 'Arial', sans-serif;
          }

          .sidebar {
           width: 250px;
           background: #f4f4f4;
           }

          .main {
          flex: 1;
          display: flex;
          flex-direction: column;
          }
         .topbar {
          height: 60px;
          background: #fff;
          border-bottom: 1px solid #ccc;
          }

         .chat-container {
          flex: 1;
          display: flex;
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
            width: 60px;
            height: 600px;
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
            position:relative;
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
        .message { position: relative; }
.message.right .status {
  font-size: 11px; color: #555; margin-top: 2px; display: block; text-align: right;
}
    </style>
</head>
<body>
<script>
    localStorage.setItem("id", "<?= $_SESSION['user_id'] ?>");
    localStorage.setItem("username", "<?= $_SESSION['username'] ?>");
    localStorage.setItem("role", "<?= $_SESSION['role'] ?>");
</script>
<?php
include 'includes/sidebar.php';
?>
<div class="main">
<?php
include "includes/header.php";
?>
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
</div>
<script src="https://cdn.socket.io/4.3.2/socket.io.min.js"></script>
<script>
    const socket = io('http://localhost:3000');
    const userId = parseInt(localStorage.getItem("id"));
    const role = parseInt(localStorage.getItem("role"));
    const username = localStorage.getItem("username");

    let activeReceiver = null;

    socket.emit('join', userId);
function absPath(p){
    if(!p || !p.trim()) return `${window.location.origin}/uploads/profile/default.png`;
    return `${window.location.origin}/${p.replace(/^\/+/, '')}`;
  }
  function handleImgErr(img){
    const tried = img.dataset.tried || '';
    if(!tried){
      if (img.src.match(/\.jpg(\?|$)/i)) {
        img.src = img.src.replace(/\.jpg(\?|$)/i, '.png$1');
      } else {
        img.src = img.src.replace(/\.png(\?|$)/i, '.jpg$1');
      }
      img.dataset.tried = '1';
    } else {
      img.src = `${window.location.origin}/uploads/profile/default.png`;
    }
  }
   fetch(`http://localhost:3000/users?role=${role}&id=${userId}`)
  .then(r=>r.json())
  .then(users=>{
    const userList = document.getElementById('users');
    userList.innerHTML = "";
    users.forEach(user=>{
      const profileImage = absPath(user.pp);
      const div = document.createElement('div');
      div.classList.add('user');
      div.innerHTML = `
        <img src="${profileImage}" onerror="handleImgErr(this)"
             alt="" style="width:60px;height:60px;border-radius:50%;">
        ${user.username}
      `;
      div.onclick = () => {
        activeReceiver = user.id;
        document.getElementById("messages").innerHTML = "";
        fetchMessages(user.id);
      };
      userList.appendChild(div);
    });
  });
function uid() {
  return 'm_' + Math.random().toString(36).slice(2, 10);
}
    function sendMessage() {
    const msg = document.getElementById("messageInput").value;
    if (!msg || !activeReceiver) return;

    const tempId = uid();

    appendMessage(msg, 'right', { tempId, status: 'sent' });

    socket.emit('send_message', {
        tempId,
        sender_id: userId,
        receiver_id: activeReceiver,
        message: msg
    });

    document.getElementById("messageInput").value = "";
}

    socket.on('receive_message', (data) => {
        if (parseInt(data.sender_id) === activeReceiver) {
            appendMessage(data.message, 'left');
        }
    });

    function appendMessage(message, side, opts = {}) {
    const { message_id = null, tempId = null, status = null } = opts;

    const div = document.createElement("div");
    div.classList.add("message", side);
    if (message_id) div.dataset.messageId = message_id;
    if (tempId) div.dataset.tempId = tempId;

    const body = document.createElement("div");
    body.textContent = message;
    div.appendChild(body);

    if (side === 'right') {
        const st = document.createElement('span');
        st.className = 'status';
        st.textContent = status || 'gönderiliyor…';
        div.appendChild(st);
    }

    document.getElementById("messages").appendChild(div);
    div.scrollIntoView({ behavior: 'smooth', block: 'end' });
}
socket.on('message_saved', ({ tempId, message_id, status }) => {
    const el = document.querySelector(`.message.right[data-temp-id="${tempId}"]`);
    if (el) {
        el.dataset.messageId = message_id;
        el.removeAttribute('data-temp-id');
        const st = el.querySelector('.status');
        if (st) st.textContent = status;
    }
});
    socket.on('message_status', ({ message_id, status }) => {
    const el = document.querySelector(`.message.right[data-message-id="${message_id}"]`);
    if (el) {
        const st = el.querySelector('.status');
        if (st) st.textContent = status;
    }
});
    socket.on('receive_message', (data) => {
    const from = parseInt(data.sender_id);
    const msgId = parseInt(data.message_id);

    if (from === activeReceiver) {
        appendMessage(data.message, 'left', { message_id: msgId });
        socket.emit('message_delivered', { message_id: msgId });
        socket.emit('conversation_seen', { other_user_id: from });
    } else {
    }
});
    function fetchMessages(receiverId) {
    fetch(`http://localhost:3000/messages?user1=${userId}&user2=${receiverId}`)
        .then(res => res.json())
        .then(data => {
            data.forEach(m => {
                const side = m.sender_id == userId ? 'right' : 'left';
                const opts = { message_id: m.id };
                if (side === 'right') opts.status = m.status; // kendi mesajında status göster
                appendMessage(m.message, side, opts);
            });
            socket.emit('conversation_seen', { other_user_id: receiverId });
        });
}
</script>
</body>
</html>