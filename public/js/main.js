// Inject Global Styles dynamically
(function () {
    if (!document.getElementById('antigravity-custom-styles')) {
        const s = document.createElement('style');
        s.id = 'antigravity-custom-styles';
        s.innerHTML = `
            .noti-dropdown { width: 360px; max-height: 500px; overflow-y: auto; border-radius: 12px !important; padding: 0 !important; }
            .noti-header { padding: 16px; border-bottom: 1px solid #f0f2f5; position: sticky; top: 0; background: white; z-index: 10; }
            .noti-item { padding: 12px 16px; display: flex; align-items: start; gap: 12px; cursor: pointer; transition: 0.2s; border-bottom: 1px solid #f0f2f5; text-decoration: none !important; color: inherit !important; }
            .noti-item:hover { background-color: #f0f2f5; }
            .noti-item.unread { background-color: #ebf5ff; }
            .noti-icon { width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0; }
            .noti-content { flex-grow: 1; }
            .noti-title { font-weight: 700; font-size: 0.9rem; margin-bottom: 2px; line-height: 1.3; }
            .noti-msg { font-size: 0.85rem; color: #65676b; margin-bottom: 4px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
            .noti-time { font-size: 0.75rem; color: #0866ff; font-weight: 600; }
            .noti-dot { width: 12px; height: 12px; background-color: #0866ff; border-radius: 50%; align-self: center; flex-shrink: 0; }
            .noti-badge { position: absolute; top: -5px; right: -5px; background-color: #e41e3f; color: white; border-radius: 50%; width: 18px; height: 18px; font-size: 10px; display: flex; align-items: center; justify-content: center; font-weight: 900; border: 2px solid white; }
            .noti-empty { padding: 40px 20px; text-align: center; color: #65676b; }
            .noti-btn-filter { font-size: 0.85rem; font-weight: 600; padding: 6px 12px; border-radius: 20px; border: none; background: transparent; color: #65676b; }
            .noti-btn-filter.active { background: #ebf5ff; color: #0064d1; }
        `;
        document.head.appendChild(s);
    }
    if (!document.querySelector('link[href*="bootstrap-icons"]')) {
        const l = document.createElement('link');
        l.rel = 'stylesheet';
        l.href = 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css';
        document.head.appendChild(l);
    }
})();

/* ── Single nav-link item ── */
function _navItem(href, icon, label, isActive) {
    return `<li class="nav-item">
        <a href="${href}" class="nav-link ${isActive ? 'active' : ''}">
            <i class="nav-icon ${icon}"></i><p>${label}</p>
        </a>
    </li>`;
}

/* ── Treeview group (collapsible) ── */
function _navGroup(icon, label, children, anyActive) {
    const childItems = children.map(c => `
        <li class="nav-item">
            <a href="${c.href}" class="nav-link ${c.active ? 'active' : ''}">
                <i class="nav-icon ${c.icon}"></i><p>${c.label}</p>
            </a>
        </li>`).join('');
    return `<li class="nav-item ${anyActive ? 'menu-open' : ''}">
        <a href="#" class="nav-link ${anyActive ? 'active' : ''}" onclick="toggleTreeview(this); return false;">
            <i class="nav-icon ${icon}"></i>
            <p>${label}<i class="nav-arrow bi bi-chevron-right ms-auto"></i></p>
        </a>
        <ul class="nav nav-treeview submenu-container">${childItems}</ul>
    </li>`;
}

