<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" type="image/svg+xml" href="assets/logo2.svg" sizes="any">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>S-SDI System | Secure OTP Reset</title> 
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #3498db;
            --primary-glow: rgba(52, 152, 219, 0.3);
            --success: #2ecc71;
            --danger: #e74c3c;
            --bg: #0b0c10;
            --card-bg: rgba(26, 28, 30, 0.85);
            --text-muted: #9ca3af;
            --border: #2a2d30;
            --input-bg: #111315;
        }

        body { 
            background: radial-gradient(circle at center, #1f2937 0%, var(--bg) 70%);
            color: #f3f4f6; 
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
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            padding: 40px 30px;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7), 0 0 40px var(--primary-glow);
            text-align: center;
            width: 100%;
            max-width: 440px; 
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-sizing: border-box;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .icon-header {
            width: 65px;
            height: 65px;
            background: rgba(52, 152, 219, 0.1);
            border: 1px solid var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px auto;
            color: var(--primary);
            font-size: 24px;
            box-shadow: 0 0 15px var(--primary-glow);
        }

        h3.title-header { 
            margin: 0 0 10px 0; 
            font-weight: 700; 
            letter-spacing: 0.5px; 
            font-size: 1.6rem;
            background: linear-gradient(to right, #fff, #9ca3af);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .desc-text {
            font-size: 13px;
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 22px;
            text-align: left;
            position: relative;
        }
        
        .form-group label {
            display: block;
            font-size: 11px;
            color: #9ca3af;
            margin-bottom: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper i {
            position: absolute;
            left: 16px;
            color: #4b5563;
            transition: color 0.3s;
        }
        
        .form-group input {
            width: 100%;
            padding: 14px 16px 14px 45px;
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
            box-shadow: 0 0 10px var(--primary-glow);
            background: #141619;
        }

        .form-group input:focus + i {
            color: var(--primary);
        }

        .otp-input {
            letter-spacing: 12px;
            text-align: center;
            font-size: 22px !important;
            font-weight: 700;
            padding-left: 16px !important;
            color: var(--primary) !important;
        }

        .btn-submit {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, var(--primary) 0%, #2980b9 100%);
            border: none;
            border-radius: 14px;
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            margin-bottom: 12px;
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.2);
        }
        
        .btn-submit:hover { 
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
        }

        .btn-back {
            width: 100%;
            padding: 14px;
            background: transparent;
            border: 1px solid var(--border);
            border-radius: 14px;
            color: var(--text-muted);
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            box-sizing: border-box;
        }

        .btn-back:hover {
            border-color: #e74c3c;
            color: #fff;
            background: rgba(231, 76, 60, 0.05);
        }

        .hidden {
            display: none;
            opacity: 0;
        }

        .fade-in-target {
            animation: formReveal 0.4s forwards ease;
        }

        @keyframes formReveal {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <div class="card">
        <div class="icon-header" id="form-icon">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
        <h3 class="title-header" id="form-title">RESET PASSWORD</h3>
        <p class="desc-text" id="form-desc">Masukkan nama akun Anda dan alamat email tujuan untuk menerima kode OTP.</p>
        
        <form id="requestOtpForm" onsubmit="handleRequestOtp(event)">
            <div class="form-group">
                <label for="reset-nama">Nama Akun (Username)</label>
                <div class="input-wrapper">
                    <input type="text" id="reset-nama" placeholder="Nama" required autocomplete="username">
                    <i class="fa-solid fa-user"></i>
                </div>
            </div>
            <div class="form-group">
                <label for="reset-email">Email Penerima OTP</label>
                <div class="input-wrapper">
                    <input type="email" id="reset-email" placeholder="@shinsei-denshi.id" required autocomplete="email">
                    <i class="fa-solid fa-envelope"></i>
                </div>
            </div>
            <button type="submit" class="btn-submit">Kirim Kode OTP</button>
            <button type="button" class="btn-back" onclick="window.location.href='verifikasi.php'">Kembali</button>
        </form>

        <form id="verifyOtpForm" class="hidden" onsubmit="handleVerifyAndReset(event)">
            <div class="form-group">
                <label for="otp-code">Kode Verifikasi OTP</label>
                <div class="input-wrapper">
                    <input type="text" id="otp-code" class="otp-input" maxlength="6" placeholder="******" required autocomplete="off">
                </div>
            </div>
            <div class="form-group">
                <label for="new-password">Kata Sandi Baru</label>
                <div class="input-wrapper">
                    <input type="password" id="new-password" placeholder="Min. 8 Karakter Kombinasi" required autocomplete="new-password">
                    <i class="fa-solid fa-lock"></i>
                </div>
            </div>
            <button type="submit" class="btn-submit">Konfirmasi Perubahan</button>
            <button type="button" class="btn-back" onclick="window.location.reload()">Gunakan Email Lain</button>
        </form>
    </div>

    <script>
    let tempNama = "";
    let tempEmail = "";

    async function handleRequestOtp(e) {
        e.preventDefault();
        tempNama = document.getElementById('reset-nama').value.trim();
        tempEmail = document.getElementById('reset-email').value.trim();

        showLoading('Mengamankan Koneksi...', 'Memeriksa kecocokan data akun...');

        try {
            const response = await fetch('proses_lupa_password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'request_otp', nama: tempNama, email: tempEmail })
            });
            
            const textData = await response.text(); 
            let result;
            try {
                result = JSON.parse(textData);
            } catch(e) {
                console.error("Respon Server Bukan JSON:", textData);
                throw new Error("Respon server bermasalah.");
            }

            Swal.close();

            if (result.status === 'success') {
                Swal.fire({
                    title: 'OTP Dikirim!',
                    text: result.message,
                    icon: 'success',
                    background: '#1a1c1e', color: '#fff', confirmButtonColor: '#3498db',
                    customClass: { popup: 'swal2-dark-custom' }
                });

                document.getElementById('requestOtpForm').style.display = 'none';
                const verifyForm = document.getElementById('verifyOtpForm');
                verifyForm.classList.remove('hidden');
                verifyForm.classList.add('fade-in-target');
                
                document.getElementById('form-icon').innerHTML = '<i class="fa-solid fa-key"></i>';
                document.getElementById('form-title').innerText = "VERIFIKASI AMAN";
                document.getElementById('form-desc').innerText = `Masukkan 6-digit kunci keamanan yang dikirimkan ke kotak masuk email ${tempEmail}.`;
            } else {
                showError('Validasi Gagal', result.message);
            }
        } catch (error) {
            Swal.close();
            showError('Server Terputus', 'Gagal memproses data. Pastikan file proses_lupa_password.php dikonfigurasi dengan benar.');
        }
    }

    async function handleVerifyAndReset(e) {
        e.preventDefault();
        const otpCode = document.getElementById('otp-code').value.trim();
        const newPassword = document.getElementById('new-password').value;

        showLoading('Menyinkronkan Kunci...', 'Memvalidasi kode OTP.');

        try {
            const response = await fetch('proses_lupa_password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    action: 'verify_otp', 
                    nama: tempNama, 
                    otp: otpCode, 
                    password: newPassword 
                })
            });

            const result = await response.json();
            Swal.close();

            if (result.status === 'success') {
                Swal.fire({
                    title: 'Berhasil!',
                    text: result.message,
                    icon: 'success',
                    background: '#1a1c1e', color: '#fff', confirmButtonColor: '#3498db',
                    customClass: { popup: 'swal2-dark-custom' }
                }).then(() => {
                    window.location.href = 'verifikasi.php';
                });
            } else {
                showError('Otorisasi Ditolak', result.message);
            }
        } catch (error) {
            Swal.close();
            showError('Database Error', 'Gagal memodifikasi kata sandi baru ke server.');
        }
    }

    function showLoading(title, text) {
        Swal.fire({
            title: title, text: text, allowOutsideClick: false, background: '#1a1c1e', color: '#fff',
            customClass: { popup: 'swal2-dark-custom' },
            didOpen: () => Swal.showLoading()
        });
    }

    function showError(title, text) {
        Swal.fire({
            title: title, text: text, icon: 'error', background: '#1a1c1e', color: '#fff', confirmButtonColor: '#e74c3c',
            customClass: { popup: 'swal2-dark-custom' }
        });
    }
    </script>
</body>
</html>