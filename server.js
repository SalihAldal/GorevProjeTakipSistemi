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

    try {
        let query = '';
        let params = [];

        if (role === 1) {
            query = 'SELECT id, username FROM users WHERE role = 0';
            params = [id];
        } else {
            query = 'SELECT id, username FROM users WHERE role = 1';
        }

        const [rows] = await db.execute(query, params);
        res.json(rows);
    } catch (err) {
        console.error(err);
        res.status(500).json({ error: 'Kullanıcılar çekilemedi' });
    }
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
        socket.join(`user_${userId}`);
    });

    socket.on('send_message', async (data) => {
        const { sender_id, receiver_id, message } = data;

        await db.execute('INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)', [
            sender_id, receiver_id, message
        ]);

        io.to(`user_${receiver_id}`).emit('receive_message', {
            sender_id, message
        });
    });
});

server.listen(3000, () => {
    console.log('Socket server 3000 portunda çalışıyor');
});
