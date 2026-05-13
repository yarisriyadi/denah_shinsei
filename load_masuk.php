<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>S-ID System | Menghubungkan...</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary: #3498db;
            --bg: #0f1113;
            --card-bg: #1a1c1e;
            --border: #2a2d30;
        }

        body {
            background: var(--bg);
            color: white;
            font-family: 'Inter', 'Segoe UI', sans-serif;
            margin: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .splash-container {
            text-align: center;
            width: 90%;
            max-width: 500px;
        }

        .logo-display {
            position: relative;
            width: 180px; 
            height: 180px;
            margin: 0 auto 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @media (min-width: 768px) {
            .logo-display {
                width: 240px;
                height: 240px;
            }
        }

        .logo-img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: drop-shadow(0 0 25px rgba(52, 152, 219, 0.3));
            z-index: 2;
        }

        .biometric-scan {
            position: absolute;
            width: 110%;
            height: 4px;
            background: var(--primary);
            box-shadow: 0 0 20px var(--primary), 0 0 40px var(--primary);
            left: -5%;
            z-index: 3;
            border-radius: 10px;
            animation: scanLoop 2s infinite ease-in-out;
        }

        @keyframes scanLoop {
            0% { top: 0%; opacity: 0; }
            50% { opacity: 1; }
            100% { top: 100%; opacity: 0; }
        }

        .status-box {
            margin-top: 20px;
        }

        .status-text {
            font-size: 16px;
            font-weight: 500;
            color: #fff;
            letter-spacing: 1px;
            margin-bottom: 15px;
            height: 20px;
        }

        .progress-rail {
            width: 100%;
            max-width: 280px;
            height: 6px;
            background: var(--border);
            border-radius: 20px;
            margin: 0 auto;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.05);
        }

        .progress-fill {
            width: 0%;
            height: 100%;
            background: linear-gradient(90deg, var(--primary), #2ecc71);
            box-shadow: 0 0 15px var(--primary);
            transition: width 0.4s cubic-bezier(0.1, 0.4, 0.4, 1);
        }

        .glow {
            position: absolute;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(52, 152, 219, 0.15) 0%, transparent 70%);
            z-index: 1;
        }

        .swal2-dark-custom {
            background: #1a1c1e !important;
            color: #fff !important;
            border-radius: 28px !important;
            border: 1px solid #2a2d30 !important;
        }
    </style>
</head>
<body>

    <div class="glow"></div>

    <div class="splash-container">
        <div class="logo-display">
            <div class="biometric-scan"></div>
            <img src="assets/icon.webp" alt="S-ID Logo" class="logo-img">
        </div>
        
        <div class="status-box">
            <div class="status-text" id="load-msg">Mengautentikasi...</div>
            <div class="progress-rail">
                <div class="progress-fill" id="bar"></div>
            </div>
        </div>
    </div>

    <script>
        const bar = document.getElementById('bar');
        const msg = document.getElementById('load-msg');
        
        const processSteps = [
            "Memverifikasi Identitas...",
            "Membangun Sesi Aman...",
            "Sinkronisasi Enkripsi...",
            "Mempersiapkan Dashboard...",
            "Akses Diberikan!"
        ];

        function startLoading() {
            let progress = 0;
            let step = 0;

            const timer = setInterval(() => {
                if (!navigator.onLine) {
                    clearInterval(timer);
                    showNetworkError();
                    return;
                }

                if (progress >= 100) {
                    clearInterval(timer);
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 600);
                } else {
                    progress += Math.floor(Math.random() * 12) + 5; 
                    if (progress > 100) progress = 100;
                    
                    bar.style.width = progress + '%';

                    if (progress > (step + 1) * 20 && step < processSteps.length) {
                        msg.innerText = processSteps[step];
                        step++;
                    }
                }
            }, 500);
        }

        function showNetworkError() {
            Swal.fire({
                title: 'Koneksi Terputus!',
                text: 'Silahkan periksa jaringan internet Anda untuk masuk ke sistem.',
                icon: 'error',
                background: '#1a1c1e',
                color: '#fff',
                confirmButtonText: 'Coba Lagi',
                confirmButtonColor: '#e74c3c',
                allowOutsideClick: false,
                customClass: { popup: 'swal2-dark-custom' }
            }).then(() => {
                window.location.href = 'verifikasi.php';
            });
        }

        window.onload = () => {
            if (!navigator.onLine) {
                showNetworkError();
            } else {
                startLoading();
            }
        };

        window.addEventListener('offline', showNetworkError);
    </script>
</body>
</html>