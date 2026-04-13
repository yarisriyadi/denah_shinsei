<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S-ID System</title> 
    <script defer src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        :root {
            --primary: #3498db;
            --success: #2ecc71;
            --bg: #0f1113;
            --card: #1a1c1e;
        }

        body { 
            background: var(--bg); 
            color: white; 
            font-family: 'Inter', 'Segoe UI', sans-serif; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            margin: 0;
            padding: 15px;
            box-sizing: border-box;
        }

        .auth-card { 
            background: var(--card); 
            padding: 30px; 
            border-radius: 28px; 
            text-align: center; 
            border: 1px solid #2a2d30; 
            width: 100%;
            max-width: 400px; 
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
        }

        h3 { 
            margin: 0 0 8px 0; 
            font-weight: 600; 
            letter-spacing: -0.5px; 
            font-size: 1.5rem;
            cursor: default;
            user-select: none; 
        }
        
        #status { 
            font-size: 14px; 
            color: #a0a0a0; 
            margin-bottom: 20px;
            min-height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .video-wrapper {
            position: relative;
            width: 100%;
            aspect-ratio: 3/4; 
            background: #000;
            border-radius: 20px;
            overflow: hidden;
            border: 2px solid #333;
            transition: all 0.4s ease;
        }

        .video-wrapper.active { border-color: var(--primary); box-shadow: 0 0 20px rgba(52, 152, 219, 0.2); }
        .video-wrapper.success { border-color: var(--success); box-shadow: 0 0 20px rgba(46, 204, 113, 0.2); }

        video { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
            transform: scaleX(-1); 
        }

        .scan-line {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(to bottom, transparent, var(--primary));
            box-shadow: 0 0 15px var(--primary);
            z-index: 10;
            animation: moveScan 2.5s ease-in-out infinite;
            display: none;
        }

        @keyframes moveScan {
            0%, 100% { top: 5%; opacity: 0.5; }
            50% { top: 90%; opacity: 1; }
        }

        .swal2-dark-custom {
            background: #1a1c1e !important;
            color: #fff !important;
            border-radius: 24px !important;
            border: 1px solid #2a2d30 !important;
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <h3 id="secret-trigger">S-ID SYSTEM ACCESS</h3>
        <p id="status">Menginisialisasi AI...</p>
        
        <div class="video-wrapper" id="v-wrap">
            <div class="scan-line" id="line"></div>
            <video id="video" autoplay muted playsinline></video>
        </div>
    </div>

    <script>
        const video = document.getElementById('video');
        const status = document.getElementById('status');
        const vWrap = document.getElementById('v-wrap');
        const line = document.getElementById('line');
        const secretTrigger = document.getElementById('secret-trigger');

        const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
        let isFinished = false;
        let clickCount = 0;

        secretTrigger.addEventListener('click', () => {
            clickCount++;
            if (clickCount === 5) {
                window.location.href = 'registrasi_wajah.php';
            }
            setTimeout(() => { clickCount = 0; }, 2000);
        });

        async function startSystem() {
            const MODEL_URL = 'https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights';
            
            try {
                if (isMobile) {
    await Swal.fire({
        title: 'Akses Sistem',
        text: 'Gunakan kamera untuk verifikasi wajah.',
        imageUrl: 'https://cdn-icons-png.flaticon.com/512/685/685655.png',
        imageWidth: 100, // Ukuran lebar ikon
        imageHeight: 100, // Ukuran tinggi ikon
        imageAlt: 'Camera Icon',
        confirmButtonText: 'Aktifkan',
        confirmButtonColor: '#3498db',
        background: '#1a1c1e',
        color: '#fff',
        customClass: { popup: 'swal2-dark-custom' },
        allowOutsideClick: false
    });
}

                status.innerText = "Memuat AI Engine...";
                await Promise.all([
                    faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                    faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                    faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
                ]);
                
                status.innerText = "Menghubungkan Kamera...";
                const stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { facingMode: "user", width: { ideal: 480 }, height: { ideal: 640 } } 
                });
                video.srcObject = stream;

            } catch (err) {
                status.innerText = "Koneksi Gagal.";
                status.style.color = "#e74c3c";
            }
        }

        video.addEventListener('play', async () => {
            try {
                const response = await fetch('ambil_data_wajah.php');
                const dataWajah = await response.json();

                if (!dataWajah || dataWajah.length === 0) {
                    status.innerText = "Database Kosong.";
                    return;
                }

                const labeledDescriptors = dataWajah.map(user => {
                    return new faceapi.LabeledFaceDescriptors(user.nama, [new Float32Array(user.descriptor)]);
                });

                const faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, 0.5);
                
                status.innerText = "Scanning Mode...";
                line.style.display = "block";
                vWrap.classList.add('active');

                const scanLoop = setInterval(async () => {
                    if (isFinished) return;

                    const detections = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
                        .withFaceLandmarks()
                        .withFaceDescriptor();

                    if (detections) {
                        const bestMatch = faceMatcher.findBestMatch(detections.descriptor);
                        
                        if (bestMatch.label !== 'unknown') {
                            isFinished = true;
                            clearInterval(scanLoop); 
                            
                            status.innerHTML = `<b style='color: #2ecc71'>ID TERVERIFIKASI</b>`;
                            vWrap.classList.replace('active', 'success');
                            line.style.display = "none";
                            
                            Swal.fire({
                                title: 'Akses Diterima!',
                                text: `Nama: ${bestMatch.label}`,
                                icon: 'success',
                                background: '#1a1c1e',
                                color: '#fff',
                                timer: 2000,
                                timerProgressBar: true,
                                showConfirmButton: false,
                                customClass: { popup: 'swal2-dark-custom' }
                            }).then(() => {
                                fetch('set_session.php', { 
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ nama: bestMatch.label }) 
                                }).then(() => {
                                    window.location.href = 'index.php';
                                });
                            });
                        }
                    }
                }, 800);
            } catch (e) {
                status.innerText = "Sistem Error.";
            }
        });

        window.onload = startSystem;
    </script>
</body>
</html>