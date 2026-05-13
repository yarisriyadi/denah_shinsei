<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" type="image/svg+xml" href="assets/logo2.svg" sizes="any">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>S-ID System | Verifikasi Wajah</title> 
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

        h3.title-header { 
            margin: 0 0 8px 0; 
            font-weight: 600; 
            letter-spacing: -0.5px; 
            font-size: 1.5rem;
            cursor: pointer;
            user-select: none;
        }

        .desc { 
            color: var(--text-muted); 
            font-size: 13px; 
            margin-bottom: 25px; 
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
    </style>
</head>
<body>

    <div class="card">
        <h3 class="title-header" id="secret-trigger">S-ID ACCESS</h3>
        <p class="desc">Verifikasi identitas melalui pemindaian wajah</p>
        
        <div class="video-container" id="v-box">
            <div class="scan-line"></div>
            <video id="video" autoplay muted playsinline></video>
        </div>
        
        <div id="status" class="msg-process">Menginisialisasi AI...</div>
    </div>

    <script>
    const video = document.getElementById('video');
    const status = document.getElementById('status');
    const vBox = document.getElementById('v-box');
    const secretTrigger = document.getElementById('secret-trigger');

    let isFinished = false;
    let faceMatcher = null;
    let clickCount = 0;
    const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);

    secretTrigger.addEventListener('click', () => {
        clickCount++;
        if (clickCount === 5) window.location.href = 'registrasi_wajah.php';
        setTimeout(() => { clickCount = 0; }, 2000);
    });

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
            if (text.includes("Memindai") || text.includes("Scanning")) vBox.classList.add('scanning');
        }
    }

    async function init() {
        const MODEL_URL = 'https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights';
        
        try {
            if (isMobile) {
                await Swal.fire({
                    title: 'Akses Sistem',
                    text: 'Gunakan kamera untuk verifikasi wajah.',
                    imageUrl: 'https://cdn-icons-png.flaticon.com/512/685/685655.png',
                    imageWidth: 80, imageHeight: 80,
                    confirmButtonText: 'Aktifkan',
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
                throw new Error("Gagal memuat model AI (Cek Koneksi Internet)");
            });
            
            showStatus("Sinkronisasi Database...", "process");
            const response = await fetch('ambil_data_wajah.php');
            if (!response.ok) throw new Error("Gagal mengambil data dari server");
            
            const dataWajah = await response.json();
            if (!dataWajah || dataWajah.length === 0) {
                showStatus("Database Kosong. Silahkan Registrasi.", "error");
                return;
            }

            const labeledDescriptors = dataWajah.map(user => {
                let desc = typeof user.descriptor === 'string' ? JSON.parse(user.descriptor) : user.descriptor;
                return new faceapi.LabeledFaceDescriptors(user.nama, [new Float32Array(desc)]);
            });

            faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, 0.55);

            showStatus("Menghubungkan Kamera...", "process");
            const stream = await navigator.mediaDevices.getUserMedia({ 
                video: { facingMode: "user", width: { ideal: 640 }, height: { ideal: 480 } } 
            });
            video.srcObject = stream;
            
            video.onloadedmetadata = () => {
                showStatus("Scanning Mode...", "process");
                startScanning();
            };

        } catch (err) {
            console.error("Detail Error:", err);
            showStatus("Gagal: " + err.message, "error");
            
            Swal.fire({
                title: 'Kesalahan Sistem',
                text: err.message,
                icon: 'error',
                background: '#1a1c1e', color: '#fff',
                confirmButtonColor: '#e74c3c'
            });
        }
    }

    function startScanning() {
        const scanLoop = setInterval(async () => {
            if (isFinished || !faceMatcher) return;

            try {
                const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
                    .withFaceLandmarks()
                    .withFaceDescriptor();

                if (detection) {
                    const bestMatch = faceMatcher.findBestMatch(detection.descriptor);
                    
if (bestMatch.label !== 'unknown') {
    isFinished = true;
    clearInterval(scanLoop); 
    
    showStatus("ID TERVERIFIKASI", "success");
    
    Swal.fire({
        title: 'Akses Diterima!',
        text: `Selamat Datang, ${bestMatch.label}`,
        icon: 'success',
        background: '#1a1c1e', 
        color: '#fff',
        timer: 1500, 
        timerProgressBar: true,
        showConfirmButton: false,
        customClass: { popup: 'swal2-dark-custom' }
    }).then(async () => {
        await fetch('set_session.php', { 
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nama: bestMatch.label }) 
        });
        
        showStatus("Menyiapkan koneksi aman...", "process");

        setTimeout(() => {
            window.location.href = 'load_masuk.php';
        }, 3000); 
    });

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

    window.onload = init;
</script>
</body>
</html>