/* ── Main sidebar renderer ── */
function renderSidebar(role, user, settings = {}) {
    const sidebar = document.getElementById('mainSidebar');
    if (!sidebar) return;

    const p = window.location.pathname.split('/').pop();
    const a = href => p === href;

    const avatarUrl = (user && user.photo) ? `../${user.photo}` : '../public/img/default-avatar.png';

    let html = `
        <!-- Logo Header -->
        <div class="sidebar-logo-header">
            <a class="sidebar-logo-link" href="#">
                <img src="${settings.school_logo ? '../' + settings.school_logo : '../public/img/logo.png'}" class="sidebar-logo-img" onerror="this.src='../public/img/logo.png'">
                <div class="sidebar-logo-text">
                    <span class="sidebar-logo-title">${settings.school_name || 'โรงเรียนชัยนาทพิทยาคม'}</span>
                    <span class="sidebar-logo-sub">CNP Application</span>
                </div>
            </a>
            <button class="sidebar-close-btn" onclick="document.getElementById('mainSidebar').classList.remove('active')">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- User Panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
            <div class="image ps-3">
                <img src="${avatarUrl}" class="rounded-circle shadow-sm" alt="User"
                     style="width:45px;height:45px;border:2px solid rgba(255,255,255,0.5);object-fit:cover;">
            </div>
            <div class="info ps-2" style="line-height: 1.2;">
                ${(() => {
            if (role === 'admin') {
                const name = (user.first_name_th || '') + ' ' + (user.last_name_th || '');
                const pos = user.position || 'ผู้ดูแลระบบ';
                return `<div class="fw-bold text-white small">${name.trim() || 'ผู้ดูแลระบบ'}</div>
                                <div class="text-white opacity-50" style="font-size: 0.65rem;">${pos}</div>`;
            } else if (role === 'teacher') {
                const name = (user.first_name_th || '') + ' ' + (user.last_name_th || '');
                const pos = user.academic_standing || user.position || 'ครูผู้สอน';
                return `<div class="fw-bold text-white small">${name || user.username}</div>
                                <div class="text-white opacity-50 text-truncate" style="font-size: 0.65rem; max-width: 130px;">${pos}</div>`;
            }
            return `<div class="fw-bold text-white small">${user.username}</div>`;
        })()}
            </div>
        </div>

        <div class="sidebar-wrapper">
        <nav class="mt-2 px-2">
        <ul class="nav sidebar-menu flex-column">
    `;

    // Static items for all logged-in staff
    // Dynamic Home link based on role
    const homeUrl = (role === 'admin') ? 'admin_dashboard.html' :
        (role === 'teacher') ? 'teacher_dashboard.html' :
            'student_dashboard.html';

    if (role === 'admin' || role === 'teacher') {
        html += _navItem(homeUrl, 'bi bi-house-door-fill', 'หน้าแรก', a(homeUrl));

        html += `<li class="nav-header opacity-75">บันทึกข้อมูล</li>`;
        html += _navGroup('bi bi-calendar-check-fill', 'เช็คชื่อมาเรียน/รายวิชา', [
            { href: 'attendance_daily.html', icon: 'bi bi-calendar-check', label: 'เช็คชื่อรายวัน', active: a('attendance_daily.html') },
            { href: 'attendance_subject.html', icon: 'bi bi-qr-code-scan', label: 'เช็คชื่อรายวิชา', active: a('attendance_subject.html') }
        ], a('attendance_daily.html') || a('attendance_subject.html'));

        html += `<li class="nav-header opacity-75">คะแนนพฤติกรรม</li>`;
        html += _navGroup('bi bi-star-fill text-warning', 'คะแนนพฤติกรรม', [
            { href: 'credit_score_manage.html', icon: 'bi bi-person-plus-fill', label: 'เพิ่ม/ลบ คะแนน', active: a('credit_score_manage.html') && !window.location.hash },
            { href: 'credit_score_manage.html#history', icon: 'bi bi-clock-history', label: 'ประวัติการให้คะแนน', active: a('credit_score_manage.html') && window.location.hash === '#history' },
            { href: 'credit_score_manage.html#stats', icon: 'bi bi-file-earmark-bar-graph', label: 'รายงานสรุปคะแนน', active: a('credit_score_manage.html') && window.location.hash === '#stats' },
            { href: 'credit_score_settings.html', icon: 'bi bi-gear-fill', label: 'ตั้งค่าระบบคะแนน', active: a('credit_score_settings.html') }
        ], ['credit_score_manage.html', 'credit_score_settings.html'].some(x => a(x)));

        if (role === 'teacher') {
            const myRoom = user.room || '';
            html += `<li class="nav-header opacity-75">นักเรียนที่ปรึกษา</li>`;
            html += _navItem(`admin_students.html${myRoom ? '?room=' + myRoom : ''}`, 'bi bi-people-fill text-warning', 'รายชื่อนักเรียนในที่ปรึกษา', p === 'admin_students.html' && new URLSearchParams(window.location.search).get('room') === myRoom);
        }

        if (role === 'admin') {
            html += `<li class="nav-header opacity-75">สถิติและรายงาน</li>`;
            html += _navItem('today_overview.html', 'bi bi-laptop text-info', 'ภาพรวมวันนี้', a('today_overview.html'));
            html += _navItem('admin_room_report.html', 'bi bi-grid-3x3-gap-fill text-primary', 'รายงานรายห้อง', a('admin_room_report.html'));
            html += _navItem('monthly_stats.html', 'bi bi-bar-chart-fill text-success', 'สถิติรายเดือน', a('monthly_stats.html'));
            html += _navItem('at_risk_students.html', 'bi bi-exclamation-triangle-fill text-danger', 'นักเรียนกลุ่มเสี่ยง', a('at_risk_students.html'));

            html += `<li class="nav-header opacity-75">จัดการข้อมูล</li>`;
            html += _navGroup('bi bi-database-fill-gear', 'จัดการข้อมูลพื้นฐาน', [
                { href: 'admin_classes.html', icon: 'bi bi-filter-square', label: 'ข้อมูลชั้นเรียน', active: a('admin_classes.html') },
                { href: 'admin_subjects.html', icon: 'bi bi-book', label: 'ข้อมูลวิชา', active: a('admin_subjects.html') },
                { href: 'admin_departments.html', icon: 'bi bi-building', label: 'ข้อมูลกลุ่มสาระฯ', active: a('admin_departments.html') },
                { href: 'admin_teachers.html', icon: 'bi bi-person-video3', label: 'ข้อมูลครู', active: a('admin_teachers.html') },
                { href: 'admin_students.html', icon: 'bi bi-people', label: 'ข้อมูลนักเรียน', active: a('admin_students.html') }
            ], ['admin_classes.html', 'admin_subjects.html', 'admin_departments.html', 'admin_teachers.html', 'admin_students.html'].some(x => a(x)));

            html += `<li class="nav-header opacity-75">ตั้งค่าระบบ</li>`;
            html += _navGroup('bi bi-tools', 'ตั้งค่าระบบ', [
                { href: 'admin_settings.html', icon: 'bi bi-gear', label: 'ตั้งค่าทั่วไป', active: a('admin_settings.html') }
            ], a('admin_settings.html'));
        } else if (role === 'teacher') {
            html += `<li class="nav-header opacity-75">จัดการข้อมูล</li>`;
            // Teacher sees only the generic student list link if they want to browse all
            const isAllStudents = a('admin_students.html') && !new URLSearchParams(window.location.search).get('room');
            html += _navItem('admin_students.html', 'bi bi-people', 'ข้อมูลนักเรียนทั้งหมด', isAllStudents);
        }
        html += `<li class="nav-header opacity-75">กิจกรรม</li>`;
        html += _navItem('academic_calendar.html', 'bi bi-calendar3 text-warning', 'ปฏิทินวิชาการ', a('academic_calendar.html'));
        html += _navItem('admin_public_service.html', 'bi bi-heart-fill text-danger', 'กิจกรรมสาธารณประโยชน์', a('admin_public_service.html'));
    }

    if (role === 'student') {
        html += _navItem(homeUrl, 'bi bi-house-door-fill', 'หน้าแรก', a(homeUrl));
        html += _navItem('student_attendance_history.html', 'bi bi-calendar-check', 'ประวัติการมาเรียน', a('student_attendance_history.html'));
        html += _navItem('student_credit_history.html', 'bi bi-star', 'คะแนนความประพฤติ', a('student_credit_history.html'));
        html += _navItem('academic_calendar.html', 'bi bi-calendar3 text-warning', 'ปฏิทินวิชาการ', a('academic_calendar.html'));
    }

    html += `<li class="nav-header mt-4 opacity-50 text-white">บัญชีผู้ใช้</li>`;
    let profileUrl = 'admin_profile.html';
    if (role === 'teacher') profileUrl = 'teacher_profile.html';
    if (role === 'student') profileUrl = 'student_profile.html';

    html += _navItem(profileUrl, 'bi bi-person-circle', 'โปรไฟล์ส่วนตัว', a(profileUrl));
    html += `<li class="nav-item">
        <a href="#" class="nav-link text-danger" onclick="logout(); return false;">
            <i class="nav-icon bi bi-box-arrow-right"></i><p>ออกจากระบบ</p>
        </a>
    </li>`;

    html += `</ul></nav></div>`;
    sidebar.innerHTML = html;
}

