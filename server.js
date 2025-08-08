// server.js
const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const mysql = require('mysql2/promise');
const cors = require('cors');

const app = express();
const server = http.createServer(app);
const io = socketIo(server, { cors: { origin: '*' } });

app.use(cors());
app.use(express.json());

let db;

mysql.createConnection({
    host: 'localhost',
    user: 'root',
    password: '',
    database: 'staj'
}).then(connection => {
    db = connection;
    console.log('MySQL bağlantısı başarılı');
}).catch(err => {
    console.error('MySQL bağlantı hatası:', err);
});

app.get('/users', async (req, res) => {
    const role = parseInt(req.query.role);
    const id = parseInt(req.query.id);

    let query = '';
    let params = [];

    if (role === 1) {
        query = 'SELECT id, username, pp FROM users WHERE id != ?';
        params = [id];
    } else {
        query = 'SELECT id, username, pp FROM users WHERE role = 1';
    }

    const [rows] = await db.execute(query, params);
    res.json(rows);
});

app.get('/messages', async (req, res) => {
    const { user1, user2 } = req.query;
    const [rows] = await db.execute(`
        SELECT * FROM messages
        WHERE (sender_id = ? AND receiver_id = ?) OR
            (sender_id = ? AND receiver_id = ?)
        ORDER BY created_at ASC
    `, [user2, user1, user1, user2]);
    res.json(rows);
});

io.on('connection', (socket) => {
    console.log('Yeni kullanıcı bağlandı');

    socket.on('join', (userId) => {
        socket.data.userId = parseInt(userId);
        socket.join(`user_${userId}`);
    });

    socket.on('send_message', async (data) => {
        const { tempId, sender_id, receiver_id, message } = data;

        const [result] = await db.execute(
            'INSERT INTO messages (sender_id, receiver_id, message, status) VALUES (?, ?, ?, ?)',
            [sender_id, receiver_id, message, 'sent']
        );
        const messageId = result.insertId;

        io.to(`user_${sender_id}`).emit('message_saved', {
            tempId,
            message_id: messageId,
            status: 'sent'
        });

        io.to(`user_${receiver_id}`).emit('receive_message', {
            message_id: messageId,
            sender_id,
            message
        });

        const room = io.sockets.adapter.rooms.get(`user_${receiver_id}`);
        if (room && room.size > 0) {
            await db.execute(
                "UPDATE messages SET status='delivered', delivered_at=NOW() WHERE id=? AND status='sent'",
                [messageId]
            );
            io.to(`user_${sender_id}`).emit('message_status', {
                message_id: messageId,
                status: 'delivered'
            });
        }
    });

    socket.on('message_delivered', async ({ message_id }) => {
        await db.execute(
            "UPDATE messages SET status='delivered', delivered_at=NOW() WHERE id=? AND status='sent'",
            [message_id]
        );
        const [[row]] = await db.execute("SELECT sender_id FROM messages WHERE id=?", [message_id]);
        if (row) {
            io.to(`user_${row.sender_id}`).emit('message_status', {
                message_id, status: 'delivered'
            });
        }
    });

    socket.on('conversation_seen', async ({ other_user_id }) => {
        const me = socket.data.userId;
        await db.execute(
            "UPDATE messages SET status='seen', seen_at=NOW() WHERE sender_id=? AND receiver_id=? AND status IN ('sent','delivered')",
            [other_user_id, me]
        );
        const [seenRows] = await db.execute(
            "SELECT id FROM messages WHERE sender_id=? AND receiver_id=? AND status='seen' ORDER BY id DESC LIMIT 50",
            [other_user_id, me]
        );
        seenRows.forEach(r => {
            io.to(`user_${other_user_id}`).emit('message_status', {
                message_id: r.id, status: 'seen'
            });
        });
    });
});
server.listen(3000, () => {
    console.log('Socket server 3000 portunda çalışıyor');
});
