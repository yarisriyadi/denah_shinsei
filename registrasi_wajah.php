<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Registrasi Wajah - Shinsei Map</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary: #3498db;
            --primary-glow: rgba(52, 152, 219, 0.25);
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

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
            width: 100%;
        }

        .btn-back { 
            position: relative; 
            color: white; 
            text-decoration: none; 
            background: rgba(255,255,255,0.05);
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px; 
            border: 1px solid var(--border);
            transition: all 0.3s ease;
            flex-shrink: 0; 
        }
        
        .btn-back:hover { 
            background: var(--primary); 
            border-color: var(--primary);
            transform: translateX(-3px);
        }

        h3.title-header { 
            margin: 0; 
            font-weight: 600; 
            letter-spacing: -0.5px; 
            font-size: 1.3rem;
            flex-grow: 1;
            text-align: center;
        }

        .header-spacer {
            width: 40px;
            flex-shrink: 0;
        }

        .desc { 
            color: var(--text-muted); 
            font-size: 13px; 
            margin-bottom: 25px; 
            line-height: 1.4;
            margin-top: 5px;
        }

        .video-container { 
            position: relative; 
            width: 100%; 
            aspect-ratio: 3/4;
            border-radius: 20px; 
            border: 2px solid #2d3135; 
            overflow: hidden;
            background: #000;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
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
            animation: moveScan 2s ease-in-out infinite;
        }

        @keyframes moveScan {
            0%, 100% { top: 5%; opacity: 0.3; }
            50% { top: 95%; opacity: 1; }
        }

        .video-container.scanning { border-color: var(--primary); box-shadow: 0 0 25px var(--primary-glow); }
        .video-container.scanning .scan-line { display: block; }
        .video-container.success { border-color: var(--success); box-shadow: 0 0 25px rgba(46, 204, 113, 0.25); }
        .video-container.error { border-color: var(--danger); box-shadow: 0 0 25px rgba(231, 76, 60, 0.25); }

        video { width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1); }

        /* --- UI Tambahan: Kamera Mati & Tombol Manual --- */
        .cam-placeholder-msg {
            display: none;
            color: var(--text-muted);
            font-size: 14px;
            flex-direction: column;
            gap: 10px;
        }

        .cam-toggle-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            z-index: 20;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            border: none;
            background: rgba(0, 0, 0, 0.6);
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        }
        .cam-toggle-btn:hover { transform: scale(1.05); }
        .cam-toggle-btn.cam-off { background: var(--danger); }

        .btn-register-manual {
            display: none;
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 15px var(--primary-glow);
            transition: all 0.2s ease;
            animation: fadeIn 0.3s ease forwards;
        }
        .btn-register-manual:hover { background: #2980b9; }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }

        .input-container {
            margin-bottom: 5px;
        }

        input { 
            padding: 14px 18px; 
            border-radius: 14px; 
            border: 2px solid var(--border); 
            margin-bottom: 12px; 
            width: 100%; 
            background: #111315; 
            color: white; 
            outline: none; 
            font-size: 15px;
            transition: all 0.3s ease;
            box-sizing: border-box;
            text-align: center;
        }
        
        input:focus { border-color: var(--primary); background: #000; box-shadow: 0 0 10px rgba(52, 152, 219, 0.15); }
        input:disabled { background: #151719; color: #555; border-color: #222; }

        #status { 
            font-size: 14px; 
            font-weight: 500; 
            min-height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px 15px;
            border-radius: 14px;
            transition: all 0.3s ease;
            line-height: 1.4;
        }

        .msg-success { color: var(--success); background: rgba(46, 204, 113, 0.08); border: 1px solid rgba(46, 204, 113, 0.15); }
        .msg-error { color: var(--danger); background: rgba(231, 76, 60, 0.08); border: 1px solid rgba(231, 76, 60, 0.15); }
        .msg-process { color: var(--primary); background: rgba(52, 152, 219, 0.06); border: 1px solid rgba(52, 152, 219, 0.12); }

        /* --- RESPONSIVENESS --- */
        @media (max-width: 480px) {
            .card { padding: 20px; border-radius: 24px; }
            h3.title-header { font-size: 1.15rem; }
            .btn-back, .header-spacer { width: 36px; height: 36px; }
            .video-container { aspect-ratio: 1/1; } 
        }

        @media (max-height: 650px) {
            body { padding: 10px; }
            .card { padding: 15px; }
            .desc { margin-bottom: 15px; }
            .video-container { aspect-ratio: 1/1; max-width: 250px; margin: 0 auto 15px auto; }
        }
    </style>
</head>
<body>

    <div class="card">
        <div class="card-header">
            <a href="verifikasi.php" class="btn-back" title="Kembali">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </a>
            <h3 class="title-header">Registrasi Wajah</h3>
            <div class="header-spacer"></div>
        </div>

        <p class="desc">Ambil data biometrik atau isi form langsung untuk keamanan akses Anda</p>
        
        <div class="input-container">
            <input type="text" id="nama" placeholder="Ketik Nama Lengkap..." autocomplete="off">
            <input type="password" id="password" placeholder="Buat Password Akun..." autocomplete="new-password">
        </div>
        
        <div class="video-container" id="v-box">
            <button class="cam-toggle-btn" id="cam-toggle" onclick="toggleCamera()" title="Matikan/Nyalakan Kamera">
                <i class="fa-solid fa-video"></i>
            </button>
            <div class="scan-line"></div>
            <video id="video" autoplay muted playsinline></video>
            
            <div class="cam-placeholder-msg" id="cam-placeholder">
                <i class="fa-solid fa-user-shield fa-2x" style="color: var(--border);"></i>
                <span>Registrasi Tanpa Wajah</span>
                <button class="btn-register-manual" id="btn-manual" onclick="triggerConfirmationPopup()">Daftar Sekarang</button>
            </div>
        </div>
        
        <div id="status" class="msg-process">Menginisialisasi AI...</div>
    </div>

    <script>
    const video = document.getElementById('video');
    const status = document.getElementById('status');
    const inputNama = document.getElementById('nama');
    const inputPassword = document.getElementById('password');
    const vBox = document.getElementById('v-box');
    const camToggle = document.getElementById('cam-toggle');
    const camPlaceholder = document.getElementById('cam-placeholder');
    const btnManual = document.getElementById('btn-manual');
    
    let isAiReady = false;
    let isProcessing = false;
    let typingTimer = null;
    let scanTimeout = null;
    let isCameraOn = true;
    let streamRef = null;
    const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);

    function handleInputChange() {
        const nama = inputNama.value.trim();
        const password = inputPassword.value;

        if (nama.length >= 3 && password.length >= 6) {
            if (!isCameraOn) {
                btnManual.style.display = "inline-block";
                clearTimeout(typingTimer); 
                showStatus("Data lengkap! Silakan klik tombol di atas untuk daftar.", "process");
                return;
            }
            if (!isAiReady || isProcessing) return;

            showStatus("Data lengkap! Menyiapkan konfirmasi...", "process");
            clearTimeout(typingTimer);
            typingTimer = setTimeout(triggerConfirmationPopup, 800);
        } else {
            if (!isCameraOn) btnManual.style.display = "none";
            
            if (nama.length < 3) {
                showStatus("Ketik nama (min. 3 karakter)", "process");
            } else if (password.length < 6) {
                showStatus("Buat password (min. 6 karakter)", "process");
            }
        }
    }

    inputNama.addEventListener('input', handleInputChange);
    inputPassword.addEventListener('input', handleInputChange);

    async function init() {
        const MODEL_URL = 'https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights';
        
        try {
            if (isMobile) {
                await Swal.fire({
                    title: 'Akses Sistem',
                    text: 'Sistem akan mengakses kamera untuk merekam data wajah Anda.',
                    imageUrl: 'https://cdn-icons-png.flaticon.com/512/685/685655.png', 
                    imageWidth: 80,
                    imageHeight: 80,
                    imageAlt: 'Icon Kamera',
                    confirmButtonText: 'Mulai Sekarang',
                    confirmButtonColor: '#3498db',
                    background: '#1a1c1e',
                    color: '#fff'
                });
            }

            showStatus("Memuat AI Engine...", "process");
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
            ]);
            
            isAiReady = true;
            await startCameraStream();

        } catch (err) {
            console.error(err);
            let errMsg = "Gagal memuat kamera.";
            if (err.name === 'NotAllowedError') {
                errMsg = "Akses ditolak. Berikan izin kamera di pengaturan browser HP Anda.";
            }

            showStatus(errMsg, "error");
            Swal.fire({
                title: 'Kamera Terblokir',
                text: errMsg,
                icon: 'error',
                background: '#1a1c1e',
                color: '#fff',
                confirmButtonColor: '#e74c3c'
            });
        }
    }

    async function startCameraStream() {
        showStatus("Menghubungkan Kamera...", "process");
        try {
            streamRef = await navigator.mediaDevices.getUserMedia({ 
                video: { 
                    facingMode: "user",
                    width: { ideal: 640 }, 
                    height: { ideal: 480 } 
                } 
            });
            video.srcObject = streamRef;
            video.style.display = "block";
            camPlaceholder.style.display = "none";
            showStatus("Silakan isi lengkap data di atas.", "process");
            handleInputChange();
        } catch (err) {
            console.error("Gagal memulai stream kamera:", err);
            showStatus("Kamera tidak tersedia.", "error");
        }
    }

    function stopCameraStream() {
        clearTimeout(scanTimeout);
        if (streamRef) {
            streamRef.getTracks().forEach(track => track.stop());
        }
        video.srcObject = null;
        video.style.display = "none";
        camPlaceholder.style.display = "flex";
    }

    function toggleCamera() {
        if (isProcessing) return;

        if (isCameraOn) {
            stopCameraStream();
            isCameraOn = false;
            camToggle.className = "cam-toggle-btn cam-off";
            camToggle.innerHTML = '<i class="fa-solid fa-video-slash"></i>';
            showStatus("Kamera Dinonaktifkan.", "process");
            handleInputChange();
        } else {
            isCameraOn = true;
            camToggle.className = "cam-toggle-btn";
            camToggle.innerHTML = '<i class="fa-solid fa-video"></i>';
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
            if (text.includes("Memindai") && isCameraOn) vBox.classList.add('scanning');
        }
    }

    function triggerConfirmationPopup() {
        if (isProcessing) return;

        const nama = inputNama.value.trim();
        const password = inputPassword.value;

        if (nama.length < 3 || password.length < 6) return;

        isProcessing = true;
        inputNama.disabled = true;
        inputPassword.disabled = true;

        Swal.fire({
            title: 'Konfirmasi Data',
            html: `<div style="text-align: left; font-size: 15px; line-height: 1.6;">
                    <p>Apakah data pendaftaran Anda sudah sesuai?</p>
                    <strong>Nama:</strong> <span style="color: var(--primary);">${nama}</span><br>
                    <strong>Password:</strong> <span style="color: var(--text-muted);">${password}</span><br>
                    <strong>Metode:</strong> <span>${isCameraOn ? 'Biometrik Wajah' : 'Tanpa Wajah (Manual)'}</span>
                   </div>`,
            icon: 'question',
            showCancelButton: true,
            reverseButtons: true,
            confirmButtonText: 'Ya, Sudah Sesuai',
            cancelButtonText: 'Belum, Ubah Lagi',
            confirmButtonColor: '#2ecc71',
            cancelButtonColor: '#e74c3c',
            background: '#1a1c1e',
            color: '#fff',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                if (isCameraOn) {
                    startAutomatedScan(nama, password);
                } else {
                    handleRegistration(null, nama, password);
                }
            } else {
                isProcessing = false;
                inputNama.disabled = false;
                inputPassword.disabled = false;
                showStatus("Silakan perbarui data Anda.", "process");
                handleInputChange();
            }
        });
    }

    async function startAutomatedScan(nama, password) {
        if (!isCameraOn) return;
        showStatus("Memindai Wajah... Tetap diam menghadap kamera", "process");

        async function performDetection() {
            if (!isProcessing || !isCameraOn) return;

            const detection = await faceapi.detectSingleFace(
                video, 
                new faceapi.TinyFaceDetectorOptions()
            ).withFaceLandmarks().withFaceDescriptor();

            if (detection) {
                handleRegistration(detection, nama, password);
            } else {
                scanTimeout = setTimeout(performDetection, 200);
            }
        }

        performDetection();
    }

    async function handleRegistration(detection, nama, password) {
        let fotoBase64 = "";
        let descriptor = [];

        if (detection && isCameraOn) {
            showStatus("Menangkap foto & sinkronisasi...", "process");

            const canvas = document.createElement('canvas');
            canvas.width = 400;  
            canvas.height = 400; 
            const ctx = canvas.getContext('2d');
            
            const videoRatio = video.videoWidth / video.videoHeight;
            let srcX = 0, srcY = 0, srcWidth = video.videoWidth, srcHeight = video.videoHeight;
            
            if (videoRatio > 1) {
                srcWidth = video.videoHeight;
                srcX = (video.videoWidth - srcWidth) / 2;
            } else {
                srcHeight = video.videoWidth;
                srcY = (video.videoHeight - srcHeight) / 2;
            }

            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, srcX, srcY, srcWidth, srcHeight, 0, 0, canvas.width, canvas.height);
            
            fotoBase64 = canvas.toDataURL('image/jpeg', 0.7);
            descriptor = Array.from(detection.descriptor);
        } else {
            showStatus("Mengirim data pendaftaran...", "process");
        }

        try {
            const simpan = await fetch('simpan_wajah.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    nama: nama, 
                    password: password,
                    descriptor: descriptor,
                    foto: fotoBase64        
                })
            });

            const rawResponse = await simpan.text();
            let resSimpan;
            
            try {
                resSimpan = JSON.parse(rawResponse.trim());
            } catch(e) {
                console.error("Format respons bukan JSON murni:", rawResponse);
                throw new Error("Terjadi masalah konfigurasi data pada server.");
            }

            if (simpan.ok && resSimpan.status === 'success') {
                showStatus("Registrasi Berhasil!", "success");
                stopCameraStream();
                Swal.fire({
                    title: 'Berhasil!',
                    text: 'Akun Anda telah aman tersimpan.',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    background: '#1a1c1e', color: '#fff'
                }).then(() => {
                    window.location.href = 'verifikasi.php';
                });
            } else {
                showStatus(resSimpan.message || "Gagal menyimpan data.", "error");
                Swal.fire({
                    title: 'Registrasi Gagal',
                    text: resSimpan.message || "Terjadi kesalahan.",
                    icon: 'error',
                    background: '#1a1c1e', color: '#fff',
                    confirmButtonColor: '#e74c3c'
                });
                
                isProcessing = false;
                inputNama.disabled = false;
                inputPassword.disabled = false;
                if(isCameraOn) startCameraStream();
                handleInputChange();
            }

        } catch (error) {
            console.error(error);
            showStatus("Kesalahan koneksi server.", "error");
            isProcessing = false;
            inputNama.disabled = false;
            inputPassword.disabled = false;
            handleInputChange();
        }
    }

    window.onload = init;
    </script>
</body>
</html>