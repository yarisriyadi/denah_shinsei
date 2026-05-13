<?php 
include 'koneksi.php'; 
$selected_floor = isset($_GET['floor']) ? $_GET['floor'] : 'denah_lantai_1';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    
        <link rel="icon" type="image/svg+xml" href="assets/logo2.svg" sizes="any">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Upload Management | Shinsei Map</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --accent: #3b82f6;
            --bg-gradient: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            --glass: rgba(255, 255, 255, 0.85);
            --text-main: #1e293b;
            --text-sub: #64748b;
            --border: #e2e8f0;
            --shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: var(--bg-gradient);
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            margin: 0; 
            color: var(--text-main);
            overflow-x: hidden;
        }

        .card { 
            background: var(--glass);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            padding: 40px; 
            border-radius: 28px; 
            box-shadow: var(--shadow); 
            width: 100%;
            max-width: 460px; 
            border: 1px solid rgba(255, 255, 255, 0.6);
            animation: slideUp 0.7s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .back-nav {
            display: inline-flex;
            align-items: center;
            text-decoration: none;
            color: var(--text-sub);
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 24px;
            padding: 8px 12px;
            border-radius: 10px;
            transition: all 0.3s ease;
            background: rgba(255,255,255,0.5);
        }

        .back-nav:hover {
            color: var(--primary);
            background: var(--white);
            transform: translateX(-5px);
        }

        .header-section {
            text-align: center;
            margin-bottom: 32px;
        }

        h2 { 
            margin: 0;
            font-size: 26px; 
            font-weight: 800; 
            color: #0f172a;
            letter-spacing: -0.5px;
        }

        .subtitle { 
            color: var(--text-sub); 
            font-size: 15px; 
            margin-top: 6px;
        }

        .form-group { 
            margin-bottom: 20px; 
        }

        label { 
            display: block; 
            font-size: 12px; 
            font-weight: 700; 
            color: #475569; 
            margin-bottom: 8px; 
            margin-left: 4px;
            text-transform: uppercase; 
            letter-spacing: 0.05em;
        }

        select, input[type="text"] { 
            width: 100%; 
            padding: 14px 18px; 
            background: #ffffff;
            border: 1.5px solid var(--border); 
            border-radius: 14px; 
            box-sizing: border-box; 
            font-size: 15px; 
            font-family: inherit;
            color: var(--text-main);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        select:focus, input[type="text"]:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
            background: #fff;
        }

        .file-input-wrapper {
            position: relative;
            background: #f8fafc;
            border: 2px dashed #cbd5e0;
            border-radius: 14px;
            padding: 20px;
            text-align: center;
            transition: 0.3s;
        }

        .file-input-wrapper:hover {
            border-color: var(--primary);
            background: #f1f5f9;
        }

        input[type="file"] {
            font-size: 13px;
            color: var(--text-sub);
            width: 100%;
        }

        .hint { 
            font-size: 11.5px; 
            color: #64748b; 
            display: block; 
            margin-top: 8px; 
            padding-left: 4px;
            line-height: 1.5;
        }

        .hint strong { color: var(--primary); }

        button { 
            width: 100%; 
            background: var(--primary); 
            color: white; 
            border: none; 
            padding: 16px; 
            border-radius: 16px; 
            cursor: pointer; 
            font-size: 16px; 
            font-weight: 700; 
            margin-top: 12px; 
            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3);
            transition: all 0.3s ease;
        }

        button:hover { 
            background: var(--primary-hover); 
            transform: translateY(-2px);
            box-shadow: 0 12px 20px -3px rgba(37, 99, 235, 0.4);
        }

        button:active { transform: translateY(0); }

        /* Responsive Mobile */
        @media (max-width: 480px) {
            .card { padding: 30px 20px; border-radius: 24px; }
            h2 { font-size: 22px; }
            select, input[type="text"], button { padding: 14px; }
        }
    </style>
</head>
<body>

<div class="card">
    <a href="index.php" class="back-nav">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        Dashboard
    </a>

    <div class="header-section">
        <h2>Upload Denah</h2>
        <p class="subtitle">Kelola aset digital <strong>Shinsei Map</strong></p>
    </div>

    <form action="proses_upload.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>1. Lokasi Lantai</label>
            <select name="target_table" id="target_table" onchange="updateMasterList()" required>
                <option value="denah_lantai_1" <?= ($selected_floor == 'denah_lantai_1') ? 'selected' : ''; ?>>Lantai Dasar (L1)</option>
                <option value="denah_lantai_2" <?= ($selected_floor == 'denah_lantai_2') ? 'selected' : ''; ?>>Lantai Atas (L2)</option>
            </select>
        </div>

        <div class="form-group">
            <label>2. Kategori Objek</label>
            <select name="parent_id" id="parent_id" onchange="updatePlaceholder()">
                <option value="0">✦ Denah Utama (Master)</option>
                <?php
                $query_master = "SELECT id, nama_lantai FROM $selected_floor WHERE parent_id = 0";
                $masters = mysqli_query($conn, $query_master);
                while($m = mysqli_fetch_assoc($masters)) {
                    echo "<option value='{$m['id']}'>↳ Sub-Layer: {$m['nama_lantai']}</option>";
                }
                ?>
            </select>
            <span class="hint">Gunakan <strong>Sub-Layer</strong> untuk aset seperti APAR atau Hydrant.</span>
        </div>

        <div class="form-group">
            <label id="label_nama">3. Identitas Layer</label>
            <input type="text" name="nama_lantai" id="input_nama" required>
        </div>

        <div class="form-group">
            <label>4. Berkas Gambar</label>
            <div class="file-input-wrapper">
                <input type="file" name="gambar" accept="image/*" required>
            </div>
        </div>
        
        <button type="submit" name="submit">SIMPAN DATA</button>
    </form>
</div>

<script>
function updateMasterList() {
    var table = document.getElementById('target_table').value;
    window.location.href = "upload.php?floor=" + table;
}

function updatePlaceholder() {
    var parentId = document.getElementById('parent_id').value;
    var inputNama = document.getElementById('input_nama');
    var labelNama = document.getElementById('label_nama');
    if (parentId == "0") {
        labelNama.innerText = "3. Nama Denah Utama (Master)";
        inputNama.placeholder = "Misal: Denah Area Produksi";
    } else {
        labelNama.innerText = "3. Label Aset / Sub-Layer";
        inputNama.placeholder = "Misal: Lokasi APAR";
    }
}
window.onload = updatePlaceholder;

let inactivityTime = function () {
    let time;
    
    const timeoutDuration = 1 * 60 * 1000; 

    function logout() {
        window.location.href = 'logout.php'; 
    }

    function resetTimer() {
        clearTimeout(time);
        time = setTimeout(logout, timeoutDuration);
    }

    window.onload = resetTimer;
    document.onmousemove = resetTimer;
    document.onkeypress = resetTimer;
    document.onclick = resetTimer;
    document.ontouchstart = resetTimer; 
};

inactivityTime();
</script>
</body>
</html>