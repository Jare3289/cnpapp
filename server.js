require('dotenv').config();
const express = require('express');
const session = require('express-session');
const path = require('path');
const db = require('./database'); // Initialize DB

const authRoutes = require('./routes/auth');
const adminRoutes = require('./routes/admin');
const teacherRoutes = require('./routes/teacher');
const studentRoutes = require('./routes/student');

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(express.static(path.join(__dirname, 'public')));
app.use(session({
    secret: process.env.SECRET_KEY || 'default_secret',
    resave: false,
    saveUninitialized: false,
    cookie: { secure: false, maxAge: 1000 * 60 * 60 * 24 } // 1 day
}));

// Route Definitions
app.use('/api/auth', authRoutes);
app.use('/api/admin', adminRoutes);
app.use('/api/teacher', teacherRoutes);
app.use('/api/student', studentRoutes);

// View routes (serving static HTML files)
app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'index.html'));
});

// Middleware for checking authentication
const requireAuth = (role) => (req, res, next) => {
    if (!req.session.user) {
        return res.redirect('/');
    }
    if (role && req.session.user.role !== role) {
        return res.status(403).send('Forbidden');
    }
    next();
};

// Finalized View routes
app.get('/views/admin_dashboard.html', requireAuth('admin'), (req, res) => {
    res.sendFile(path.join(__dirname, 'views', 'admin_dashboard.html'));
});

app.get('/views/admin_import.html', requireAuth('admin'), (req, res) => {
    res.sendFile(path.join(__dirname, 'views', 'admin_import.html'));
});

app.get('/views/admin_users.html', requireAuth('admin'), (req, res) => {
    res.sendFile(path.join(__dirname, 'views', 'admin_users.html'));
});

app.get('/views/admin_profile.html', requireAuth(), (req, res) => {
    res.sendFile(path.join(__dirname, 'views', 'admin_profile.html'));
});

app.get('/views/admin_calendar.html', requireAuth('admin'), (req, res) => {
    res.sendFile(path.join(__dirname, 'views', 'admin_calendar.html'));
});

app.get('/views/teacher_dashboard.html', requireAuth('teacher'), (req, res) => {
    res.sendFile(path.join(__dirname, 'views', 'teacher_dashboard.html'));
});

app.get('/views/student_dashboard.html', requireAuth('student'), (req, res) => {
    res.sendFile(path.join(__dirname, 'views', 'student_dashboard.html'));
});

app.get('/views/attendance.html', requireAuth(), (req, res) => {
    res.sendFile(path.join(__dirname, 'views', 'attendance.html'));
});

app.get('/views/edit_attendance.html', requireAuth(), (req, res) => {
    res.sendFile(path.join(__dirname, 'views', 'edit_attendance.html'));
});

app.get('/views/reports.html', requireAuth(), (req, res) => {
    res.sendFile(path.join(__dirname, 'views', 'reports.html'));
});

app.get('/views/teacher_students.html', requireAuth('teacher'), (req, res) => {
    res.sendFile(path.join(__dirname, 'views', 'teacher_students.html'));
});

app.get('/views/credit.html', requireAuth('teacher'), (req, res) => {
    res.sendFile(path.join(__dirname, 'views', 'credit.html'));
});

app.get('/views/student_attendance_history.html', requireAuth('student'), (req, res) => {
    res.sendFile(path.join(__dirname, 'views', 'student_attendance_history.html'));
});

app.get('/views/student_credit_history.html', requireAuth('student'), (req, res) => {
    res.sendFile(path.join(__dirname, 'views', 'student_credit_history.html'));
});

// Emergency Reset Calendar Data
app.post('/api/admin/reset-calendar', (req, res) => {
    const db = require('./database');
    db.serialize(() => {
        db.run("DELETE FROM academic_calendar WHERE academic_year = '2569'");
        db.run("DELETE FROM holidays");
        
        db.run("INSERT INTO academic_calendar (academic_year, semester, start_date, end_date) VALUES (?, ?, ?, ?)", ['2569', 1, '2026-05-18', '2026-10-13']);
        db.run("INSERT INTO academic_calendar (academic_year, semester, start_date, end_date) VALUES (?, ?, ?, ?)", ['2569', 2, '2026-11-02', '2027-03-31']);
        
        const holidayData = [
            ['2026-06-01', 'วันหยุดชดเชยวันวิสาขบูชา', 'holiday'],
            ['2026-06-03', 'วันเฉลิมฯ สมเด็จพระนางเจ้าฯ พระบรมราชินี', 'holiday'],
            ['2026-07-28', 'วันเฉลิมฯ พระบาทสมเด็จพระเจ้าอยู่หัว', 'holiday'],
            ['2026-07-29', 'วันอาสาฬหบูชา', 'holiday'],
            ['2026-07-30', 'วันเข้าพรรษา', 'holiday'],
            ['2026-08-12', 'วันแม่แห่งชาติ', 'holiday'],
            ['2026-10-13', 'วันนวมินทรมหาราช', 'holiday'],
            ['2026-10-23', 'วันปิยมหาราช', 'holiday'],
            ['2026-12-05', 'วันพ่อแห่งชาติ', 'holiday'],
            ['2026-12-07', 'วันหยุดชดเชยวันพ่อแห่งชาติ', 'holiday'],
            ['2026-12-10', 'วันรัฐธรรมนูญ', 'holiday'],
            ['2026-12-31', 'วันสิ้นปี', 'holiday'],
            ['2027-01-01', 'วันขึ้นปีใหม่', 'holiday'],
            ['2027-02-21', 'วันมาฆบูชา', 'holiday'],
            ['2027-02-22', 'วันหยุดชดเชยวันมาฆบูชา', 'holiday']
        ];
        holidayData.forEach(h => db.run("INSERT INTO holidays (date, name, type) VALUES (?, ?, ?)", h));
    });
    res.json({ success: true, message: 'Calendar reset complete' });
});

// Start server
app.listen(PORT, () => {
    console.log(`Server is running on http://localhost:${PORT}`);
});
