const express = require('express');
const router = express.Router();
const db = require('../database');
const bcrypt = require('bcrypt');
const multer = require('multer');
const xlsx = require('xlsx');

const upload = multer({ dest: 'uploads/' });

// 1. Template routes (Public/Dashboard access)
router.get('/template-students', (req, res) => {
    try {
        const data = [
            {
                "รหัสนักเรียน": "64001",
                "เลขที่": "1",
                "ห้อง": "6/1",
                "ระดับชั้น": "ม.6",
                "คำนำหน้าชื่อ": "นาย",
                "ชื่อจริง": "สมชาย",
                "นามสกุล": "ใจดี",
                "NAME": "Somchai",
                "SURENAME": "Jaidee",
                "เบอร์โทรศัพท์": "0812345678"
            },
            {
                "รหัสนักเรียน": "64002",
                "เลขที่": "2",
                "ห้อง": "6/1",
                "ระดับชั้น": "ม.6",
                "คำนำหน้าชื่อ": "นางสาว",
                "ชื่อจริง": "สมศรี",
                "นามสกุล": "รักเรียน",
                "NAME": "Somsri",
                "SURENAME": "Rakrian",
                "เบอร์โทรศัพท์": "0898765432"
            }
        ];
        const ws = xlsx.utils.json_to_sheet(data);
        const wb = xlsx.utils.book_new();
        xlsx.utils.book_append_sheet(wb, ws, "รายชื่อนักเรียน");
        const buffer = xlsx.write(wb, { type: "buffer", bookType: "xlsx" });
        res.setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        res.setHeader('Content-Disposition', 'attachment; filename="Template_Students_CNP.xlsx"');
        res.send(buffer);
    } catch (err) {
        res.status(500).send("Error generating template");
    }
});

router.get('/template-teachers', (req, res) => {
    try {
        const data = [
            {
                "IDT": "T101",
                "ชื่อจริง": "ใจรัก",
                "นามสกุล": "สอนดี",
                "NAME": "Jairak",
                "SURENAME": "Sorndee",
                "ห้อง": "6/1",
                "ตำแหน่ง": "ครูที่ปรึกษา",
                "เบอร์โทรศัพท์": "0881112222"
            }
        ];
        const ws = xlsx.utils.json_to_sheet(data);
        const wb = xlsx.utils.book_new();
        xlsx.utils.book_append_sheet(wb, ws, "รายชื่อคุณครู");
        const buffer = xlsx.write(wb, { type: "buffer", bookType: "xlsx" });
        res.setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        res.setHeader('Content-Disposition', 'attachment; filename="Template_Teachers_CNP.xlsx"');
        res.send(buffer);
    } catch (err) {
        res.status(500).send("Error generating template");
    }
});

// 2. Middleware for Admin only
const requireAdmin = (req, res, next) => {
    if (req.session.user && req.session.user.role === 'admin') {
        next();
    } else {
        res.status(403).json({ error: 'Admin access required' });
    }
};

router.use(requireAdmin);