/* ── Treeview Toggle ── */
function toggleTreeview(link) {
    link.closest('.nav-item').classList.toggle('menu-open');
}

/* Legacy compat */
function toggleNavGroup(el) { el.parentElement.classList.toggle('menu-open'); }
function toggleFbGroup(btn) { btn.closest('.nav-item').classList.toggle('menu-open'); }
function toggleSidebar() { document.getElementById('mainSidebar')?.classList.toggle('active'); }

/* ── Auth ── */
async function checkAuth(expectedRole) {
    try {
        const [meRes, settingsRes] = await Promise.all([
            fetch('../api/me.php'),
            fetch('../api/settings.php').catch(() => null)
        ]);

        if (!meRes.ok) { window.location.href = '../'; return null; }
        const data = await meRes.json();

        let sysSettings = {};
        if (settingsRes && settingsRes.ok) {
            const setJson = await settingsRes.json();
            if (setJson.data) sysSettings = setJson.data;
        }

        let roles = [];
        if (expectedRole) roles = Array.isArray(expectedRole) ? expectedRole : [expectedRole];

        if (roles.length > 0 && !roles.includes(data.user.role)) {
            window.location.href = '../';
            return null;
        }
        renderSidebar(data.user.role, data.user, sysSettings);
        renderHeader(data.user.role, data.user, sysSettings);
        renderFooter(sysSettings);
        return data.user;
    } catch (err) {
        window.location.href = '../';
        return null;
    }
}

