const express = require('express');
const router = express.Router();
const bcrypt = require('bcrypt');
const db = require('../database');

// Login endpoint
router.post('/login', (req, res) => {
    const { username, password, role } = req.body;

    if (!username || !password) {
        return res.status(400).json({ error: 'Please provide username and password' });
    }

    db.get('SELECT * FROM users WHERE username = ?', [username], async (err, user) => {
        if (err) {
            return res.status(500).json({ error: 'Database error' });
        }
        if (!user) {
            return res.status(401).json({ error: 'Invalid username or password' });
        }

        // Optional role check if provided by client
        if (role && user.role !== role) {
            return res.status(401).json({ error: 'บทบาทผู้ใช้ไม่ถูกต้องสำหรับบัญชีนี้' });
        }

        const match = await bcrypt.compare(password, user.password);
        if (!match) {
            return res.status(401).json({ error: 'Invalid username or password' });
        }

        req.session.user = {
            id: user.id,
            username: user.username,
            role: user.role
        };

        // If teacher, fetch teacher_id. If student, fetch student_id.
        if (user.role === 'teacher') {
            db.get('SELECT id as teacher_profile_id FROM teachers WHERE user_id = ?', [user.id], (err, teacher) => {
                if (teacher) req.session.user.profile_id = teacher.teacher_profile_id;
                res.json({ success: true, role: user.role, redirect: '/views/' + user.role + '_dashboard.html' });
            });
        } else if (user.role === 'student') {
            db.get('SELECT id as student_profile_id FROM students WHERE user_id = ?', [user.id], (err, student) => {
                if (student) req.session.user.profile_id = student.student_profile_id;
                res.json({ success: true, role: user.role, redirect: '/views/' + user.role + '_dashboard.html' });
            });
        } else {
            res.json({ success: true, role: user.role, redirect: '/views/admin_dashboard.html' });
        }
    });
});

// Logout endpoint
router.post('/logout', (req, res) => {
    req.session.destroy();
    res.json({ success: true });
});

// Get current user info
router.get('/me', (req, res) => {
    if (req.session.user) {
        res.json({ user: req.session.user });
    } else {
        res.status(401).json({ error: 'Not authenticated' });
    }
});

module.exports = router;
