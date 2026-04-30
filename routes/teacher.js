const express = require('express');
const router = express.Router();
const db = require('../database');
const bcrypt = require('bcrypt');

const requireAuthorized = (req, res, next) => {
    if (req.session.user && (req.session.user.role === 'teacher' || req.session.user.role === 'admin')) {
        next();
    } else {
        res.status(403).json({ error: 'Authorized access required' });
    }
};

router.use(requireAuthorized);

// Get Teacher classes (Or all for Admin)
router.get('/my-classes', (req, res) => {
    const user = req.session.user;
    if (user.role === 'admin') {
        db.all('SELECT DISTINCT class FROM students ORDER BY class', (err, rows) => {
            if (err) return res.json({ classes: [] });
            res.json({ classes: rows.map(r => r.class) });
        });
    } else {
        db.get('SELECT class_assigned FROM teachers WHERE user_id = ?', [user.id], (err, row) => {
            if (err || !row) return res.json({ classes: [] });
            const classes = row.class_assigned ? row.class_assigned.split(',') : [];
            res.json({ classes });
        });
    }
});

// Get Students by class
router.get('/students/:className', (req, res) => {
    const className = req.params.className;
    db.all('SELECT * FROM students WHERE class = ? ORDER BY CAST(number_in_class AS INTEGER) ASC', [className], (err, rows) => {
        if (err) return res.status(500).json({ error: 'Database error' });
        res.json({ students: rows || [] });
    });
});

// Save Attendance (Batch with Upsert)
router.post('/attendance', async (req, res) => {
    const { date, records, class_name } = req.body;
    const recordedBy = req.session.user.id;

    if (!records || !Array.isArray(records)) return res.status(400).json({ error: 'Invalid data' });

    try {
        const classRow = await new Promise((resolve, reject) => {
            db.get('SELECT id FROM classes WHERE class_name = ?', [class_name], (err, row) => err ? reject(err) : resolve(row));
        });

        if (!classRow) return res.status(400).json({ error: 'Class not found' });

        db.serialize(() => {
            db.run("BEGIN TRANSACTION");
            const stmt = db.prepare(`
                INSERT INTO attendance (student_id, class_id, date, status, remark, recorded_by) 
                VALUES (?, ?, ?, ?, ?, ?)
                ON CONFLICT(student_id, class_id, date) DO UPDATE SET 
                    status = excluded.status,
                    remark = excluded.remark,
                    recorded_by = excluded.recorded_by
            `);
            
            // Note: SQLite ON CONFLICT requires a unique index on (student_id, class_id, date)
            // If it doesn't exist, we'll just delete and insert as a fallback or use another method.
            // For safety, let's delete existing for this class/date first.
            db.run('DELETE FROM attendance WHERE class_id = ? AND date = ?', [classRow.id, date], (err) => {
                records.forEach(record => {
                    db.run(`INSERT INTO attendance (student_id, class_id, date, status, remark, recorded_by) 
                            VALUES (?, ?, ?, ?, ?, ?)`, 
                            [record.student_id, classRow.id, date, record.status, record.remark, recordedBy]);
                });
                db.run("COMMIT", (err) => {
                    if (err) return res.status(500).json({ error: 'Commit failed' });
                    res.json({ success: true });
                });
            });
        });
    } catch (err) {
        res.status(500).json({ error: 'Internal Error' });
    }
});

// Get Reports with percentage
router.get('/reports', (req, res) => {
    const { class_name, month, academic_year } = req.query;
    let filter = '1=1';
    let params = [];

    if (class_name) { filter += ' AND c.class_name = ?'; params.push(class_name); }
    if (month) { filter += ' AND a.date LIKE ?'; params.push(`${month}%`); }
    // Academic year filtering would require academic_calendar table joins

    const query = `
        SELECT 
            s.student_id, s.full_name, s.number_in_class,
            COUNT(a.id) as total_days,
            SUM(CASE WHEN a.status = 'มา' THEN 1 ELSE 0 END) as present,
            SUM(CASE WHEN a.status = 'ขาด' THEN 1 ELSE 0 END) as absent,
            SUM(CASE WHEN a.status = 'ลา' THEN 1 ELSE 0 END) as leave,
            SUM(CASE WHEN a.status = 'สาย' THEN 1 ELSE 0 END) as late,
            SUM(CASE WHEN a.status = 'ป่วย' THEN 1 ELSE 0 END) as sick
        FROM students s
        LEFT JOIN classes c ON s.class = c.class_name
        LEFT JOIN attendance a ON s.id = a.student_id
        WHERE ${filter}
        GROUP BY s.id
        ORDER BY CAST(s.number_in_class AS INTEGER) ASC
    `;

    db.all(query, params, (err, rows) => {
        if (err) return res.status(500).json({ error: 'DB Error' });
        
        const reports = rows.map(r => {
            const total = r.total_days || 1;
            return {
                ...r,
                percent: ((r.present / total) * 100).toFixed(2)
            };
        });
        
        res.json({ reports });
    });
});

// Dashboard Stats (Class specific)
router.get('/dashboard-stats', async (req, res) => {
    try {
        const teacherId = req.session.user.id;
        const teacher = await new Promise(resolve => {
            db.get('SELECT class_assigned FROM teachers WHERE user_id = ?', [teacherId], (err, row) => resolve(row));
        });
        
        const classes = teacher && teacher.class_assigned ? teacher.class_assigned.split(',') : [];
        const todayStr = new Date().toISOString().split('T')[0];

        // Total students in assigned classes
        const totalStudents = await new Promise(resolve => {
            db.get('SELECT COUNT(*) as count FROM students WHERE class IN (' + classes.map(() => '?').join(',') + ')', classes, (err, row) => resolve(row ? row.count : 0));
        });

        // Today's attendance stats for those classes
        const todayStats = await new Promise(resolve => {
            const query = `
                SELECT status, COUNT(*) as count 
                FROM attendance a
                JOIN students s ON a.student_id = s.id
                WHERE a.date = ? AND s.class IN (${classes.map(() => '?').join(',')})
                GROUP BY status
            `;
            db.all(query, [todayStr, ...classes], (err, rows) => {
                let s = { present: 0, absent: 0, late: 0, leave: 0, sick: 0 };
                if (rows) {
                    rows.forEach(r => {
                        if (r.status === 'มา') s.present = r.count;
                        if (r.status === 'ขาด') s.absent = r.count;
                        if (r.status === 'สาย') s.late = r.count;
                        if (r.status === 'ลา') s.leave = r.count;
                        if (r.status === 'ป่วย') s.sick = r.count;
                    });
                }
                resolve(s);
            });
        });

        res.json({ totalStudents, today: todayStats });
    } catch (err) {
        res.status(500).json({ error: 'Internal Error' });
    }
});

// Update Profile
router.post('/profile', async (req, res) => {
    const { full_name, password } = req.body;
    const userId = req.session.user.id;

    try {
        if (full_name) {
            db.run('UPDATE teachers SET full_name = ? WHERE user_id = ?', [full_name, userId]);
        }
        if (password) {
            const hashedPwd = await bcrypt.hash(password, 10);
            db.run('UPDATE users SET password = ? WHERE id = ?', [hashedPwd, userId]);
        }
        res.json({ success: true });
    } catch (err) {
        res.status(500).json({ error: 'Update failed' });
    }
});

module.exports = router;