function logout() {
    fetch('../api/logout.php').then(() => window.location.href = '../');
}

/* ── House Badge Helper ── */
function getHouseBadge(houseName) {
    if (!houseName) return '-';
    let name = houseName.trim().replace('ขุุน', 'ขุน');
    let clz = 'badge bg-light text-dark border'; // default
    if (name === 'ขุนสรรค์') clz = 'badge badge-house-khunsan rounded-pill px-3 fw-normal';
    else if (name === 'ขุนศรี') clz = 'badge badge-house-khunsri rounded-pill px-3 fw-normal';
    else if (name === 'เจ้ายี่') clz = 'badge badge-house-chaoyee rounded-pill px-3 fw-normal';
    else if (name === 'ธรรมจักร') clz = 'badge badge-house-dhammachak rounded-pill px-3 fw-normal';
    return `<span class="${clz}">${name}</span>`;
}
/* ── Formatter Helpers ── */
function formatPhone(phone) {
    if (!phone) return '-';
    let p = phone.toString().trim();
    if (p.length >= 8 && !p.startsWith('0')) {
        p = '0' + p;
    }
    return p;
}

/* ── Global Header Renderer ── */
const SCHEDULE_CONFIG = {
    normal: {
        name: 'คาบปกติ',
        periods: [
            ['08:30', '09:25'], ['09:25', '10:20'], ['10:20', '11:15'],
            ['11:15', '12:10'], ['12:10', '13:05'], ['13:05', '14:00'],
            ['14:00', '14:55'], ['14:55', '15:50'], ['15:50', '16:45']
        ]
    },
    friday: {
        name: 'คาบทด',
        periods: [
            ['09:00', '09:50'], ['09:50', '10:40'], ['10:40', '11:35'],
            ['11:35', '12:25'], ['12:25', '13:15'], ['13:15', '14:05'],
            ['14:05', '15:00'], ['15:00', '15:50'], ['15:50', '16:45']
        ]
    },
    sport_1: {
        name: 'คาบกีฬาสี',
        periods: [
            ['08:30', '09:15'], ['09:15', '10:00'], ['10:00', '10:45'],
            ['10:45', '11:30'], ['11:30', '12:15'], ['12:15', '13:00'],
            ['13:00', '13:45'], ['13:45', '14:30'], ['14:30', '15:15']
        ]
    },
    sport_2: {
        name: 'คาบกีฬาสีทด',
        periods: [
            ['09:00', '09:45'], ['09:45', '10:30'], ['10:30', '11:15'],
            ['11:15', '12:00'], ['12:00', '12:45'], ['12:45', '13:30'],
            ['13:30', '14:15'], ['14:15', '15:00'], ['15:00', '15:45']
        ]
    }
};

