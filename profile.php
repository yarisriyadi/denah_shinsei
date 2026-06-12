<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'koneksi.php'; 

if (!isset($_SESSION['terverifikasi']) || $_SESSION['terverifikasi'] !== true) {
    header("Location: verifikasi.php");
    exit();
}

$db = (isset($conn)) ? $conn : (isset($koneksi) ? $koneksi : null);

if (!$db) {
    die("Kesalahan: Variabel koneksi database tidak ditemukan.");
}

$id_terdeteksi = $_SESSION['user_id'] ?? null;
$namaUser = "Guest";
$fotoUser = "";

if ($id_terdeteksi) {
    $query = "SELECT nama, foto FROM data_wajah WHERE id = ?";
    $stmt = $db->prepare($query);
    if ($stmt) {
        $stmt->bind_param("s", $id_terdeteksi);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $namaUser = $row['nama'];
            $fotoUser = $row['foto'];
        }
        $stmt->close();
    }
}

// Tambahan: Logika Ucapan Waktu Dinamis
date_default_timezone_set('Asia/Jakarta');
$jam = date('H');
if ($jam >= 5 && $jam < 11) {
    $ucapan = "Selamat Pagi";
    $ikonWaktu = "☀️";
} elseif ($jam >= 11 && $jam < 15) {
    $ucapan = "Selamat Siang";
    $ikonWaktu = "🌤️";
} elseif ($jam >= 15 && $jam < 18) {
    $ucapan = "Selamat Sore";
    $ikonWaktu = "⛅";
} else {
    $ucapan = "Selamat Malam";
    $ikonWaktu = "🌙";
}

