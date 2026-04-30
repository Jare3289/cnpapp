const express = require('express');
const router = express.Router();
const db = require('../database');

const requireStudent = (req, res, next) => {
    if (req.session.user && req.session.user.role === 'student') {
        next();
    } else {
        res.status(403).json({ error: 'Student access required' });
    }
};

router.use(requireStudent);

// Get my attendance history
router.get('/attendance', (req, res) => {
    const studentId = req.session.user.profile_id;
    db.all(`
        SELECT a.date, a.status, c.class_name, t.name as recorded_by_name
        FROM attendance a
        JOIN classes c ON a.class_id = c.id
        JOIN teachers t ON a.recorded_by = t.id
        WHERE a.student_id = ?
        ORDER BY a.date DESC
    `, [studentId], (err, rows) => {
        if (err) return res.status(500).json({ error: 'Database error' });
        res.json({ attendance: rows || [] });
    });
});

// Get my credit history
router.get('/credit', (req, res) => {
    const studentId = req.session.user.profile_id;
    db.all(`
        SELECT cr.date, cr.type, cr.points, cr.reason, t.name as recorded_by_name
        FROM credits cr
        JOIN teachers t ON cr.recorded_by = t.id
        WHERE cr.student_id = ?
        ORDER BY cr.date DESC
    `, [studentId], (err, rows) => {
        if (err) return res.status(500).json({ error: 'Database error' });
        
        let total = 0;
        if (rows) {
            rows.forEach(r => {
                if (r.type === 'บวก') total += r.points;
                else if (r.type === 'ลบ') total -= r.points;
            });
        }
        res.json({ credits: rows || [], currentScore: total });
    });
});

module.exports = router;