function getCurrentPeriod(scheduleKey) {
    const config = SCHEDULE_CONFIG[scheduleKey];
    if (!config) return null;

    const now = new Date();
    const currentTime = now.getHours() * 60 + now.getMinutes();

    for (let i = 0; i < config.periods.length; i++) {
        const [startStr, endStr] = config.periods[i];
        const [sH, sM] = startStr.split(':').map(Number);
        const [eH, eM] = endStr.split(':').map(Number);

        const startTime = sH * 60 + sM;
        const endTime = eH * 60 + eM;

        if (currentTime >= startTime && currentTime < endTime) {
            return i + 1;
        }
    }
    return null;
}

function renderHeader(role, user, settings = {}) {
    const mainPanel = document.querySelector('.main-panel');
    if (!mainPanel || document.getElementById('globalHeader')) return;

    let fullName = 'ผู้ใช้งานระบบ';
    if (user) {
        fullName = user.full_name_th || (user.first_name_th + ' ' + user.last_name_th) || user.username || fullName;
    }

    const scheduleKey = settings.active_schedule || 'normal';
    const scheduleName = SCHEDULE_CONFIG[scheduleKey]?.name || 'คาบปกติ';

    let prefix = (role === 'teacher' || role === 'admin') ? 'ครู' : 'นักเรียน';
    if (role === 'student') prefix = '';

    let displayName = prefix + fullName;

    const headerHtml = `
<nav class="navbar navbar-expand navbar-light bg-white sticky-top shadow-sm px-3" id="globalHeader">
    <div class="container-fluid p-0 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <!-- ปุ่ม Toggle Hamburger -->
            <div id="menu-toggle" class="text-secondary" style="cursor: pointer;" onclick="toggleSidebar();">
                <i class="fa-solid fa-bars-staggered fs-4"></i>
            </div>
            <div class="ms-3 d-none d-md-block">
                <div class="fw-bold text-dark fs-6" style="line-height: 1.2;">ระบบบริหารจัดการโรงเรียน</div>
            </div>

            <!-- รวมสถานะและนาฬิกาไว้ใน Badge เดียวกัน -->
            <div class="ms-3 badge bg-white text-dark border shadow-sm d-none d-lg-inline-flex align-items-center py-2 px-3" style="font-weight: normal; gap: 12px;">
                <!-- ส่วนนาฬิกา -->
                <div class="d-flex align-items-center">
                    <i class="fa-regular fa-calendar-days text-primary me-2"></i>
                    <span id="realtime-clock" style="font-size: 0.85rem;">กำลังโหลด...</span>
                </div>

                <div class="vr opacity-25" style="height: 15px;"></div>

                <!-- ส่วนสถานะคาบเรียน -->
                <div class="d-flex align-items-center gap-2">
                    <span class="text-primary fw-bold" style="font-size: 0.75rem;">
                        <i class="bi bi-calendar-check me-1"></i> ${scheduleName}
                    </span>
                    <span id="current-period-status" style="font-size: 0.75rem;">
                        <span class="text-muted"><i class="bi bi-moon-stars me-1"></i> นอกเวลาเรียน</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- ส่วนด้านขวา: แจ้งเตือน และ ชื่อผู้ใช้ -->
        <div class="d-flex align-items-center gap-3">
            <!-- Notification Bell -->
            <div class="dropdown">
                <a class="nav-link px-0 position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" onclick="fetchNotifications()">
                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;">
                        <i class="bi bi-bell-fill text-secondary fs-5"></i>
                    </div>
                    <div id="noti-unread-badge" class="noti-badge d-none">0</div>
                </a>
                <div class="dropdown-menu dropdown-menu-end shadow border-0 noti-dropdown">
                    <div class="noti-header">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-black mb-0">การแจ้งเตือน</h5>
                            <button class="btn btn-link btn-sm text-decoration-none p-0" onclick="markAllAsRead()">ทำเครื่องหมายว่าอ่านแล้วทั้งหมด</button>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="noti-btn-filter active" id="noti-filter-all" onclick="filterNotifications('all')">ทั้งหมด</button>
                            <button class="noti-btn-filter" id="noti-filter-unread" onclick="filterNotifications('unread')">ยังไม่ได้อ่าน</button>
                        </div>
                    </div>
                    <div id="noti-list-container">
                        <div class="noti-empty">กำลังโหลด...</div>
                    </div>
                    <div class="p-3 text-center border-top">
                        <a href="#" class="small fw-bold text-primary text-decoration-none">ดูการแจ้งเตือนทั้งหมด</a>
                    </div>
                </div>
            </div>

            <div class="dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center px-0" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="${user.photo ? '../' + user.photo : '../public/img/default-avatar.png'}" class="rounded-circle me-2 avatar-img" width="32" height="32" style="object-fit:cover;" alt="User">
                    <span class="text-dark fw-bold small d-none d-sm-inline">${fullName}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-3 rounded-3">
                    <li class="px-3 py-2 text-center bg-light rounded-top">
                        <small class="text-muted fw-bold">ปีการศึกษา: ${settings.current_academic_year || '2569'}/${settings.current_semester || '1'}</small>
                    </li>
                    <li><a class="dropdown-item py-2" href="${role === 'admin' ? 'admin_profile.html' : role === 'teacher' ? 'teacher_profile.html' : 'student_profile.html'}"><i class="fa-solid fa-user-circle text-primary me-2"></i> โปรไฟล์</a></li>
                    <li><hr class="dropdown-divider opacity-50 my-1"></li>
                    <li><a class="dropdown-item py-2 text-danger fw-bold" href="#" onclick="event.preventDefault(); logout();"><i class="fa-solid fa-power-off me-2"></i> ออกจากระบบ</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>
    `;
    mainPanel.insertAdjacentHTML('afterbegin', headerHtml);

    // Clean up duplicate hamburger menus from the legacy headers
    document.querySelectorAll('.app-content-header .fa-bars, .app-content-header .fa-bars-staggered, .app-content-header .bi-list').forEach(icon => {
        let btn = icon.closest('button, .btn, a');
        if (btn) btn.remove();
    });

    // Real-time Clock Initialization
    function updateClock() {
        const clockEl = document.getElementById('realtime-clock');
        if (!clockEl) return;

        const now = new Date();
        const thMonths = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
        const thDays = ['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์'];

        const dayName = thDays[now.getDay()];
        const day = now.getDate();
        const month = thMonths[now.getMonth()];
        const year = now.getFullYear() + 543;

        const dateStr = `วัน${dayName}ที่ ${day} ${month} ${year}`;
        const timeStr = now.toLocaleTimeString('th-TH', { hour12: false });

        clockEl.innerHTML = `<span class="text-secondary fw-bold">${dateStr}</span> <span class="mx-2 text-muted">|</span> <i class="fa-regular fa-clock text-danger me-1"></i> <strong class="text-danger fs-6">${timeStr} น.</strong>`;

        // Update Period Status
        const periodEl = document.getElementById('current-period-status');
        if (periodEl) {
            const currentPeriod = getCurrentPeriod(settings.active_schedule || 'normal');
            if (currentPeriod) {
                periodEl.innerHTML = `<span class="text-success fw-bold"><i class="bi bi-clock-history me-1"></i> คาบที่ ${currentPeriod}</span>`;
            } else {
                periodEl.innerHTML = `<span class="text-muted"><i class="bi bi-moon-stars me-1"></i> นอกเวลาเรียน</span>`;
            }
        }
    }
    updateClock();
    setInterval(updateClock, 1000);
}

