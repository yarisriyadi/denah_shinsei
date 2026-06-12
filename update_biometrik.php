<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" type="image/svg+xml" href="assets/logo2.svg" sizes="any">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>S-SDI System | Registrasi Biometrik</title> 
    <script defer src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #3498db;
            --primary-glow: rgba(52, 152, 219, 0.25);
            --success: #2ecc71;
            --danger: #e74c3c;
            --bg: #090a0f;
            --card-bg: #13151a;
            --input-bg: #1c1f26;
            --text-muted: #717784;
            --border: #262a34;
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
            background-image: radial-gradient(circle at 50% 50%, #16192b 0%, var(--bg) 80%);
        }

        .card {
            background: var(--card-bg);
            padding: 35px 30px;
            border-radius: 24px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.6), 0 0 40px rgba(0,0,0,0.3);
            text-align: center;
            width: 100%;
            max-width: 440px; 
            border: 1px solid var(--border);
            box-sizing: border-box;
            backdrop-filter: blur(10px);
        }

.brand-icon-container {
    width: 80px;
    height: 80px;
    margin: 0 auto 20px auto;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    animation: biometrikPulse 3s infinite ease-in-out;
}
.brand-icon-container img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    filter: drop-shadow(0px 8px 16px rgba(52, 152, 219, 0.25)); 
}
@keyframes biometrikPulse {
    0%, 100% { transform: scale(1); filter: drop-shadow(0 4px 10px rgba(52, 152, 219, 0.25)); }
    50% { transform: scale(1.05); filter: drop-shadow(0 8px 20px rgba(52, 152, 219, 0.5)); }
}

        h3.title-header { 
            margin: 0 0 8px 0; 
            font-weight: 700; 
            letter-spacing: 0.5px; 
            font-size: 1.45rem;
            background: linear-gradient(135deg, #ffffff 0%, #a5b4fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .title-desc {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin: 0 0 25px 0;
            line-height: 1.4;
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

        .form-group {
            text-align: left;
            margin-bottom: 24px;
        }
        .form-group label {
            display: block;
            font-size: 0.8rem;
            color: #a3aed0;
            margin-bottom: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .input-icon-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .input-icon-wrapper i {
            position: absolute;
            left: 16px;
            color: var(--text-muted);
            font-size: 1.1rem;
            transition: 0.3s;
        }
        .form-group input {
            width: 100%;
            padding: 15px 16px 15px 46px;
            background: var(--input-bg);
            border: 1px solid var(--border);
            border-radius: 14px;
            color: #fff;
            font-size: 15px;
            box-sizing: border-box;
            outline: none;
            transition: all 0.3s ease;
        }
        .form-group input:focus { 
            border-color: var(--primary); 
            box-shadow: 0 0 0 4px var(--primary-glow);
            background: #222630;
        }
        .form-group input:focus + i {
            color: var(--primary);
        }

        .button-layout-group {
            display: grid;
            grid-template-columns: 1.8fr 1fr;
            gap: 12px;
            width: 100%;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 15px;
            border: none;
            border-radius: 14px;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            box-sizing: border-box;
            text-decoration: none;
        }
        .btn-primary { 
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); 
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }
        .btn-primary:hover { 
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
        }
        .btn-primary:active { transform: translateY(0); }

        .btn-secondary { 
            background: #1e222b;
            border: 1px solid var(--border);
            color: #f3f4f6;
        }
        .btn-secondary:hover { 
            background: #282e3b;
            border-color: #4b5563;
            color: #fff;
            transform: translateY(-2px);
        }
        .btn-secondary:active { transform: translateY(0); }

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

        /* --- Style Pesan Kompatibel dengan verifikasi.php --- */
        .msg-process { color: var(--primary); background: rgba(52, 152, 219, 0.08); border: 1px solid rgba(52, 152, 219, 0.1); }
        .msg-success { color: var(--success); background: rgba(46, 204, 113, 0.1); border: 1px solid rgba(46, 204, 113, 0.2); }
        .msg-error { color: var(--danger); background: rgba(231, 76, 60, 0.1); border: 1px solid rgba(231, 76, 60, 0.2); }

        .swal2-dark-custom {
            background: #1a1c1e !important;
            color: #fff !important;
            border-radius: 24px !important;
            border: 1px solid #2a2d30 !important;
        }
    </style>
</head>
<body>

    <div class="card">
    <div class="brand-icon-container">
        <img src="https://cdn-icons-png.flaticon.com/512/685/685655.png" alt="Face Recognition Icon">
    </div>
    
    <h3 class="title-header">REGISTRASI FACE ID</h3>
        <p class="title-desc">Daftarkan data biometrik wajah Anda untuk proteksi akses instan ke sistem.</p>
        
        <form id="checkAccountForm" onsubmit="verifyUserAccount(event)">
            <div class="form-group">
                <label for="reg-nama">Identitas Akun</label>
                <div class="input-icon-wrapper">
                    <input type="text" id="reg-nama" placeholder="Masukan Nama Yang Terdaftar" required>
                    <i class="fa-solid fa-user-shield"></i>
                </div>
            </div>
            
            <div class="button-layout-group">
                <button type="submit" class="btn-action btn-primary">
                     Verifikasi Akun
                </button>
                <button type="button" class="btn-action btn-secondary" onclick="window.location.href='verifikasi.php'">
                     Kembali
                </button>
            </div>
        </form>

        <div id="camera-section" style="display: none;">
            <div class="video-container" id="v-box">
                <button class="cam-toggle-btn" id="cam-toggle" onclick="toggleCamera()" title="Matikan/Nyalakan Kamera">
                    <i class="fa-solid fa-video"></i>
                </button>
                <div class="scan-line"></div>
                <video id="video" autoplay muted playsinline></video>
            </div>
            <div id="status" class="msg-process">Menginisialisasi AI Engine...</div>
        </div>
    </div>

    <script>
    const checkForm = document.getElementById('checkAccountForm');
    const cameraSection = document.getElementById('camera-section');
    const video = document.getElementById('video');
    const status = document.getElementById('status');
    const vBox = document.getElementById('v-box');
    const camToggle = document.getElementById('cam-toggle');
    
    let verifiedName = "";
    let localStream = null;
    let aiLoaded = false;
    let currentDescriptor = null;
    let isCapturing = false; 
    let detectionInterval = null;
    let isCameraOn = true;

    async function loadAIEngine() {
        const MODEL_URL = 'https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights';
        try {
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
            ]);
            aiLoaded = true;
            if(cameraSection.style.display === 'block' && isCameraOn) {
                showStatus("AI Siap. Posisikan wajah Anda di depan kamera.", "process");
            }
        } catch(err) {
            console.error(err);
            showStatus("Gagal memuat AI Engine. Periksa internet.", "error");
        }
    }

    async function verifyUserAccount(e) {
        e.preventDefault();
        const nama = document.getElementById('reg-nama').value.trim();

        Swal.fire({
            title: 'Memvalidasi Data...',
            allowOutsideClick: false,
            background: '#1a1c1e', color: '#fff',
            customClass: { popup: 'swal2-dark-custom' },
            didOpen: () => Swal.showLoading()
        });

        try {
            const response = await fetch('cek_user_biometrik.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nama: nama })
            });
            
            const result = await response.json();
            Swal.close();

            if (result.status === 'success') {
                verifiedName = result.nama;
                checkForm.style.display = 'none';
                cameraSection.style.display = 'block';
                
                initAIAndCamera();
            } else if (result.status === 'already_registered') {
                Swal.fire({
                    title: 'Registrasi Ditolak',
                    text: result.message, // "Wajah sudah terdaftar pada username ..."
                    icon: 'warning',
                    background: '#1a1c1e', color: '#fff',
                    confirmButtonColor: '#3498db',
                    customClass: { popup: 'swal2-dark-custom' }
                });
            } else {
                Swal.fire({
                    title: 'Data Tidak Ditemukan',
                    text: result.message || 'Nama yang Anda masukkan belum terdaftar di sistem.',
                    icon: 'error',
                    background: '#1a1c1e', color: '#fff',
                    confirmButtonColor: '#e74c3c',
                    customClass: { popup: 'swal2-dark-custom' }
                });
            }
        } catch (err) {
            console.error(err);
            Swal.close();
            Swal.fire({
                title: 'Gagal Terhubung',
                text: 'Terjadi kesalahan saat menghubungi server.',
                icon: 'error',
                background: '#1a1c1e', color: '#fff',
                customClass: { popup: 'swal2-dark-custom' }
            });
        }
    }

    async function initAIAndCamera() {
        if(detectionInterval) clearInterval(detectionInterval); 
        isCapturing = false;

        try {
            showStatus("Menghubungkan Kamera...", "process");
            localStream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: "user", width: { ideal: 640 }, height: { ideal: 480 } }
            });
            video.srcObject = localStream;
            isCameraOn = true;
            camToggle.className = "cam-toggle-btn";
            camToggle.innerHTML = '<i class="fa-solid fa-video"></i>';
            
            if(!aiLoaded) {
                showStatus("Memuat model AI Engine...", "process");
                await loadAIEngine();
            }

            video.onloadedmetadata = () => {
                showStatus("Scanning Mode... Harap diam sebentar.", "process");
                startDetectionLoop();
            };
        } catch (err) {
            console.error(err);
            isCameraOn = false;
            showStatus("Gagal mengakses kamera: " + err.message, "error");
        }
    }

    function stopStream() {
        if(detectionInterval) clearInterval(detectionInterval);
        if(localStream) {
            localStream.getTracks().forEach(track => track.stop());
            localStream = null;
        }
        video.srcObject = null;
    }

    function toggleCamera() {
        if (isCameraOn) {
            stopStream();
            isCameraOn = false;
            camToggle.className = "cam-toggle-btn cam-off";
            camToggle.innerHTML = '<i class="fa-solid fa-video-slash"></i>';
            showStatus("Kamera Dinonaktifkan.", "process");
        } else {
            isCameraOn = true;
            initAIAndCamera();
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
            if ((text.includes("Mendeteksi") || text.includes("Scanning") || text.includes("Posisikan")) && isCameraOn) {
                vBox.classList.add('scanning');
            }
        }
    }

    function startDetectionLoop() {
        detectionInterval = setInterval(async () => {
            if (!aiLoaded || !localStream || isCapturing || !isCameraOn) return;
            
            try {
                const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
                    .withFaceLandmarks()
                    .withFaceDescriptor();
                
                if (detection && !isCapturing) {
                    isCapturing = true; 
                    clearInterval(detectionInterval); 
                    
                    currentDescriptor = detection.descriptor;
                    showStatus("Wajah Terdeteksi! Memproses data...", "success");
                    
                    await autoCaptureBiometric();
                } else if(!isCapturing) {
                    currentDescriptor = null;
                    showStatus("Posisikan wajah di dalam frame", "process");
                }
            } catch(e) {
                console.error("Loop Error:", e);
            }
        }, 700);
    }

    async function autoCaptureBiometric() {
        if (!currentDescriptor) return;

        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        
        ctx.translate(canvas.width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        const base64Image = canvas.toDataURL('image/jpeg', 0.6); 

        try {
            const response = await fetch('simpan_biometrik_baru.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    nama: verifiedName,
                    descriptor: Array.from(currentDescriptor),
                    foto: base64Image
                })
            });

            const textData = await response.text();
            let result;
            try {
                result = JSON.parse(textData);
            } catch(e) {
                console.error("Respon Server Rusak:", textData);
                throw new Error("Server mengirimkan response non-JSON (Kemungkinan Error 500).");
            }

            if (result.status === 'success') {
                stopStream();

                Swal.fire({
                    title: 'Berhasil Menambahkan Biometrik!',
                    text: 'Face ID Anda berhasil dikonfigurasi. Silakan menuju ke halaman verifikasi.',
                    icon: 'success',
                    background: '#1a1c1e', color: '#fff',
                    confirmButtonColor: '#2ecc71',
                    customClass: { popup: 'swal2-dark-custom' },
                    allowOutsideClick: false
                }).then(() => {
                    window.location.href = 'verifikasi.php';
                });
            } else {
                showErrorNotification(result.message);
            }
        } catch (err) {
            console.error(err);
            showErrorNotification("Gagal memproses data ke database server.");
        }
    }

    function showErrorNotification(msg) {
        stopStream();
        Swal.fire({
            title: 'Sistem Mengalami Kendala',
            text: msg + " Silakan set ulang pemindaian kamera Anda.",
            icon: 'warning',
            background: '#1a1c1e', color: '#fff',
            confirmButtonColor: '#3498db',
            customClass: { popup: 'swal2-dark-custom' }
        }).then(() => {
            setTimeout(() => {
                initAIAndCamera();
            }, 1000);
        });
    }

    window.addEventListener('DOMContentLoaded', loadAIEngine);
    </script>
</body>
</html>