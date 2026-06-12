<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" type="image/svg+xml" href="assets/logo2.svg" sizes="any">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>S-SDI System | Verifikasi Akses</title> 
    <script defer src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #3498db;
            --success: #2ecc71;
            --danger: #e74c3c;
            --bg: #0f1113;
            --card-bg: #1a1c1e;
            --text-muted: #888;
            --border: #2a2d30;
        }

        body { 
            background: var(--bg); 
            color: white; 
            font-family: 'Inter', 'Segoe UI', sans-serif; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            min-height: 100vh; 
            margin: 0; 
            padding: 15px;
            box-sizing: border-box;
        }

        .card {
            background: var(--card-bg);
            padding: 25px;
            border-radius: 28px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.6);
            text-align: center;
            width: 100%;
            max-width: 420px; 
            border: 1px solid var(--border);
            box-sizing: border-box;
        }

        h3.title-header { 
            margin: 0 0 8px 0; 
            font-weight: 600; 
            letter-spacing: -0.5px; 
            font-size: 1.5rem;
            cursor: pointer;
            user-select: none;
        }

        .mode-container {
            display: flex;
            background: #111315;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 4px;
            margin-bottom: 20px;
        }
        .mode-btn {
            flex: 1;
            background: transparent;
            border: none;
            color: var(--text-muted);
            padding: 10px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .mode-btn.active {
            background: var(--primary);
            color: #fff;
        }

        .video-container { 
            position: relative; 
            width: 100%; 
            aspect-ratio: 3/4;
            border-radius: 20px; 
            border: 2px solid #333; 
            overflow: hidden;
            background: #000;
            transition: all 0.4s ease;
            margin-bottom: 20px;
        }

        .cam-toggle-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            z-index: 20;
            background: rgba(0, 0, 0, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            backdrop-filter: blur(4px);
            transition: all 0.3s ease;
        }
        .cam-toggle-btn:hover {
            transform: scale(1.05);
            background: rgba(0, 0, 0, 0.8);
        }
        .cam-toggle-btn.cam-off {
            background: var(--danger);
            border-color: var(--danger);
        }

        .scan-line {
            position: absolute;
            width: 100%;
            height: 4px;
            background: linear-gradient(to bottom, transparent, var(--primary));
            box-shadow: 0 5px 15px var(--primary);
            top: 0;
            z-index: 10;
            display: none;
            animation: moveScan 2.5s ease-in-out infinite;
        }

        @keyframes moveScan {
            0%, 100% { top: 5%; opacity: 0.3; }
            50% { top: 90%; opacity: 1; }
        }

        .video-container.scanning { border-color: var(--primary); }
        .video-container.scanning .scan-line { display: block; }
        .video-container.success { border-color: var(--success); box-shadow: 0 0 20px rgba(46, 204, 113, 0.2); }
        .video-container.error { border-color: var(--danger); }

        video { width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1); }

        .password-container {
            display: none;
            text-align: left;
            animation: fadeIn 0.4s ease;
        }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            font-size: 12px;
            color: var(--text-muted);
            margin-bottom: 6px;
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 14px 16px;
            background: #111315;
            border: 1px solid var(--border);
            border-radius: 14px;
            color: #fff;
            font-size: 15px;
            box-sizing: border-box;
            outline: none;
            transition: 0.3s;
        }
        .form-group input:focus {
            border-color: var(--primary);
        }
        .btn-submit-pass {
            width: 100%;
            padding: 14px;
            background: var(--primary);
            border: none;
            border-radius: 14px;
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
            transition: 0.2s;
        }
        .btn-submit-pass:hover { background: #2980b9; }

        #status { 
            font-size: 14px; 
            font-weight: 500; 
            min-height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px 15px;
            border-radius: 14px;
            transition: 0.3s;
        }

        .msg-process { color: var(--primary); background: rgba(52, 152, 219, 0.08); border: 1px solid rgba(52, 152, 219, 0.1); }
        .msg-success { color: var(--success); background: rgba(46, 204, 113, 0.1); border: 1px solid rgba(46, 204, 113, 0.2); }
        .msg-error { color: var(--danger); background: rgba(231, 76, 60, 0.1); border: 1px solid rgba(231, 76, 60, 0.2); }

        .swal2-dark-custom {
            background: #1a1c1e !important;
            color: #fff !important;
            border-radius: 24px !important;
            border: 1px solid #2a2d30 !important;
        }
        .forgot-password-link {
            display: block;
            text-align: right;
            margin-top: -5px;
            margin-bottom: 15px;
        }
        .forgot-password-link a {
            color: var(--text-muted);
            font-size: 13px;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .forgot-password-link a:hover {
            color: var(--primary);
        }
    </style>
</head>
<body>

    <div class="card">
        <h3 class="title-header" id="secret-trigger">S-SDI ACCESS</h3>
        
        <div class="mode-container">
            <button class="mode-btn active" id="btn-face-mode" onclick="switchMode('face')"><i class="fa-solid fa-face-smile"></i> Face ID</button>
            <button class="mode-btn" id="btn-pass-mode" onclick="switchMode('pass')"><i class="fa-solid fa-key"></i> Password</button>
        </div>

        <div id="face-section">
            <div class="video-container" id="v-box">
                <button class="cam-toggle-btn" id="cam-toggle" onclick="toggleCamera()" title="Matikan/Nyalakan Kamera">
                    <i class="fa-solid fa-video"></i>
                </button>
                <div class="scan-line"></div>
                <video id="video" autoplay muted playsinline></video>
            </div>
            <div id="status" class="msg-process">Menginisialisasi AI...</div>
        </div>

        <div id="password-section" class="password-container">
            <form id="loginPasswordForm" onsubmit="handlePasswordLogin(event)">
                <div class="form-group">
                    <label for="login-username">Nama Lengkap / Username</label>
                    <input type="text" id="login-username" placeholder="Masukkan nama" required autocomplete="username">
                </div>
                <div class="form-group">
                    <label for="login-password">Kata Sandi (Password)</label>
                    <input type="password" id="login-password" placeholder="Masukkan password" required autocomplete="current-password">
                </div>
                
                <div class="forgot-password-link">
                    <a href="lupa_password.php">Lupa Password?</a>
                </div>

                <div class="forgot-password-link" style="margin-top: 5px;">
                    <a href="update_biometrik.php" style="color: var(--success);"><i class="fa-solid fa-user-plus"></i> Daftarkan Face ID</a>
                </div>
                
                <button type="submit" class="btn-submit-pass">Masuk</button>
            </form>
        </div>
    </div>

    <script>
    const video = document.getElementById('video');
    const status = document.getElementById('status');
    const vBox = document.getElementById('v-box');
    const secretTrigger = document.getElementById('secret-trigger');
    const camToggle = document.getElementById('cam-toggle');

    let isFinished = false;
    let faceMatcher = null;
    let clickCount = 0;
    let localStream = null;
    let scanLoopInterval = null;
    let isCameraOn = true;
    let currentActiveMode = 'face';
    let userRoleMapping = {}; 

    const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);

    secretTrigger.addEventListener('click', () => {
        clickCount++;
        if (clickCount === 5) window.location.href = 'registrasi_wajah.php';
        setTimeout(() => { clickCount = 0; }, 2000);
    });

    function switchMode(targetMode) {
        currentActiveMode = targetMode;
        document.getElementById('btn-face-mode').classList.toggle('active', targetMode === 'face');
        document.getElementById('btn-pass-mode').classList.toggle('active', targetMode === 'pass');

        if (targetMode === 'face') {
            document.getElementById('password-section').style.display = 'none';
            document.getElementById('face-section').style.display = 'block';
            if(!localStream && isCameraOn) startCameraStream();
        } else {
            document.getElementById('face-section').style.display = 'none';
            document.getElementById('password-section').style.display = 'block';
            stopCameraStream();
        }
    }

    async function startCameraStream() {
        try {
            showStatus("Menghubungkan Kamera...", "process");
            localStream = await navigator.mediaDevices.getUserMedia({ 
                video: { facingMode: "user", width: { ideal: 640 }, height: { ideal: 480 } } 
            });
            video.srcObject = localStream;
            isCameraOn = true;
            camToggle.className = "cam-toggle-btn";
            camToggle.innerHTML = '<i class="fa-solid fa-video"></i>';
            
            video.onloadedmetadata = () => {
                showStatus("Scanning Mode...", "process");
                startScanning();
            };
        } catch(err) {
            console.error(err);
            isCameraOn = false;
            showStatus("Kamera Gagal Dimuat. Gunakan Password.", "error");
            setTimeout(() => { switchMode('pass'); }, 1500);
        }
    }

    function stopCameraStream() {
        if(scanLoopInterval) clearInterval(scanLoopInterval);
        if (localStream) {
            localStream.getTracks().forEach(track => track.stop());
            localStream = null;
        }
        video.srcObject = null;
    }

    function toggleCamera() {
        if (isCameraOn) {
            stopCameraStream();
            isCameraOn = false;
            camToggle.className = "cam-toggle-btn cam-off";
            camToggle.innerHTML = '<i class="fa-solid fa-video-slash"></i>';
            showStatus("Kamera Dinonaktifkan.", "process");
        } else {
            isCameraOn = true;
            startCameraStream();
        }
    }

    function showStatus(text, type) {
        status.innerText = text;
        vBox.classList.remove('scanning', 'success', 'error');
        if (type === 'success') {
            status.className = 'msg-success';
            vBox.classList.add('success');
        } else if (type === 'error') {
            status.className = 'msg-error';
            vBox.classList.add('error');
        } else {
            status.className = 'msg-process';
            if ((text.includes("Memindai") || text.includes("Scanning")) && isCameraOn) vBox.classList.add('scanning');
        }
    }

    async function init() {
        const MODEL_URL = 'https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights';
        
        try {
            if (isMobile) {
                await Swal.fire({
                    title: 'Akses Sistem',
                    text: 'Gunakan kamera atau mode sandi untuk verifikasi.',
                    imageUrl: 'https://cdn-icons-png.flaticon.com/512/685/685655.png',
                    imageWidth: 80, imageHeight: 80,
                    confirmButtonText: 'Mulai Sekarang',
                    confirmButtonColor: '#3498db',
                    background: '#1a1c1e', color: '#fff',
                    customClass: { popup: 'swal2-dark-custom' },
                    allowOutsideClick: false
                });
            }

            showStatus("Memuat AI Engine...", "process");
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
            ]).catch(err => {
                throw new Error("Gagal memuat model AI (Cek Internet)");
            });
            
            showStatus("Sinkronisasi Database...", "process");
            const response = await fetch('ambil_data_wajah.php');
            if (!response.ok) throw new Error("Gagal mengambil data dari server");
            
            const dataWajah = await response.json();
            const validDataWajah = dataWajah.filter(user => user.descriptor !== null && user.descriptor !== '' && user.descriptor !== undefined);

            if (!validDataWajah || validDataWajah.length === 0) {
                showStatus("Tidak Ada Biometrik Aktif. Gunakan Password.", "error");
                setTimeout(() => { switchMode('pass'); }, 1500);
                return;
            }

            const LabeledDescriptors = validDataWajah.map(user => {
                let desc = typeof user.descriptor === 'string' ? JSON.parse(user.descriptor) : user.descriptor;
                userRoleMapping[user.nama] = user.role || 'user';
                return new faceapi.LabeledFaceDescriptors(user.nama, [new Float32Array(desc)]);
            });

            faceMatcher = new faceapi.FaceMatcher(LabeledDescriptors, 0.55);
            await startCameraStream();

        } catch (err) {
            console.error("Detail Error:", err);
            showStatus("Gagal: " + err.message, "error");
            switchMode('pass');
        }
    }

    document.addEventListener("DOMContentLoaded", () => {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('status') === 'logout') {
            Swal.fire({
                title: 'Terima Kasih!',
                text: 'Anda telah berhasil keluar dari sistem.',
                icon: 'success',
                background: '#1a1c1e',
                color: '#fff',
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true,
                willClose: () => {
                    window.history.replaceState({}, document.title, window.location.pathname);
                }
            });
        }
    });

    function startScanning() {
        if(scanLoopInterval) clearInterval(scanLoopInterval);
        
        scanLoopInterval = setInterval(async () => {
            if (isFinished || !faceMatcher || !isCameraOn || currentActiveMode !== 'face') return;

            try {
                const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
                    .withFaceLandmarks()
                    .withFaceDescriptor();

                if (detection) {
                    const bestMatch = faceMatcher.findBestMatch(detection.descriptor);
                    
                    if (bestMatch.label !== 'unknown') {
                        isFinished = true;
                        clearInterval(scanLoopInterval); 
                        stopCameraStream();
                        
                        showStatus("ID TERVERIFIKASI", "success");
                        const detectedRole = userRoleMapping[bestMatch.label] || 'user';
                        handleLoginSuccess(bestMatch.label, detectedRole);
                    } else {
                        showStatus("Memindai... Wajah tidak dikenali", "process");
                    }
                } else {
                    showStatus("Posisikan wajah di dalam frame", "process");
                }
            } catch (scanErr) {
                console.error("Loop Error:", scanErr);
            }
        }, 700); 
    }

    function handleLoginSuccess(namaUser, roleUser) {
        Swal.fire({
            title: 'Akses Diterima!',
            text: `Selamat Datang, ${namaUser}`,
            icon: 'success',
            background: '#1a1c1e', 
            color: '#fff',
            timer: 1500, 
            timerProgressBar: true,
            showConfirmButton: false,
            customClass: { popup: 'swal2-dark-custom' }
        }).then(async () => {
            showStatus("Menyiapkan koneksi aman...", "process");
            
            await fetch('set_session.php', { 
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nama: namaUser, role: roleUser }) 
            });
            
            setTimeout(() => {
                if (roleUser === 'admin') {
                    window.location.href = 'admin_dashboard.php';
                } else {
                    window.location.href = 'load_masuk.php';
                }
            }, 1000); 
        });
    }

    async function handlePasswordLogin(e) {
        e.preventDefault();
        const username = document.getElementById('login-username').value.trim();
        const password = document.getElementById('login-password').value;

        Swal.fire({
            title: 'Memverifikasi...',
            allowOutsideClick: false,
            background: '#1a1c1e', color: '#fff',
            didOpen: () => Swal.showLoading()
        });

        try {
            const response = await fetch('login_password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, password })
            });
            
            const result = await response.json();
            Swal.close();

            if (result.status === 'success') {
                isFinished = true;
                stopCameraStream();
                handleLoginSuccess(result.nama, result.role);
            } else {
                Swal.fire({
                    title: 'Verifikasi Gagal',
                    text: result.message || 'Username atau password salah.',
                    icon: 'error',
                    background: '#1a1c1e', color: '#fff',
                    confirmButtonColor: '#e74c3c'
                });
            }
        } catch (error) {
            console.error(error);
            Swal.close();
            Swal.fire({
                title: 'Error Server',
                text: 'Gagal terhubung dengan layanan autentikasi.',
                icon: 'error',
                background: '#1a1c1e', color: '#fff'
            });
        }
    }

    window.onload = init;
    </script>
</body>
</html>