/* ── Global Footer Renderer ── */
function renderFooter(settings = {}) {
    const mainPanel = document.querySelector('.main-panel');
    if (!mainPanel || document.getElementById('globalFooter')) return;

    const schName = settings.school_name || 'โรงเรียนชัยนาทพิทยาคม';
    const schAffiliation = settings.school_affiliation || 'สำนักงานเขตพื้นที่การศึกษามัธยมศึกษาอุทัยธานี ชัยนาท';
    const schAddress = settings.school_address || 'เลขที่ 55/30 ถนนลูกเสือ 1 ตำบลบ้านกล้วย อำเภอเมืองชัยนาท จังหวัดชัยนาท 17000';
    const schPhone = settings.school_phone || '056-411645';
    const schEmail = settings.school_email || 'natchanan@chainatpit.ac.th';

    const footerHtml = `
<footer id="globalFooter" class="app-footer text-center text-md-start mt-auto">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-5 mb-3 mb-md-0">
                <div class="d-flex flex-column">
                    <span class="fw-bold text-dark mb-1" style="font-size: 0.8rem;">
                        2026 
                        <a href="#" class="text-decoration-none footer-link fw-bold" style="color: #6610f2;">
                            CNPAPP<sup>©</sup>
                        </a>
                        ระบบสารสนเทศนักเรียน${schName}
                    </span>
                    <span class="text-secondary" style="font-size: 0.8rem;">
                        แอปพลิเคชันเพื่อการบริหารจัดการข้อมูล${schName}
                    </span>
                </div>
            </div>

            <div class="col-md-7 text-md-end">
                <div class="mb-1 text-secondary" style="font-size: 0.75rem;">
                    ${schAffiliation}<br>
                    ${schAddress}
                </div>
                
                <div class="d-inline-flex flex-wrap align-items-center gap-3 justify-content-center justify-content-md-end text-secondary mb-1" style="font-size: 0.75rem;">
                    <span class="d-flex align-items-center text-nowrap" style="color: #3b82f6;">
                        <i class="bi bi-telephone-fill me-1"></i> ${schPhone}
                    </span>
                    
                    <a href="mailto:${schEmail}" target="_blank" class="text-decoration-none d-flex align-items-center text-nowrap" style="color: #3b82f6;">
                        <i class="bi bi-envelope-fill me-1"></i> Email
                    </a>
                    
                    <span class="text-muted opacity-50 d-none d-md-inline">|</span>
                    
                    <a href="https://cnp.clubth.com/privacy-policy" class="text-decoration-none text-secondary text-nowrap d-flex align-items-center">
                        <i class="bi bi-shield-check me-1"></i> นโยบายคุ้มครองข้อมูลส่วนบุคคล
                    </a>
                </div>
            </div>
        </div>
    </div>
</footer>
    `;
    mainPanel.insertAdjacentHTML('beforeend', footerHtml);
}

