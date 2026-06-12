<?php
session_start();

if (!isset($_SESSION['terverifikasi']) || $_SESSION['terverifikasi'] !== true) {
    header("Location: verifikasi.php");
    exit();
}
include 'koneksi.php';

$id_terdeteksi = $_SESSION['user_id'] ?? $_POST['id'] ?? null;

date_default_timezone_set('Asia/Jakarta');
$jam = date('G');

if ($jam >= 5 && $jam < 11) {
    $sapaan = "Selamat Pagi";
} elseif ($jam >= 11 && $jam < 15) {
    $sapaan = "Selamat Siang";
} elseif ($jam >= 15 && $jam < 18) {
    $sapaan = "Selamat Sore";
} else {
    $sapaan = "Selamat Malam";
}

$namaUser = "Guest"; 

if ($id_terdeteksi) {
    $query = "SELECT nama FROM data_wajah WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $id_terdeteksi);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $namaUser = $row['nama'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" type="image/svg+xml" href="assets/logo2.svg" sizes="any">
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Sistem Denah Shinsei Denshi</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

      <style>
        :root { 
            --sidebar-bg: #121416; 
            --blue: #3498db; 
            --blue-dark: #2980b9;
            --item-bg: rgba(255, 255, 255, 0.03); 
            --item-hover: rgba(255, 255, 255, 0.08);
            --text-main: #ffffff;
            --text-dim: #a0a0a0;
            --red: #ff4757; 
        }
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            margin: 0; display: flex; 
            height: 100vh; 
            overflow: hidden; background: #0f1113; 
        }
        #sidebar { 
            width: 320px; 
            background: var(--sidebar-bg); 
            color: white; 
            height: 100vh; 
            display: flex;
            flex-direction: column;
            z-index: 2000; 
            border-right: 1px solid rgba(255,255,255,0.05);
            transition: transform 0.3s ease; 
        }
        .sidebar-header { 
            padding: 25px 15px; 
            border-bottom: 1px solid rgba(255,255,255,0.05);
            display: flex;
            align-items: center;
            justify-content: space-between; 
            gap: 10px;
        }
        .brand { 
            font-size: 18px; 
            font-weight: 800; 
            margin: 0;
            white-space: nowrap;
            display: flex;
            align-items: center;
            padding: 5px 0; 
        }
        .brand-logo {
            height: 75px; 
            width: auto;  
            object-fit: contain;
            display: block;
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .brand-logo:hover {
            transform: scale(1.05); 
            filter: brightness(1.2);
        }
        .brand-logo:active {
            transform: scale(0.95); 
        }

        .brand span { 
            color: var(--blue); 
        }
        .sidebar-content { 
            padding: 0 15px 10px 15px; 
            flex-grow: 1; 
            overflow-y: auto; 
            box-sizing: border-box; 
        }
        .denah-group { 
            margin-bottom: 5px; 
            border-radius: 14px; 
            overflow: hidden; 
            transition: 0.3s; 
        }
        .sub-item { 
            font-size: 13px;
            padding: 10px 15px;
            cursor: pointer; 
            background: var(--item-bg);
            border: 1px solid rgba(255,255,255,0.05); 
            border-radius: 12px;
            display: flex; 
            align-items: center; 
            justify-content: space-between;
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .sub-item:hover { 
            background: var(--item-hover); 
            border-color: rgba(52, 152, 219, 0.3); 
        }
        .sub-item.active { 
            background: linear-gradient(135deg, var(--blue) 0%, var(--blue-dark) 100%); 
            box-shadow: 0 8px 15px rgba(52, 152, 219, 0.2); 
            border-color: transparent; 
            color: white !important; 
        }
        .floor-badge { 
            font-size: 10px; 
            background: rgba(255,255,255,0.05); 
            color: var(--blue); 
            padding: 3px 8px; 
            border-radius: 6px; 
            margin-right: 12px; 
            font-weight: 700; 
            border: 1px solid rgba(52, 152, 219, 0.3); 
        }
        .active .floor-badge { 
            background: white; 
            color: var(--blue); 
            border: none; 
        }
        .arrow { 
            border: solid #666; 
            border-width: 0 2px 2px 0; 
            display: inline-block; 
            padding: 3px; 
            transform: rotate(45deg); 
            transition: 0.3s; 
        }
        .open .arrow { 
            transform: rotate(-135deg); 
            border-color: white; 
        }
        .child-container { 
            display: none; 
            padding: 5px 0 10px 0; 
            background: rgba(0,0,0,0.2); 
        }
        .sub-child { 
            font-size: 11px; 
            padding: 8px 18px 8px 50px; 
            color: var(--text-dim); 
            cursor: pointer; 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            position: relative; 
            transition: 0.2s; 
        }
        .sub-child::before { 
            content: ""; 
            position: absolute; 
            left: 30px; 
            top: 0; 
            bottom: 0; 
            width: 1.5px; 
            background: rgba(255,255,255,0.1); 
        }
        .sub-child:hover { 
            color: white; 
            background: rgba(255,255,255,0.05); 
        }
        .sub-child.active { 
            color: var(--blue); 
            font-weight: 600; 
        }
        .btn-delete, .btn-edit { 
            width: 24px; 
            height: 24px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            border-radius: 6px; 
            text-decoration: none; 
            opacity: 0; 
            transition: 0.2s; 
        }
        .btn-delete { color: var(--red); font-size: 18px; }
        .btn-edit { color: var(--blue); font-size: 14px; }

        .sub-item:hover .btn-delete, .sub-item:hover .btn-edit,
        .sub-child:hover .btn-delete, .sub-child:hover .btn-edit { 
            opacity: 0.5; 
        }
        .btn-delete:hover { 
            opacity: 1 !important; 
            background: rgba(255, 71, 87, 0.1); 
            transform: scale(1.1); 
        }
        .btn-edit:hover {
            opacity: 1 !important;
            background: rgba(52, 152, 219, 0.1);
            transform: scale(1.1);
        }
        .sidebar-footer { 
            padding: 20px; 
            border-top: 1px solid rgba(255,255,255,0.05); 
            background: var(--sidebar-bg); 
        }
        .btn-upload { 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            gap: 10px; 
            width: 100%; 
            padding: 14px 0; 
            background: var(--blue); 
            color: white; 
            text-align: center; 
            text-decoration: none; 
            border-radius: 12px; 
            font-size: 14px; 
            font-weight: 700; 
            transition: 0.3s; 
            margin-bottom: 15px; 
        }
        .copyright { 
            font-size: 10px; 
            color: #4a4d50; 
            text-align: center; 
            text-transform: uppercase; 
            letter-spacing: 1px; 
            font-weight: 600; 
        }
        #content { 
            flex: 1; 
            position: relative; 
            display: flex;
            flex-direction: column;
            height: 100vh;
            background: #1a1c1e;
            width: 100%; 
        }
        #map { 
            flex-grow: 1; 
            width: 100%; 
            height: 100%; 
            outline: none; 
            z-index: 1; 
        }
        .leaflet-image-layer {
            image-rendering: -webkit-optimize-contrast;
            image-rendering: crisp-edges;
        }
        .map-label {
            position: absolute; 
            top: 15px; 
            left: 65px; 
            z-index: 1000;
            background: rgba(255, 255, 255, 0.95); 
            padding: 8px 20px;
            border-radius: 10px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            font-weight: 700; 
            font-size: 13px; 
            color: #1a1a1a; 
            pointer-events: none;
            text-transform: uppercase; 
            border-left: 4px solid var(--blue);
        }
        .btn-print {
            position: absolute; 
            top: 15px; 
            right: 20px; 
            z-index: 1000;
            background: white; 
            border: none; 
            padding: 10px 20px;
            border-radius: 10px; 
            font-weight: 700; 
            cursor: pointer;
            display: flex; 
            align-items: center; 
            gap: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15); 
            transition: 0.3s;
            font-family: inherit;
        }
        #mobile-menu-btn {
            display: none; 
            position: fixed; 
            bottom: 20px; 
            left: 20px; 
            z-index: 2500;
            background: var(--blue); 
            border: none;
            width: 50px; 
            height: 50px; 
            border-radius: 50%;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            cursor: pointer;
            padding: 0; 
            outline: none;
            align-items: center;
            justify-content: center;
            transition: background 0.3s ease; 
        }

        .hamburger-icon {
            display: inline-block;
            font-size: 24px;
            line-height: 1;
            color: white; 
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1); 
        }

        #mobile-menu-btn.rotated {
            background: #3498db; 
            border: none;
        }

        #mobile-menu-btn.rotated .hamburger-icon {
            transform: rotate(90deg); 
            color: white; 
        }

        .brand-logo {
            height: 75px; 
            width: auto;  
            object-fit: contain;
            display: block;
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            content: url('assets/icon.webp'); 
        }

        #welcome-screen {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at center, #1e293b 0%, #0f172a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 20px;
            box-sizing: border-box;
        }

        .welcome-card {
            text-align: center;
            padding: 50px 30px;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.8);
            max-width: 420px;
            width: 100%;
            box-sizing: border-box;
        }

        .welcome-icon {
            font-size: 60px;
            margin-bottom: 15px;
            display: inline-block;
            filter: drop-shadow(0 10px 15px rgba(0,0,0,0.3));
        }

        .welcome-card h2 {
            color: white;
            font-size: clamp(24px, 4vw, 32px);
            margin: 0 0 12px 0;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .welcome-card p {
            color: #94a3b8;
            font-size: clamp(14px, 2vw, 16px);
            margin: 0 0 35px 0;
            line-height: 1.8;
        }

        .version-badge {
            display: inline-block;
            padding: 6px 16px;
            background: rgba(52, 152, 219, 0.1);
            color: #3498db;
            border: 1px solid rgba(52, 152, 219, 0.3);
            border-radius: 100px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .map-loader {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1500; 
            display: none;
            flex-direction: column; 
            align-items: center;
            justify-content: center;
            background: none; 
            padding: 0;
            border-radius: 0;
            backdrop-filter: none;
            -webkit-backdrop-filter: none;
        }

        .spinner {
            width: 45px;
            height: 45px;
            border: 4px solid rgba(52, 152, 219, 0.1);
            border-top: 4px solid var(--blue);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        #load-percentage {
            color: var(--blue);
            font-weight: 800;
            margin-top: 15px;
            font-size: 16px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            letter-spacing: 1px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.5); /* Shadow agar teks tetap terbaca jelas di atas denah gambar apa pun */
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        #welcome-screen.fade-out {
            opacity: 0;
            transform: scale(1.05);
            pointer-events: none;
        }

        @media (max-width: 768px) {
            #sidebar {
                position: fixed;
                left: 0;
                transform: translateX(-100%);
                height: 100%;
                box-shadow: 10px 0 30px rgba(0,0,0,0.5);
            }
            #sidebar.active { transform: translateX(0); }
            #mobile-menu-btn { display: flex; }
            .map-label { 
                left: 15px; top: 15px; font-size: 11px; padding: 6px 12px;
                max-width: 160px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            }
            .btn-print { 
                padding: 8px 12px; font-size: 11px; top: 10px; right: 10px;
                max-width: 120px;
            }
            .brand { font-size: 16px; }
            .sidebar-header { padding: 20px 12px; }
            .user-name-label { max-width: 60px; }
            .brand-logo { height: 75px; }
            .welcome-card { padding: 40px 20px; }
            .welcome-icon { font-size: 50px; }
            #load-percentage { font-size: 14px; }
            .spinner { width: 35px; height: 35px; }
        }

        @media (max-width: 480px) {
            .welcome-card { padding: 40px 25px; border-radius: 30px; }
        }

        @media print {
            #sidebar, .btn-print, #mobile-menu-btn { display: none !important; }
        }

        body.swal2-shown { height: 100vh !important; overflow: hidden !important; }
        .swal2-container { z-index: 9999 !important; }
        body.swal2-shown { overflow: hidden !important; padding-right: 0 !important; }
        
        .btn-edit-canvas {
            font-size: 14px; opacity: 0.6; transition: 0.3s; padding: 2px 5px; border-radius: 4px;
        }
        .sub-child:hover .btn-edit-canvas, .sub-item:hover .btn-edit-canvas { opacity: 1; }
        .btn-edit-canvas:hover { background: rgba(46, 204, 113, 0.2); transform: scale(1.2); }
        
        .user-dropdown { position: relative; margin-top: 0; }
        .user-trigger {
            display: flex; align-items: center; gap: 6px; cursor: pointer; padding: 5px 8px;
            border-radius: 8px; background: rgba(255,255,255,0.03); transition: 0.3s;
            border: 1px solid rgba(255,255,255,0.05);
        }
        .user-trigger:hover { background: rgba(52, 152, 219, 0.1); border-color: var(--blue); }
        .user-avatar-wrapper { width: 24px; height: 24px; flex-shrink: 0; }
        .user-name-label {
            font-size: 13px; font-weight: 600; color: white; max-width: 80px; 
            overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }
        .user-menu-content {
            display: none; position: absolute; top: 110%; right: 0; width: 160px;
            background: #1c1f22; border: 1px solid rgba(255,255,255,0.1); border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5); z-index: 3000;
        }
        .user-menu-content.show { display: block; animation: slideIn 0.2s ease-out; }
        .menu-greeting { padding: 12px 15px; font-size: 12px; color: var(--text-dim); }
        .menu-divider { height: 1px; background: rgba(255,255,255,0.05); }
        .user-menu-content a { display: flex; align-items: center; gap: 10px; padding: 10px 15px; color: white; text-decoration: none; font-size: 13px; transition: 0.2s; }
        .user-menu-content a:hover { background: var(--blue); }
        .logout-link:hover { background: var(--red) !important; }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .btn-print.hidden { display: none; }
          .welcome-divider {
    width: 60%;              
    height: 2px;              
    background: linear-gradient(90deg, transparent, #3498db, transparent); 
    margin: 20px auto;        
    opacity: 0.7;
}
          
.mobile-inline-hamburger {
    display: none;             
    font-size: 14px;           
    color: #ffffff;           
    background-color: #3498db; 
    padding: 2px 6px;          
    border-radius: 4px;        
    margin-left: 6px;          
    vertical-align: middle;    
    line-height: 1;            
}

@media (max-width: 768px) {
    .mobile-inline-hamburger {
        display: inline-block;
    }
}
      </style>
</head>
<body>

<button id="mobile-menu-btn" onclick="toggleSidebar()" aria-label="Buka Menu Navigasi">
    <span class="hamburger-icon">☰</span>
</button>
    <div id="sidebar">
    <div class="sidebar-header">
        <div class="brand">
    <a href="index.php" style="display: block;">
        <img src="assets/icon.webp" alt="Shinsei Map Logo" class="brand-logo">
    </a>
</div>

        <div class="user-dropdown">
            <div class="user-trigger" onclick="toggleUserMenu()">
                <div class="user-avatar-wrapper">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="11" stroke="#3498db" stroke-width="2"/>
                        <circle cx="12" cy="8" r="4" fill="#3498db"/>
                        <path d="M5 19C5 15.134 8.134 12 12 12C15.866 12 19 15.134 19 19" stroke="#3498db" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
                <span class="user-name-label"><?= htmlspecialchars($namaUser) ?></span>
                <span class="chevron-icon" style="font-size: 10px;">▾</span>
            </div>

            <div id="userMenu" class="user-menu-content">
                <div class="menu-greeting">
                    <span id="greeting-text">Halo,</span>
                    <strong><?= htmlspecialchars($namaUser) ?></strong>
                </div>
                <div class="menu-divider"></div>
                <a href="profile.php">👤 Profile</a>
                <a href="logout.php" class="logout-link">⬅️ Keluar</a>
            </div>
        </div>
    </div>

    <div class="sidebar-content">
        <div id="list-menu">
            <?php
            $tables = ['L1' => 'denah_lantai_1', 'L2' => 'denah_lantai_2'];
            foreach ($tables as $kode => $tableName) {
                $masterQuery = mysqli_query($conn, "SELECT * FROM $tableName WHERE parent_id = 0 ORDER BY id ASC");
                while ($master = mysqli_fetch_assoc($masterQuery)) {
                    $mID = $kode . '_' . $master['id']; 
                    ?>
                    
                    <div class="denah-group" id="group-<?= $mID ?>">
                        <div class="sub-item" onclick="toggleSub('<?= $mID ?>'); handleMapLoad('<?= $master['file_gambar'] ?>', <?= $master['lebar_px'] ?>, <?= $master['tinggi_px'] ?>, '<?= $kode ?> - <?= addslashes($master['nama_lantai']) ?>', this)">
                            <div style="display:flex; align-items:center;">
                                <span class="floor-badge"><?= $kode ?></span>
                                <strong><?= htmlspecialchars($master['nama_lantai']) ?></strong>
                            </div>
                            <div style="display:flex; align-items:center; gap: 8px;">
                                <a href="javascript:void(0)" class="btn-edit" onclick="renameItem(event, '<?= $master['id'] ?>', '<?= $tableName ?>', '<?= htmlspecialchars($master['nama_lantai']) ?>')">✎</a>
                                <a href="javascript:void(0)" class="btn-delete" onclick="deleteItem(event, '<?= $master['id'] ?>', '<?= $tableName ?>', '<?= htmlspecialchars($master['nama_lantai']) ?>')">×</a>
                                <a href="edit_denah.php?id=<?= $master['id'] ?>&table=<?= $tableName ?>" class="btn-edit" style="color: #2ecc71; opacity: 1;" title="Edit Layout">🎨</a>

                            </div>
                        </div>
                        <div class="child-container" id="child-<?= $mID ?>">
    <?php
    $subQuery = mysqli_query($conn, "SELECT * FROM $tableName WHERE parent_id = {$master['id']} ORDER BY keterangan ASC");
    while ($sub = mysqli_fetch_assoc($subQuery)) {
        ?>
        <div class="sub-child" onclick="handleMapLoad('<?= $sub['file_gambar'] ?>', <?= $sub['lebar_px'] ?>, <?= $sub['tinggi_px'] ?>, '<?= $kode ?> - <?= addslashes($sub['keterangan']) ?>', this)">
            <span><?= htmlspecialchars($sub['keterangan']) ?></span>
            
            <div style="display:flex; align-items:center; gap: 8px;">
                <a href="javascript:void(0)" class="btn-edit" onclick="renameItem(event, '<?= $sub['id'] ?>', '<?= $tableName ?>', '<?= htmlspecialchars($sub['keterangan']) ?>')">✎</a>
                
                <a href="javascript:void(0)" class="btn-delete" onclick="deleteItem(event, '<?= $sub['id'] ?>', '<?= $tableName ?>', '<?= htmlspecialchars($sub['keterangan']) ?>')">×</a>
                
                <a href="edit_denah.php?id=<?= $sub['id'] ?>&table=<?= $tableName ?>" 
                   class="btn-edit-canvas" 
                   style="color: #2ecc71; text-decoration: none;" 
                   onclick="event.stopPropagation();" 
                   title="Edit Konten Gambar Sub-Denah">🎨</a>
            </div>
        </div>
        <?php
    }
    ?>
</div>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
    </div>
    
    <div class="sidebar-footer">
        <a href="upload.php" class="btn-upload">Upload Denah Baru</a>
        <div class="copyright">&copy; <?= date('Y'); ?> PT. Shinsei Denshi Indonesia</div>
    </div>
</div>

<div id="content">
    <div class="map-label" id="current-label" style="display: none;">Pilih Denah</div>
    
    <div id="welcome-screen">
        <div class="welcome-card">
            <div class="welcome-icon">🗺️</div>
            
            <h2 id="welcome-greeting"><?= $sapaan ?>, <?= htmlspecialchars($namaUser) ?>!</h2>
            
            <p>Silakan Pilih Maps<span class="mobile-inline-hamburger">☰</span></p>
            
            <div class="welcome-divider"></div>
            
            <div class="version-badge">
                Version 1.0
            </div>
        </div>
    </div>


    <div id="map-loader" class="map-loader" style="display: none;">
        <div class="spinner"></div>
        <div id="load-percentage">0%</div>
    </div>

    <button class="btn-print" id="btnExport" onclick="printUltraHighResDenah()" style="display: none;">
        <span>Export PDF</span>
    </button>
    <div id="map"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/@geoman-io/leaflet-geoman-free@latest/dist/leaflet-geoman.css" />
<script src="https://unpkg.com/@geoman-io/leaflet-geoman-free@latest/dist/leaflet-geoman.min.js"></script>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

<script>
const Toast = Swal.mixin({
    focusConfirm: false,
});

var map = L.map('map', {
    crs: L.CRS.Simple,
    minZoom: 0,
    maxZoom: 4,
    zoomSnap: 0.1,
    attributionControl: false,
    zoomControl: true
});

var currentOverlay = null;
    
function toggleUserMenu() {
    const menu = document.getElementById('userMenu');
    if (menu) {
        menu.classList.toggle('show');
    }
}

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        sidebar.classList.toggle('active');
    }
}

function handleMapLoad(file, w, h, judul, element) {
    loadMap(file, w, h, judul, element);
    if (window.innerWidth <= 768) {
        toggleSidebar();
    }
}

function toggleSub(id) {
    var container = document.getElementById('child-' + id);
    var group = document.getElementById('group-' + id);
    
    document.querySelectorAll('.child-container').forEach(el => { 
        if (el.id !== 'child-' + id) el.style.display = 'none'; 
    });
    document.querySelectorAll('.denah-group').forEach(el => { 
        if (el.id !== 'group-' + id) el.classList.remove('open'); 
    });

    if (container) {
        if (container.style.display === "block") {
            container.style.display = "none";
            if (group) group.classList.remove('open');
        } else {
            container.style.display = "block";
            if (group) group.classList.add('open');
        }
    }
}

function loadMap(file, w, h, judul, element) {
    if (window.event && (window.event.target.classList.contains('btn-delete') || window.event.target.classList.contains('btn-edit'))) return;
    
    if (element && element.classList && typeof element.classList.contains === 'function') {
        if (element.classList.contains('active')) return;
    }

    sessionStorage.setItem('activeMapFile', file);
    sessionStorage.setItem('activeMapW', w);
    sessionStorage.setItem('activeMapH', h);
    sessionStorage.setItem('activeMapJudul', judul);
    
    const allItems = Array.from(document.querySelectorAll('.sub-item, .sub-child'));
    if (element) {
        const elementIndex = allItems.indexOf(element);
        sessionStorage.setItem('activeMapIndex', elementIndex);
    }

    const welcomeScreen = document.getElementById('welcome-screen');
    if (welcomeScreen) {
        welcomeScreen.style.display = 'none';
    }

    const currentLabel = document.getElementById('current-label');
    if (currentLabel) {
        currentLabel.innerText = judul;
        currentLabel.style.display = 'block';
    }
    
    allItems.forEach(i => i.classList.remove('active'));
    if (element && element.classList) {
        element.classList.add('active');
    }

    if (currentOverlay) map.removeLayer(currentOverlay);

    if (!file || file === 'NULL' || file.trim() === '') {
        const btnExport = document.getElementById('btnExport');
        if (btnExport) btnExport.style.display = 'none';

        Swal.fire({
            icon: 'info',
            title: 'Gambar Belum Tersedia',
            text: `Layout untuk "${judul}" belum diunggah atau file kosong.`,
            background: '#1a1c1e',
            color: '#ffffff'
        });
        return;
    }

    const btnExport = document.getElementById('btnExport');
    if (btnExport) btnExport.style.display = 'block';

    var sw = map.unproject([0, h], map.getMaxZoom());
    var ne = map.unproject([w, 0], map.getMaxZoom());
    var bounds = new L.LatLngBounds(sw, ne);

    var fileNameWebp = file.substring(0, file.lastIndexOf('.')) + '.webp';
    var imageUrl = 'uploads/' + fileNameWebp;

    const loader = document.getElementById('map-loader');
    const percentText = document.getElementById('load-percentage');
    if (loader) loader.style.display = 'flex';
    if (percentText) percentText.innerText = '0%';

    var xhr = new XMLHttpRequest();
    xhr.open('GET', imageUrl, true);
    xhr.responseType = 'blob';

    xhr.onprogress = function(e) {
        if (e.lengthComputable) {
            var percentage = Math.round((e.loaded / e.total) * 100);
            if (percentText) percentText.innerText = percentage + '%';
        }
    };

    xhr.onload = function() {
        if (xhr.status === 200) {
            if (percentText) percentText.innerText = '100%';
            var blobUrl = URL.createObjectURL(xhr.response);

            currentOverlay = L.imageOverlay(blobUrl, bounds, {
                opacity: 1, 
                alt: judul,
                interactive: true
            }).addTo(map);

            if (loader) loader.style.display = 'none';

            const imgNode = currentOverlay.getElement();
            if (imgNode) {
                imgNode.onerror = function() {
                    if (loader) loader.style.display = 'none';
                    Swal.fire({
                        icon: 'error',
                        title: 'File Gambar Hilang',
                        text: `File "${fileNameWebp}" tidak ditemukan di direktori server.`,
                        background: '#1a1c1e',
                        color: '#ffffff'
                    });
                };
            }
            map.fitBounds(bounds);
        } else {
            if (loader) loader.style.display = 'none';
            Swal.fire({
                icon: 'error',
                title: 'Gagal Memuat File',
                text: `Sistem mendeteksi HTTP Status ${xhr.status} saat mencoba mengunduh denah.`,
                background: '#1a1c1e',
                color: '#ffffff'
            });
        }
    };

    xhr.onerror = function() {
        if (loader) loader.style.display = 'none';
        Swal.fire({
            icon: 'error',
            title: 'Kesalahan Jaringan',
            text: 'Gagal menyambung ke server untuk mengunduh asset denah.',
            background: '#1a1c1e',
            color: '#ffffff'
        });
    };

    xhr.send();
}

const mobileMenuBtn = document.getElementById('mobile-menu-btn');
if (mobileMenuBtn) {
    mobileMenuBtn.addEventListener('click', function() {
        this.classList.toggle('rotated');
    });
}

function renameItem(event, id, table, oldName) {
    event.stopPropagation();
    Swal.fire({
        title: 'Ubah Nama',
        input: 'text',
        inputValue: oldName,
        inputLabel: 'Masukkan nama baru untuk denah ini',
        showCancelButton: true,
        reverseButtons: true,
        confirmButtonText: 'Simpan',
        cancelButtonText: 'Batal',
        background: '#1a1c1e',
        color: '#ffffff',
        preConfirm: (newName) => {
            if (!newName || newName.trim() === "") {
                Swal.showValidationMessage('Nama tidak boleh kosong!');
                return false;
            }
            const formData = new FormData();
            formData.append('id', id);
            formData.append('table', table);
            formData.append('new_name', newName.trim());

            return fetch('rename.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .catch(error => Swal.showValidationMessage(`Gagal: ${error}`));
        }
    }).then((result) => {
        if (result.isConfirmed && result.value && result.value.success) {
            location.reload();
        }
    });
}

function deleteItem(event, id, table, name) {
    event.stopPropagation(); 
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: `Denah "${name}" akan dihapus secara permanen!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ff4757',
        cancelButtonColor: '#3498db',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        background: '#1a1c1e',
        color: '#ffffff',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Menghapus...',
                allowOutsideClick: false,
                background: '#1a1c1e',
                color: '#ffffff',
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(`hapus.php?id=${id}&table=${table}`)
                .then(response => {
                    if (response.ok) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Terhapus!',
                            text: 'Data telah berhasil dihapus.',
                            timer: 1500,
                            showConfirmButton: false,
                            background: '#1a1c1e',
                            color: '#ffffff'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        throw new Error('Gagal menghapus data.');
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: error.message,
                        background: '#1a1c1e',
                        color: '#ffffff'
                    });
                });
        }
    });
}

async function printUltraHighResDenah() {
    if (!currentOverlay) {
        Swal.fire({ icon: 'warning', title: 'Oops...', text: 'Pilih denah dulu!', background: '#1a1c1e', color: '#fff' });
        return;
    }

    const btn = document.getElementById('btnExport');
    const originalText = btn.innerHTML;
    const judul = document.getElementById('current-label').innerText;

    Swal.fire({
        title: 'Memproses PDF...',
        didOpen: () => { Swal.showLoading(); },
        background: '#1a1c1e',
        color: '#fff'
    });

    btn.disabled = true;

    try {
        const imgElement = currentOverlay.getElement();
        const canvas = await html2canvas(imgElement, {
            useCORS: true,
            scale: 5
        });

        const imgData = canvas.toDataURL('image/png', 1.0);
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('l', 'mm', 'a4');
        
        const pageWidth = pdf.internal.pageSize.getWidth();
        const pageHeight = pdf.internal.pageSize.getHeight();
        const imgProps = pdf.getImageProperties(imgData);
        const ratio = imgProps.width / imgProps.height;

        let printWidth = pageWidth;
        let printHeight = pageWidth / ratio;

        if (printHeight > pageHeight) {
            printHeight = pageHeight;
            printWidth = pageHeight * ratio;
        }

        pdf.addImage(imgData, 'PNG', (pageWidth - printWidth) / 2, (pageHeight - printHeight) / 2, printWidth, printHeight);
        pdf.save(`Denah_${judul.replace(/[^a-z0-9]/gi, '_')}.pdf`);

        Swal.close();
    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Gagal Export', text: err.message });
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

function setGreeting() {
    const hour = new Date().getHours();
    const greetingElement = document.getElementById('welcome-greeting'); 
    let greeting = hour < 11 ? "☀️ Selamat Pagi" : hour < 15 ? "🌤️ Selamat Siang" : hour < 18 ? "⛅ Selamat Sore" : "🌙 Selamat Malam";
    
    if (greetingElement) {
        greetingElement.innerHTML = `${greeting}, <?= htmlspecialchars($namaUser) ?>!`;
    }
}

window.onload = function() {
    setGreeting();
    
    const savedFile = sessionStorage.getItem('activeMapFile');
    const savedW = sessionStorage.getItem('activeMapW');
    const savedH = sessionStorage.getItem('activeMapH');
    const savedJudul = sessionStorage.getItem('activeMapJudul');
    const savedIndex = sessionStorage.getItem('activeMapIndex');

    const hasSavedMap = savedFile && savedW && savedH && savedJudul && savedIndex !== null && savedIndex !== 'null';

    if (hasSavedMap) {
        const allItems = document.querySelectorAll('.sub-item, .sub-child');
        const targetIndex = parseInt(savedIndex, 10);
        
        const targetElement = (allItems && allItems[targetIndex]) ? allItems[targetIndex] : null;
        
        loadMap(savedFile, parseInt(savedW, 10), parseInt(savedH, 10), savedJudul, targetElement);
        
    } else {
        document.getElementById('welcome-screen')?.style.setProperty('display');
    }
};
    
window.addEventListener('click', function(e) {
    const userMenu = document.getElementById('userMenu');
    const dropdown = document.querySelector('.user-dropdown');
    if (userMenu && dropdown && !dropdown.contains(e.target)) {
        userMenu.classList.remove('show');
    }
});
    
    function periksaSesiPerangkat() {
    fetch('cek_sesi.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'conflict' || data.status === 'invalid') {
                
                clearInterval(intervalCekSesi);

                let pesan = data.status === 'conflict' 
                    ? 'Akun Anda baru saja digunakan untuk login di perangkat atau browser lain.' 
                    : 'Sesi Anda telah berakhir. Silahkan login kembali.';


                Swal.fire({
                    icon: 'error',
                    title: 'Sesi Berakhir!',
                    text: pesan,
                    background: '#1a1c1e',
                    color: '#ffffff',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#ff4757'
                }).then(() => {
                    window.location.href = 'verifikasi.php'; 
                });
            }
        })
        .catch(error => console.error('Gagal memvalidasi sesi:', error));
}

const intervalCekSesi = setInterval(periksaSesiPerangkat, 5000);
</script>
</body>
</html>