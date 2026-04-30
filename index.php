<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <title>CNP APP | โรงเรียนชัยนาทพิทยาคม</title>
    <meta content='width=device-width, initial-scale=1.0, shrink-to-fit=no' name='viewport' />

    <!-- Google Sans Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Google+Sans:ital,opsz,wght@0,17..18,400..700;1,17..18,400..700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6.4 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="public/css/style.css">


    <style>
        :root {
            --glass-white: rgba(255, 255, 255, 0.6);
            --border-white: 1px solid rgba(255, 255, 255, 0.8);
            --navy-gradient: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        }

        .fw-black { font-weight: 900 !important; }
        .bg-app-portal { background-color: #f1f5f9; overflow: hidden; min-height: 100vh; }
        .mesh-gradient { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -2; background: radial-gradient(at 0% 0%, rgba(255, 133, 187, 0.1) 0px, transparent 50%), radial-gradient(at 100% 0%, rgba(30, 60, 114, 0.1) 0px, transparent 50%); }
        .hero-huge { font-size: clamp(3rem, 5vw, 4.5rem); line-height: 1.1; letter-spacing: -2px; }
        .gradient-text { background: var(--navy-gradient); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }
        .bg-glass-white { background-color: rgba(255, 255, 255, 0.7); backdrop-filter: blur(20px); border: var(--border-white); }
        .icon-circle-lg { width: 90px; height: 90px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; }

        /* Role Button Items */
        .role-button-item {
            display: flex; align-items: center; padding: 25px; border-radius: 28px;
            background: #fff; cursor: pointer; transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 4px 15px rgba(0, 45, 98, 0.05); border: 2px solid transparent;
        }

        .role-button-item:hover { transform: translateX(-10px); box-shadow: 0 15px 35px rgba(0, 45, 98, 0.1); border-color: var(--pink-vibrant); }

        .btn-icon { width: 55px; height: 55px; border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #fff; margin-right: 20px; }
        .student-btn .btn-icon { background: var(--pink-gradient); }
        .teacher-btn .btn-icon { background: var(--navy-gradient); }
        .admin-btn .btn-icon { background: #111827; }

        .btn-text { flex-grow: 1; color: var(--navy-deep); }
        .btn-text h5 { color: #0f172a !important; font-weight: 800; }
        .btn-text p { color: #475569 !important; font-weight: 600; }
        .btn-arrow { color: var(--navy-deep); opacity: 0.3; transition: 0.3s; }
        .role-button-item:hover .btn-arrow { opacity: 1; transform: translateX(5px); color: var(--pink-vibrant); }

        /* Login App UI */
        .input-app-wrapper { background: #f1f5f9; border-radius: 18px; padding: 5px 20px; display: flex; align-items: center; border: 2px solid #e2e8f0; }
        .input-app-wrapper:focus-within { border-color: var(--pink-vibrant); background: #fff; }
        .input-app-wrapper input { border: none; background: transparent; padding: 12px; width: 100%; font-weight: 600; color: #0f172a; }
        .input-app-wrapper input:focus { outline: none; }
        .input-app-wrapper i { color: #64748b; font-size: 1.2rem; margin-right: 5px; }

        .btn-app-primary { background: var(--navy-deep); color: #fff; border: none; border-radius: 20px; font-weight: 800; transition: 0.3s; }
        .btn-app-primary:hover { background: var(--navy-accent); transform: scale(1.02); color: #fff; }

        .glass-error { background: rgba(239, 68, 68, 0.1); color: #b91c1c; font-weight: 700; border: 1px solid rgba(239, 68, 68, 0.2); }

        /* Animation */
        @keyframes animateUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-up { opacity: 0; animation: animateUp 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards; }
        
        .stats-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.5);
            transition: 0.3s;
            height: 100%;
        }
        .stats-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.05); }
        .stats-icon { width: 50px; height: 50px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; margin-bottom: 15px; }
    </style>
</head>

<body class="bg-app-portal">
    <!-- Premium Ambient Background -->
    <div class="mesh-gradient"></div>
    <div class="glass-overlay"></div>

    <div class="wrapper-portal position-relative d-flex flex-column vh-100 overflow-hidden">
        <!-- Minimal Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light bg-transparent pt-4">
            <div class="container d-flex justify-content-between align-items-center">
                <a class="navbar-brand fw-bold text-navy d-flex align-items-center" href="index.php">
                    <img src="public/img/logo.png" alt="Logo" class="me-3" style="height: 48px;" onerror="this.style.display='none'">
                    <span class="fs-3 fw-black ls-1">CNP <span class="text-pink-vibrant">APP</span></span>
                </a>
                <div class="d-none d-md-block">
                    <span class="badge rounded-pill bg-white text-navy shadow-sm border px-4 py-2 fw-bold" style="font-size: 0.8rem;">
                        <i class="fas fa-circle text-success me-2 blink"></i> PORTAL ACTIVE
                    </span>
                </div>
            </div>
        </nav>

        <!-- Main Portal Container -->
        <div class="container flex-grow-1 d-flex align-items-center">
            <div class="row w-100 align-items-center g-5">
                <!-- Left Side: Welcome Text -->
                <div class="col-lg-7 text-start animated fadeInLeft">
                    <div class="badge-hero mb-3">
                        <span class="badge rounded-pill px-4 py-2 shadow-sm" style="background: var(--pink-gradient); color: #fff; font-weight: 800; font-size: 0.8rem; letter-spacing: 2px;">NEXT-GEN EDUCATION</span>
                    </div>
                    <h1 class="hero-huge fw-black text-navy mb-4">
                        ระบบบริหารจัดการ<br>
                        <span class="gradient-text">สารสนเทศยุคใหม่</span>
                    </h1>
                    <p class="text-muted fs-4 opacity-75 mb-5" style="max-width: 600px; font-weight: 500; line-height: 1.6;">
                        สัมผัสประสบการณ์การจัดการโรงเรียนที่ทันสมัย รวบรวมข้อมูลนักเรียน คุณครู และคะแนนพฤติกรรมไว้อย่างเป็นระบบ รวดเร็ว และแม่นยำที่สุด
                    </p>
                    <div class="d-flex gap-4 mt-2">
                        <div class="stat-item">
                            <h3 class="fw-black text-navy mb-0">1.2k+</h3>
                            <p class="small text-muted mb-0">STUDENTS</p>
                        </div>
                        <div class="vr opacity-10"></div>
                        <div class="stat-item">
                            <h3 class="fw-black text-navy mb-0">99%</h3>
                            <p class="small text-muted mb-0">ACCURACY</p>
                        </div>
                    </div>
                </div>

                <!-- Right Side: Vertical Role Buttons -->
                <div class="col-lg-5 text-center animated fadeInRight">
                    <div class="vertical-menu-wrapper p-2 bg-glass-white rounded-5 shadow-2xl border-white">
                        <div class="p-4 mb-2">
                            <h3 class="fw-black text-navy mt-2">เลือกบทบาทเข้าสู่ระบบ</h3>
                            <p class="small text-muted">SELECT YOUR ROLE TO START</p>
                        </div>
                        <div class="d-grid gap-3 p-3">
                            <!-- Student Button -->
                            <div class="role-button-item student-btn" onclick="goToLogin('student')">
                                <div class="btn-icon">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                                <div class="btn-text text-start">
                                    <h5 class="fw-black mb-0">นักเรียน</h5>
                                    <p class="small mb-0 opacity-75">STUDENT PORTAL</p>
                                </div>
                                <div class="btn-arrow"><i class="fas fa-chevron-right"></i></div>
                            </div>

                            <!-- Teacher Button -->
                            <div class="role-button-item teacher-btn" onclick="goToLogin('teacher')">
                                <div class="btn-icon">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                </div>
                                <div class="btn-text text-start">
                                    <h5 class="fw-black mb-0">คุณครู / บุคลากร</h5>
                                    <p class="small mb-0 opacity-75">STAFF ACCESS</p>
                                </div>
                                <div class="btn-arrow"><i class="fas fa-chevron-right"></i></div>
                            </div>

                            <!-- Admin Button -->
                            <div class="role-button-item admin-btn" onclick="goToLogin('admin')">
                                <div class="btn-icon">
                                    <i class="fas fa-user-shield"></i>
                                </div>
                                <div class="btn-text text-start">
                                    <h5 class="fw-black mb-0">ผู้ดูแลระบบ</h5>
                                    <p class="small mb-0 opacity-75">ADMINISTRATOR</p>
                                </div>
                                <div class="btn-arrow"><i class="fas fa-chevron-right"></i></div>
                            </div>
                        </div>
                        <div class="p-3 text-center opacity-25">
                            <span class="x-small text-navy fw-bold" style="font-size: 0.6rem;">SECURE CONNECTION ESTABLISHED</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Bar -->
        <footer class="py-4 text-start">
            <div class="container border-top pt-4 border-white opacity-50">
                <span class="small text-navy fw-bold" style="letter-spacing: 1px;">&copy; 2026 CNP APP - CHAINAT PITTHAYAKOM SCHOOL INFORMATION SYSTEM</span>
            </div>
        </footer>
    </div>

    <!-- Minimal Modern Login Modal (Remains the same as optimized before) -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-5 shadow-2xl overflow-hidden bg-glass-modal">
                <div class="modal-body p-5">
                    <div class="text-center mb-5">
                        <div id="modalIconContainer" class="icon-circle-lg mb-4 mx-auto">
                            <i id="modalIcon" class="fas fa-user-circle"></i>
                        </div>
                        <h2 class="fw-black text-navy mb-1" id="modalTitle">เข้าใช้งาน</h2>
                        <p class="text-muted small fw-bold mt-2" id="modalSubTitle">CHAINAT PITTHAYAKOM PORTAL</p>
                    </div>
                    <form id="loginForm">
                        <input type="hidden" id="selectedRole" value="">
                        <div class="form-group-app mb-4">
                            <label class="small text-navy fw-black mb-2 ms-1">บัญชีผู้ใช้งาน</label>
                            <div class="input-app-wrapper shadow-sm">
                                <i class="fas fa-at"></i>
                                <input type="text" id="username" placeholder="Username" required>
                            </div>
                        </div>
                        <div class="form-group-app mb-5">
                            <label class="small text-navy fw-black mb-2 ms-1">รหัสผ่าน</label>
                            <div class="input-app-wrapper shadow-sm">
                                <i class="fas fa-fingerprint"></i>
                                <input type="password" id="password" placeholder="Password" required>
                            </div>
                        </div>
                        <div id="errorMsg" class="alert alert-danger border-0 rounded-4 py-3 small mb-4 text-center glass-error" style="display: none;"></div>
                        <button type="submit" id="submitBtn" class="btn btn-app-primary w-100 py-3 shadow-pink">
                            UNLOCK SYSTEM <i class="fas fa-arrow-right-long ms-2"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Essential Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));

        function goToLogin(role) {
            document.getElementById('selectedRole').value = role;
            const titleMap = { 'student': 'นักเรียนเข้าใช้งาน', 'teacher': 'คุณครูเข้าใช้งาน', 'admin': 'แอดมินเข้าใช้งาน' };
            const iconMap = { 'student': 'fa-user-graduate', 'teacher': 'fa-chalkboard-teacher', 'admin': 'fa-user-shield' };
            const colorMap = { 'student': '#db2777', 'teacher': '#1e40af', 'admin': '#111827' };
            
            document.getElementById('modalTitle').innerText = titleMap[role];
            document.getElementById('modalIcon').className = `fas ${iconMap[role]}`;
            document.getElementById('modalIconContainer').style.color = colorMap[role];
            document.getElementById('errorMsg').style.display = 'none';
            loginModal.show();
        }

        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const role = document.getElementById('selectedRole').value;
            const errorMsg = document.getElementById('errorMsg');
            const submitBtn = document.getElementById('submitBtn');

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-grow spinner-grow-sm me-2"></span> กำลังประมวลผล...';

            try {
                const response = await fetch('api/login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username, password, role })
                });
                const data = await response.json();

                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    errorMsg.innerText = data.error || 'ข้อมูลไม่ถูกต้อง';
                    errorMsg.style.display = 'block';
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'UNLOCK SYSTEM <i class="fas fa-arrow-right-long ms-2"></i>';
                }
            } catch (err) {
                errorMsg.innerText = 'เซิร์ฟเวอร์ขัดข้อง';
                errorMsg.style.display = 'block';
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'UNLOCK SYSTEM <i class="fas fa-arrow-right-long ms-2"></i>';
            }
        });
    </script>
</body>
</html>