if (isset($_FILES['profile_pix']) && $_FILES['profile_pix']['error'] === 0) {
    $namaFile = $_FILES['profile_pix']['name'];
    $tmpName  = $_FILES['profile_pix']['tmp_name'];
    $ekstensiValid = ['jpg', 'jpeg', 'png'];
    $ekstensiFile  = strtolower(pathinfo($namaFile, PATHINFO_EXTENSION));

    if (in_array($ekstensiFile, $ekstensiValid)) {
        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }

        if ($fotoUser && file_exists('uploads/'.$fotoUser)) {
            unlink('uploads/'.$fotoUser);
        }

        $namaFileBaru = $namaUser . "_". $id_terdeteksi . "_" . time() . "." . $ekstensiFile;
        
        if (move_uploaded_file($tmpName, 'uploads/' . $namaFileBaru)) {
            $update = "UPDATE data_wajah SET foto = ? WHERE id = ?";
            $stmt_upd = $db->prepare($update);
            if ($stmt_upd) {
                $stmt_upd->bind_param("ss", $namaFileBaru, $id_terdeteksi);
                $stmt_upd->execute();
                $stmt_upd->close();
                header("Location: profile.php?upload=success");
                exit();
            }
        }
    } else {
        header("Location: profile.php?upload=error_ext");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
        <link rel="icon" type="image/svg+xml" href="assets/logo2.svg" sizes="any">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - SHINSEI MAP</title>
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --dark-bg: #0f1113;
            --card-bg: #1c1f22;
            --blue: #3498db;
            --yellow: #f1c40f;
            --text-gray: #a0a0a0;
            --white: #ffffff;
            --red: #e74c3c;
        }

        * {
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            margin: 0;
            padding: 20px;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--dark-bg);
            color: var(--white);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .profile-card {
            background: var(--card-bg);
            border-radius: 24px;
            padding: 40px 30px;
            text-align: center;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            border: 1px solid rgba(255,255,255,0.05);
            position: relative;
        }

        .profile-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 6px;
            background: linear-gradient(90deg, var(--blue), var(--yellow));
            border-radius: 24px 24px 0 0;
        }

        .avatar-wrapper {
            position: relative;
            width: 130px;
            height: 130px;
            margin: 0 auto 25px;
        }

        .avatar-frame {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            overflow: hidden;
            border: 4px solid var(--blue);
            box-shadow: 0 8px 15px rgba(0,0,0,0.3);
            display: flex;
            justify-content: center;
            align-items: center;
            background: rgba(52, 152, 219, 0.1);
        }

        .avatar-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Dropdown CSS */
        .dropdown-photo {
            position: absolute;
            bottom: 5px;
            right: 5px;
            z-index: 10;
        }

        .btn-ellipsis {
            background: var(--yellow);
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            border: 3px solid var(--card-bg);
            transition: 0.2s ease;
        }

        .btn-ellipsis:hover { transform: scale(1.1); }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: 45px;
            right: 0;
            background: #25282c;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            width: 160px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            overflow: hidden;
            animation: fadeIn 0.2s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .dropdown-item {
            padding: 12px 15px;
            font-size: 14px;
            color: var(--white);
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: background 0.2s;
            text-align: left;
            width: 100%;
            background: none;
            border: none;
            font-family: inherit;
        }

        .dropdown-item:hover { background: rgba(255,255,255,0.05); }
        .dropdown-item.text-red { color: #ff6b6b; }
        .dropdown-item.text-red:hover { background: rgba(231, 76, 60, 0.1); }

        .show { display: block; }

        /* General Info CSS */
        .greeting-text {
            font-size: 14px;
            color: var(--text-gray);
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        h2 { margin: 0 0 5px; font-size: 26px; font-weight: 700; text-transform: capitalize; }
        
        .user-status {
            color: var(--yellow);
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 30px;
            display: inline-block;
            letter-spacing: 1.5px;
            padding: 4px 12px;
            background: rgba(241, 196, 15, 0.1);
            border-radius: 20px;
        }

        .info-group {
            text-align: left;
            background: rgba(0,0,0,0.2);
            padding: 20px;
            border-radius: 16px;
            margin-bottom: 30px;
        }

        .info-item { margin-bottom: 15px; }
        .info-item:last-child { margin-bottom: 0; }
        .info-label { display: block; font-size: 11px; color: var(--text-gray); text-transform: uppercase; margin-bottom: 4px; font-weight: 600; }
        .info-value { font-size: 16px; color: var(--white); font-weight: 500; word-break: break-all; }

        .action-buttons { display: flex; flex-direction: column; gap: 12px; }
        .btn {
            text-decoration: none;
            padding: 14px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
        }

        .btn-back { background: var(--blue); color: var(--white); }
        .btn-back:hover { background: #2980b9; transform: translateY(-2px); }
        .btn-logout { background: transparent; color: var(--red); border: 1.5px solid rgba(231, 76, 60, 0.3); }

        #file-input { display: none; }
    </style>
</head>
<body>

<div class="profile-card">
    <form action="" method="POST" enctype="multipart/form-data" id="formFoto">
        <div class="avatar-wrapper">
            <div class="avatar-frame">
                <?php if($fotoUser && file_exists('uploads/'.$fotoUser)): ?>
                    <img src="uploads/<?= $fotoUser ?>" class="avatar-img" alt="Profile">
                <?php else: ?>
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($namaUser) ?>&background=3498db&color=fff&size=128" class="avatar-img" alt="Avatar">
                <?php endif; ?>
            </div>
            
            <!-- Tombol Tiga Titik & Dropdown -->
            <div class="dropdown-photo">
                <div class="btn-ellipsis" onclick="toggleDropdown(event)">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#121416" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="1"></circle>
                        <circle cx="19" cy="12" r="1"></circle>
                        <circle cx="5" cy="12" r="1"></circle>
                    </svg>
                </div>
                
                <div id="photoMenu" class="dropdown-menu">
                    <?php if($fotoUser && file_exists('uploads/'.$fotoUser)): ?>
                        <button type="button" class="dropdown-item" onclick="document.getElementById('file-input').click()">
                            📷 Ubah Foto
                        </button>
                        <button type="button" class="dropdown-item text-red" onclick="deletePhoto()">
                            🗑️ Hapus Foto
                        </button>
                    <?php else: ?>
                        <button type="button" class="dropdown-item" onclick="document.getElementById('file-input').click()">
                            ➕ Tambah Foto
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <input type="file" name="profile_pix" id="file-input" accept=".jpg, .jpeg, .png" onchange="handleFileSelect(this)">
        </div>
    </form>

    <div class="greeting-text">
        <span><?= $ikonWaktu ?></span> <?= $ucapan ?>,
    </div>

    <h2><?= htmlspecialchars($namaUser) ?></h2>
    <span class="user-status">Terverifikasi (Face ID)</span>

    <div class="info-group">
        <div class="info-item">
            <span class="info-label">Nama Lengkap</span>
            <span class="info-value"><?= htmlspecialchars($namaUser) ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">ID Sistem</span>
            <span class="info-value">#<?= htmlspecialchars($id_terdeteksi) ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Status Akses</span>
            <span class="info-value">Full Access Management</span>
        </div>
    </div>

    <div class="action-buttons">
        <a href="index.php" class="btn btn-back">Dashboard SDI</a>
        <a href="logout.php" class="btn btn-logout">Keluar Sesi</a>
    </div>
</div>

<script>
    function toggleDropdown(event) {
        event.stopPropagation();
        document.getElementById("photoMenu").classList.toggle("show");
    }

    window.onclick = function(event) {
        if (!event.target.matches('.btn-ellipsis') && !event.target.closest('.btn-ellipsis')) {
            var dropdowns = document.getElementsByClassName("dropdown-menu");
            for (var i = 0; i < dropdowns.length; i++) {
                var openDropdown = dropdowns[i];
                if (openDropdown.classList.contains('show')) {
                    openDropdown.classList.remove('show');
                }
            }
        }
    }

    // Handle File Upload
    function handleFileSelect(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const validImageTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            
            if (!validImageTypes.includes(file.type)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Format Salah',
                    text: 'Hanya diperbolehkan format .jpg atau .png!',
                    background: '#1c1f22',
                    color: '#fff'
                });
                input.value = ''; 
            } else {
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Sedang mengunggah foto profil Anda',
                    allowOutsideClick: false,
                    background: '#1c1f22',
                    color: '#fff',
                    didOpen: () => {
                        Swal.showLoading();
                        document.getElementById('formFoto').submit();
                    }
                });
            }
        }
    }

    // Handle Delete
    function deletePhoto() {
        Swal.fire({
            title: 'Hapus Foto Profil?',
            text: "Foto akan dihapus permanen dan kembali ke avatar default.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            cancelButtonColor: '#3498db',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            background: '#1c1f22',
            color: '#fff',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'hapus_foto.php';
            }
        })
    }

    // URL Alerts (Upload/Delete Success)
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('upload') && urlParams.get('upload') === 'success') {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Foto profil telah diperbarui.',
            background: '#1c1f22',
            color: '#fff',
            timer: 2000,
            showConfirmButton: false
        });
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    if (urlParams.has('delete') && urlParams.get('delete') === 'success') {
        Swal.fire({
            icon: 'success',
            title: 'Terhapus!',
            text: 'Foto telah dihapus, kembali ke default.',
            background: '#1c1f22',
            color: '#fff',
            timer: 2000,
            showConfirmButton: false
        });
        window.history.replaceState({}, document.title, window.location.pathname);
    }
    function periksaSesiEditor() {
    fetch('cek_sesi.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'conflict' || data.status === 'invalid') {
                
                clearInterval(intervalCekSesiEditor);

                let pesanBatal = data.status === 'conflict' 
                    ? 'Akun Anda baru saja digunakan untuk login di perangkat atau browser lain.' 
                    : 'Sesi Anda telah berakhir. Silahkan login kembali.';

                Swal.fire({
                    icon: 'error',
                    title: 'Sesi Kerja Berakhir!',
                    text: pesanBatal,
                    background: '#1a1c1e',
                    color: '#ffffff',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    confirmButtonText: 'Kembali',
                    confirmButtonColor: '#ff4757'
                }).then(() => {
                    window.location.href = 'verifikasi.php'; // Sesuaikan nama file login Anda
                });
            }
        })
        .catch(error => console.error('Gagal memvalidasi status sesi:', error));
}

const intervalCekSesiEditor = setInterval(periksaSesiEditor, 5000);

</script>

</body>
</html>