// 3. Admin Protected Routes
router.get('/dashboard-stats', async (req, res) => {
    try {
        const todayStr = new Date().toISOString().split('T')[0];
        
        const counts = await new Promise((resolve) => {
            db.get(`SELECT 
                (SELECT COUNT(*) FROM students) as students,
                (SELECT COUNT(*) FROM teachers) as teachers,
                (SELECT COUNT(*) FROM classes) as classes
            `, (err, row) => resolve(row || { students: 0, teachers: 0, classes: 0 }));
        });

        const todayStats = await new Promise((resolve) => {
            db.all('SELECT status, COUNT(*) as count FROM attendance WHERE date = ? GROUP BY status', [todayStr], (err, rows) => {
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

        // Weekly trend (Last 5 days)
        const weeklyTrend = await new Promise((resolve) => {
            db.all(`
                SELECT date, COUNT(*) as count 
                FROM attendance 
                WHERE status = 'มา' 
                GROUP BY date 
                ORDER BY date DESC 
                LIMIT 5
            `, (err, rows) => resolve(rows ? rows.reverse() : []));
        });

        // Risk Group (Absences > 3 in last 30 days)
        const riskGroup = await new Promise((resolve) => {
            db.all(`
                SELECT s.full_name, COUNT(a.id) as count
                FROM attendance a
                JOIN students s ON a.student_id = s.id
                WHERE a.status IN ('ขาด', 'สาย', 'ป่วย')
                GROUP BY a.student_id
                HAVING count >= 3
                ORDER BY count DESC
                LIMIT 5
            `, (err, rows) => resolve(rows || []));
        });

        res.json({
            ...counts,
            today: todayStats,
            weekly: weeklyTrend,
            risk: riskGroup
        });
    } catch (err) {
        res.status(500).json({ error: 'Internal Server Error' });
    }
});

async function handleExcelUpload(filePath, type) {
    const workbook = xlsx.readFile(filePath);
    const sheetName = workbook.SheetNames[0];
    const data = xlsx.utils.sheet_to_json(workbook.Sheets[sheetName]);
    let successCount = 0;
    const defaultPwd = await bcrypt.hash('123456', 10);

    return new Promise((resolve, reject) => {
        db.serialize(() => {
            db.run("BEGIN TRANSACTION");
            try {
                for (let row of data) {
                    if (type === 'student') {
                        const stdId = String(row['รหัสนักเรียน'] || row['ID'] || '');
                        const fname = row['ชื่อจริง'] || row['ชื่อ'] || row['NAME'] || '';
                        const sname = row['นามสกุล'] || row['SURENAME'] || row['สกุล'] || '';
                        const fullName = `${fname} ${sname}`.trim();
                        const className = row['ห้อง'] || 'N/A';
                        if (!stdId || !fname) continue;
                        db.run("INSERT OR IGNORE INTO users (username, password, role) VALUES (?, ?, 'student')", [stdId, defaultPwd]);
                        db.run(`INSERT OR IGNORE INTO students (user_id, student_id, name, class) 
                                VALUES ((SELECT id FROM users WHERE username = ?), ?, ?, ?)`, [stdId, stdId, fullName, className]);
                    } else if (type === 'teacher') {
                        const tId = String(row['IDT'] || row['ID'] || '');
                        const fname = row['ชื่อจริง'] || row['ชื่อ'] || row['NAME'] || '';
                        const sname = row['นามสกุล'] || row['สกุล'] || row['SURENAME'] || '';
                        const fullName = `${fname} ${sname}`.trim();
                        const className = row['ห้อง'] || 'N/A';
                        if (!tId || !fname) continue;
                        db.run("INSERT OR IGNORE INTO users (username, password, role) VALUES (?, ?, 'teacher')", [tId, defaultPwd]);
                        db.run(`INSERT OR IGNORE INTO teachers (user_id, teacher_id, name, class_assigned) 
                                VALUES ((SELECT id FROM users WHERE username = ?), ?, ?, ?)`, [tId, tId, fullName, className]);
                    }
                    successCount++;
                }
                db.run("COMMIT", (err) => { err ? reject(err) : resolve(successCount); });
            } catch (err) {
                db.run("ROLLBACK");
                reject(err);
            }
        });
    });
}

router.post('/upload-students', upload.single('excelFile'), async (req, res) => {
    try {
        if (!req.file) return res.status(400).json({ error: 'No file uploaded' });
        const count = await handleExcelUpload(req.file.path, 'student');
        res.json({ message: `นำเข้าข้อมูลนักเรียนสำเร็จ ${count} รายการ` });
    } catch (err) {
        res.status(500).json({ error: 'Failed to process file' });
    }
});

router.post('/upload-teachers', upload.single('excelFile'), async (req, res) => {
    try {
        if (!req.file) return res.status(400).json({ error: 'No file uploaded' });
        const count = await handleExcelUpload(req.file.path, 'teacher');
        res.json({ message: `นำเข้าข้อมูลครูสำเร็จ ${count} รายการ` });
    } catch (err) {
        res.status(500).json({ error: 'Failed to process file' });
    }
});

// 4. Data Management Routes (View/Delete)
router.get('/students', (req, res) => {
    const query = `
        SELECT s.id, s.student_id, s.name, s.class, u.username 
        FROM students s 
        JOIN users u ON s.user_id = u.id
    `;
    db.all(query, (err, rows) => {
        if (err) return res.status(500).json({ error: 'DB Error' });
        res.json({ students: rows });
    });
});

router.get('/teachers', (req, res) => {
    const query = `
        SELECT t.id, t.teacher_id, t.name, t.class_assigned, u.username 
        FROM teachers t 
        JOIN users u ON t.user_id = u.id
    `;
    db.all(query, (err, rows) => {
        if (err) return res.status(500).json({ error: 'DB Error' });
        res.json({ teachers: rows });
    });
});

router.delete('/user/:id', (req, res) => {
    const userId = req.params.id;
    // Note: Usually we'd delete student/teacher profile first, but DB schema has foreign keys.
    // For simplicity in this demo, we'll just delete the user record if cascaded, or handle manually.
    db.run('DELETE FROM users WHERE id = ?', [userId], function(err) {
        if (err) return res.status(500).json({ error: 'Delete failed' });
        res.json({ message: 'ลบข้อมูลสำเร็จ' });
    });
});

// 5. Calendar & Holiday Management
router.get('/catalog/semesters', (req, res) => {
    db.all('SELECT * FROM academic_calendar ORDER BY academic_year DESC, semester ASC', (err, rows) => {
        if (err) return res.status(500).json({ error: 'DB Error' });
        res.json(rows || []);
    });
});

router.post('/catalog/semesters', (req, res) => {
    const { academic_year, semester, start_date, end_date } = req.body;
    db.run('INSERT INTO academic_calendar (academic_year, semester, start_date, end_date) VALUES (?, ?, ?, ?)',
        [academic_year, semester, start_date, end_date], function(err) {
            if (err) return res.status(500).json({ error: 'Save failed' });
            res.json({ id: this.lastID });
        });
});

router.delete('/catalog/semesters/:id', (req, res) => {
    db.run('DELETE FROM academic_calendar WHERE id = ?', [req.params.id], err => {
        res.json({ success: !err });
    });
});

router.get('/catalog/holidays', (req, res) => {
    db.all('SELECT * FROM holidays ORDER BY date DESC', (err, rows) => {
        if (err) return res.status(500).json({ error: 'DB Error' });
        res.json(rows || []);
    });
});

router.post('/catalog/holidays', (req, res) => {
    const { name, date, type } = req.body;
    db.run('INSERT OR REPLACE INTO holidays (name, date, type) VALUES (?, ?, ?)',
        [name, date, type], function(err) {
            if (err) return res.status(500).json({ error: 'Save failed' });
            res.json({ id: this.lastID });
        });
});

router.delete('/catalog/holidays/:id', (req, res) => {
    db.run('DELETE FROM holidays WHERE id = ?', [req.params.id], err => {
        res.json({ success: !err });
    });
});

module.exports = router;