/* ── Notification Logic ── */
let lastUnreadCount = 0;
async function fetchNotifications() {
    try {
        const res = await fetch('../api/notifications.php');
        const json = await res.json();
        if (json.success) {
            renderNotifications(json.data);
            updateNotiBadge(json.unread_count);
        }
    } catch (e) { console.error("Error fetching notifications", e); }
}

function updateNotiBadge(count) {
    const badge = document.getElementById('noti-unread-badge');
    if (!badge) return;
    if (count > 0) {
        badge.textContent = count > 9 ? '9+' : count;
        badge.classList.remove('d-none');
        if (count > lastUnreadCount) {
            // Play sound or wiggle? Let's just update for now
        }
    } else {
        badge.classList.add('d-none');
    }
    lastUnreadCount = count;
}

function renderNotifications(items) {
    const container = document.getElementById('noti-list-container');
    if (!container) return;
    if (items.length === 0) {
        container.innerHTML = '<div class="noti-empty">ไม่มีการแจ้งเตือน</div>';
        return;
    }

    let html = '';
    items.forEach(n => {
        const isUnread = n.is_read == 0;
        const iconBg = n.type === 'system' ? '#f1f5f9' : n.type === 'public_service' ? '#ebf5ff' : '#f0f2f5';
        const iconColor = n.type === 'system' ? '#64748b' : n.type === 'public_service' ? '#0064d1' : '#1e3c72';

        html += `
            <a href="${n.link || '#'}" class="noti-item ${isUnread ? 'unread' : ''}" onclick="markAsRead(${n.id})">
                <div class="noti-icon" style="background: ${iconBg}; color: ${iconColor};">
                    <i class="${n.icon || 'bi bi-bell'}"></i>
                </div>
                <div class="noti-content">
                    <div class="noti-title">${n.title}</div>
                    <div class="noti-msg">${n.message}</div>
                    <div class="noti-time">${n.time_ago}</div>
                </div>
                ${isUnread ? '<div class="noti-dot"></div>' : ''}
            </a>
        `;
    });
    container.innerHTML = html;
}

async function markAsRead(id) {
    try {
        await fetch('../api/notifications.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'mark_read', id: id })
        });
        fetchNotifications();
    } catch (e) { console.error(e); }
}

async function markAllAsRead() {
    try {
        await fetch('../api/notifications.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'mark_read' })
        });
        fetchNotifications();
    } catch (e) { console.error(e); }
}

function filterNotifications(type) {
    document.querySelectorAll('.noti-btn-filter').forEach(b => b.classList.remove('active'));
    document.getElementById(`noti-filter-${type}`).classList.add('active');
    // For now we just fetch all, but we could filter locally or call API with ?unread=1
    fetchNotifications();
}

// Initial check for badge count
setInterval(fetchNotifications, 30000); // Check every 30s
setTimeout(fetchNotifications, 1000);   // Initial check

