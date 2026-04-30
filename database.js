const sqlite3 = require('sqlite3').verbose();
const path = require('path');
const bcrypt = require('bcrypt');

const dbPath = path.resolve(__dirname, 'database.sqlite');
const db = new sqlite3.Database(dbPath, (err) => {
    if (err) {
        console.error('Error connecting to database:', err.message);
    } else {
        console.log('Connected to the SQLite database.');
        initDb();
    }
});

function initDb() {
    db.serialize(() => {
        // Create tables
        db.run(`CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            role TEXT NOT NULL
        )`);

        db.run(`CREATE TABLE IF NOT EXISTS students (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER UNIQUE NOT NULL,
            student_id TEXT UNIQUE NOT NULL, -- IDS
            number_in_class TEXT,            -- เลขที่
            class TEXT,                      -- ห้อง
            level TEXT,                      -- ระดับชั้น
            faculty TEXT,                    -- คณะ
            photo TEXT,                      -- รูปถ่าย
            title TEXT,                      -- คำนำหน้าชื่อ
            first_name TEXT,                 -- ชื่อ
            last_name TEXT,                  -- นามสกุล
            full_name TEXT,                  -- ชื่อจริง
            first_name_en TEXT,              -- NAME
            last_name_en TEXT,               -- SURENAME
            nickname TEXT,                   -- ชื่อเล่น
            email TEXT,                      -- ที่อยู่อีเมล
            gender TEXT,                     -- เพศ
            citizen_id TEXT,                 -- เลขบัตรประชาชน
            ethnicity TEXT,                  -- เชื้อชาติ
            nationality TEXT,                -- สัญชาติ
            religion TEXT,                   -- ศาสนา
            birth_date TEXT,                 -- วันเดือนปีเกิด
            FOREIGN KEY (user_id) REFERENCES users(id)
        )`);

        db.run(`CREATE TABLE IF NOT EXISTS teachers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER UNIQUE NOT NULL,
            teacher_id TEXT UNIQUE NOT NULL, -- IDT
            email TEXT,                      -- EMAIL
            citizen_id TEXT,                 -- เลขบัตรประชาชน
            title TEXT,                      -- คำนำหน้า
            first_name TEXT,                 -- ชื่อ
            last_name TEXT,                  -- สกุล
            full_name TEXT,                  -- ชื่อจริง
            first_name_en TEXT,              -- NAME
            last_name_en TEXT,               -- SURENAME
            class_assigned TEXT,             -- ห้อง
            faculty TEXT,                    -- คณะ
            photo TEXT,                      -- รูปถ่าย
            position TEXT,                   -- ตำแหน่ง
            academic_standing TEXT,          -- วิทยฐานะ
            department TEXT,                 -- กลุ่มสาระการเรียนรู้
            birth_date TEXT,                 -- วันเดือนปีเกิด
            recruitment_date TEXT,           -- วันที่บรรจุ
            recruited_at TEXT,               -- บรรจุเมื่อ
            FOREIGN KEY (user_id) REFERENCES users(id)
        )`);

        db.run(`CREATE TABLE IF NOT EXISTS classes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            class_name TEXT UNIQUE NOT NULL, -- ห้องเรียน
            location TEXT,                   -- ที่ตั้ง
            building TEXT,                   -- อาคาร
            floor TEXT,                      -- ชั้น
            room_number TEXT,                -- ห้อง
            level TEXT,                      -- ระดับชั้น
            faculty TEXT                     -- คณะ
        )`);

        db.run(`CREATE TABLE IF NOT EXISTS attendance (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            student_id INTEGER NOT NULL,
            class_id INTEGER NOT NULL,
            date TEXT NOT NULL,
            status TEXT NOT NULL,
            remark TEXT,
            recorded_by INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (student_id) REFERENCES students(id),
            FOREIGN KEY (class_id) REFERENCES classes(id),
            FOREIGN KEY (recorded_by) REFERENCES users(id)
        )`);

        db.run(`CREATE TABLE IF NOT EXISTS academic_calendar (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            academic_year TEXT NOT NULL,
            semester INTEGER NOT NULL,
            start_date TEXT NOT NULL,
            end_date TEXT NOT NULL
        )`);

        db.run(`CREATE TABLE IF NOT EXISTS holidays (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            date TEXT UNIQUE NOT NULL,
            name TEXT NOT NULL,
            type TEXT NOT NULL DEFAULT 'holiday' -- holiday, special, compensatory
        )`);

        db.run(`CREATE TABLE IF NOT EXISTS credits (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            student_id INTEGER NOT NULL,
            type TEXT NOT NULL,
            points INTEGER NOT NULL,
            reason TEXT NOT NULL,
            recorded_by INTEGER NOT NULL,
            date TEXT NOT NULL,
            FOREIGN KEY (student_id) REFERENCES students(id),
            FOREIGN KEY (recorded_by) REFERENCES users(id)
        )`);

        seedData();
    });
}

