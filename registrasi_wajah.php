<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Registrasi Wajah - Shinsei Map</title>
    <script defer src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            border: 2px solid #333; 
            overflow: hidden;
            background: #000;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            margin-bottom: 20px;
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

        input { 
            padding: 14px 18px; 
            border-radius: 14px; 
            border: 2px solid var(--border); 
            margin-bottom: 12px; 
            width: 100%; 
            background: #111; 
            color: white; 
            outline: none; 
            font-size: 16px;
            transition: 0.3s;
            box-sizing: border-box;
            text-align: center;
        }
        
        input:focus { border-color: var(--primary); background: #000; box-shadow: 0 0 10px rgba(52, 152, 219, 0.1); }

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
            line-height: 1.4;
        }

        .msg-success { color: var(--success); background: rgba(46, 204, 113, 0.1); border: 1px solid rgba(46, 204, 113, 0.2); }
        .msg-error { color: var(--danger); background: rgba(231, 76, 60, 0.1); border: 1px solid rgba(231, 76, 60, 0.2); }
        .msg-process { color: var(--primary); background: rgba(52, 152, 219, 0.08); border: 1px solid rgba(52, 152, 219, 0.1); }

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

        <p class="desc">Ambil data biometrik untuk keamanan akses Anda</p>
        
        <input type="text" id="nama" placeholder="Ketik Nama Lengkap..." autocomplete="off">
        <input type="password" id="password" placeholder="Buat Password Akun..." autocomplete="new-password">
        
        <div class="video-container" id="v-box">
            <div class="scan-line"></div>
            <video id="video" autoplay muted playsinline></video>
        </div>
        
        <div id="status" class="msg-process">Menginisialisasi AI...</div>
    </div>

    <script>
    const video = document.getElementById('video');
    const status = document.getElementById('status');
    const inputNama = document.getElementById('nama');
    const inputPassword = document.getElementById('password');
    const vBox = document.getElementById('v-box');
    
    let isProcessing = false;
    const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);

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
            
            showStatus("Menghubungkan Kamera...", "process");
            const stream = await navigator.mediaDevices.getUserMedia({ 
                video: { 
                    facingMode: "user",
                    width: { ideal: 640 }, 
                    height: { ideal: 480 } 
                } 
            });
            video.srcObject = stream;
            
            showStatus("Siap. Silakan isi nama dan password.", "process");
            startScan();
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
            if (text.includes("Memindai")) vBox.classList.add('scanning');
        }
    }

    function startScan() {
        const scanInterval = setInterval(async () => {
            if (isProcessing) return;

            const nama = inputNama.value.trim();
            const password = inputPassword.value;

            if (nama.length < 3) {
                showStatus("Ketik nama (min. 3 karakter)", "process");
                return;
            }

            if (password.length < 6) {
                showStatus("Buat password (min. 6 karakter)", "process");
                return;
            }

            showStatus("Memindai Wajah... Tetap Diam", "process");

            const detection = await faceapi.detectSingleFace(
                video, 
                new faceapi.TinyFaceDetectorOptions()
            ).withFaceLandmarks().withFaceDescriptor();

            if (detection) {
                isProcessing = true;
                clearInterval(scanInterval); 
                handleRegistration(detection, nama, password);
            }
        }, 1000);
    }

    async function handleRegistration(detection, nama, password) {
        showStatus("Menangkap foto & sinkronisasi...", "process");

        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        
        ctx.translate(canvas.width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        const fotoBase64 = canvas.toDataURL('image/jpeg', 0.9);
        const descriptor = Array.from(detection.descriptor);

        try {
            const cek = await fetch('cek_wajah.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ descriptor })
            });
            const resCek = await cek.json();

            if (resCek.exists) {
                showStatus(`Gagal: Wajah ini milik ${resCek.nama}`, "error");
                Swal.fire({
                    title: 'Wajah Sudah Terdaftar',
                    text: `Data biometrik ini sudah digunakan oleh ${resCek.nama}.`,
                    icon: 'warning',
                    background: '#1a1c1e', color: '#fff',
                    confirmButtonColor: '#3498db'
                });
                setTimeout(() => { isProcessing = false; startScan(); }, 4000);
                return;
            }

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
                throw new Error("Terjadi masalah pada format data server.");
            }

            if (simpan.ok && resSimpan.status === 'success') {
                showStatus("Registrasi Berhasil!", "success");
                Swal.fire({
                    title: 'Berhasil!',
                    text: 'Data wajah, akun, dan foto telah disimpan.',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    background: '#1a1c1e', color: '#fff'
                }).then(() => {
                    window.location.href = 'verifikasi.php';
                });
            } else {
                showStatus(resSimpan.message || "Gagal menyimpan data.", "error");
                setTimeout(() => { isProcessing = false; startScan(); }, 3000);
            }

        } catch (error) {
            console.error(error);
            showStatus("Kesalahan server.", "error");
            setTimeout(() => { isProcessing = false; startScan(); }, 3000);
        }
    }

    window.onload = init;
    </script>
</body>
</html>