function seedData() {
    // Check if admin exists
    db.get("SELECT id FROM users WHERE username = ?", ['admin'], async (err, row) => {
        if (!row) {
            const hashedAdminPwd = await bcrypt.hash('admin123', 10);
            db.run("INSERT INTO users (username, password, role) VALUES (?, ?, ?)", ['admin', hashedAdminPwd, 'admin']);
        }
    });

    // Check if teacher1 exists
    db.get("SELECT id FROM users WHERE username = ?", ['teacher1'], async (err, row) => {
        if (!row) {
            const hashedTeacherPwd = await bcrypt.hash('pass123', 10);
            db.run("INSERT INTO users (username, password, role) VALUES (?, ?, ?)", ['teacher1', hashedTeacherPwd, 'teacher'], function (err) {
                if (!err) {
                    db.run("INSERT INTO teachers (user_id, teacher_id, full_name, class_assigned) VALUES (?, ?, ?, ?)", [this.lastID, 'T001', 'Teacher One', 'M.1/1']);
                }
            });
        }
    });

    // Check if student1 exists
    db.get("SELECT id FROM users WHERE username = ?", ['student1'], async (err, row) => {
        if (!row) {
            const hashedStudentPwd = await bcrypt.hash('pass123', 10);
            db.run("INSERT INTO users (username, password, role) VALUES (?, ?, ?)", ['student1', hashedStudentPwd, 'student'], function (err) {
                if (!err) {
                    db.run("INSERT INTO students (user_id, student_id, full_name, class) VALUES (?, ?, ?, ?)", [this.lastID, 'S001', 'Student One', 'M.1/1']);
                }
            });
        }
    });

    // Check if class exists
    db.get("SELECT id FROM classes WHERE class_name = ?", ['M.1/1'], (err, row) => {
        if (!row) {
            db.run("INSERT INTO classes (class_name) VALUES (?)", ['M.1/1']);
        }
    });

    // Seed Semesters (2569 BE = 2026 AD)
    db.run("DELETE FROM academic_calendar WHERE academic_year = '2569'", (err) => {
        db.run("INSERT INTO academic_calendar (academic_year, semester, start_date, end_date) VALUES (?, ?, ?, ?)", ['2569', 1, '2026-05-18', '2026-10-13']);
        db.run("INSERT INTO academic_calendar (academic_year, semester, start_date, end_date) VALUES (?, ?, ?, ?)", ['2569', 2, '2026-11-02', '2027-03-31']);
    });

    // Seed Holidays matching the user's list
    // Clear old holidays first to ensure exact match with the new list
    db.run("DELETE FROM holidays");
    
    const holidayData = [
        ['2026-06-01', 'วันหยุดชดเชยวันวิสาขบูชา', 'holiday'],
        ['2026-06-03', 'วันเฉลิมพระชนมพรรษาฯ พระบรมราชินี', 'holiday'],
        ['2026-07-28', 'วันเฉลิมพระชนมพรรษา ร.10', 'holiday'],
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

    holidayData.forEach(h => {
        db.run("INSERT INTO holidays (date, name, type) VALUES (?, ?, ?)", h);
    });
}

module.exports = db;
