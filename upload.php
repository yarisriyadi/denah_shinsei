<?php 
include 'koneksi.php'; 
$selected_floor = isset($_GET['floor']) ? $_GET['floor'] : 'denah_lantai_1';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Upload Management | Shinsei Map</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #3498db;
            --primary-dark: #2980b9;
            --bg: #f0f4f8;
            --text-main: #2d3436;
            --text-sub: #636e72;
            --white: #ffffff;
            --error: #e74c3c;
        }
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: linear-gradient(135deg, #e0eafc 0%, #cfdef3 100%);
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            margin: 0; 
            padding: 20px; 
            box-sizing: border-box;
            color: var(--text-main);
        }
        .card { 
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 40px; 
            border-radius: 24px; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.1); 
            width: 100%;
            max-width: 480px; 
            border: 1px solid rgba(255, 255, 255, 0.3);
            animation: fadeIn 0.6s ease-out;
            position: relative; 
        }
        @media (max-width: 480px) {
            body { padding: 10px; }
            .card { padding: 30px 20px 25px 20px; border-radius: 18px; }
            h2 { font-size: 20px; }
            button { padding: 14px; font-size: 15px; }
            select, input[type="text"] { padding: 12px; font-size: 14px; }
        }
        .back-nav {
            display: inline-flex;
            align-items: center;
            text-decoration: none;
            color: var(--text-sub);
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
            transition: 0.3s;
        }
        .back-nav:hover {
            color: var(--primary);
            transform: translateX(-3px);
        }
        h2 { 
            text-align: center; 
            margin-top: 0; 
            margin-bottom: 8px; 
            font-size: 24px; 
            font-weight: 700; 
            color: var(--text-main); 
        }
        .subtitle { 
            text-align: center; 
            color: var(--text-sub); 
            font-size: 14px; 
            margin-bottom: 30px; 
        }

        .form-group { 
            margin-bottom: 18px; 
        }
        label { 
            display: block; 
            font-size: 11px; 
            font-weight: 700; 
            color: var(--text-main); 
            margin-bottom: 6px; 
            text-transform: uppercase; 
            letter-spacing: 1px; 
        }
        select, input[type="text"], input[type="file"] { 
            width: 100%; 
            padding: 14px; 
            background: var(--white);
            border: 2px solid #edf2f7; 
            border-radius: 12px; 
            box-sizing: border-box; 
            font-size: 15px; 
            font-family: inherit;
            transition: all 0.3s ease;
        }
        select:focus, input[type="text"]:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
        }
        input[type="file"] {
            background: #f8fafc;
            border: 2px dashed #cbd5e0;
            padding: 10px;
            cursor: pointer;
            font-size: 12px;
        }
        .hint { 
            font-size: 11px; 
            color: var(--error); 
            display: block; 
            margin-top: 6px; 
            line-height: 1.4; 
            font-style: italic; 
        }
        button { 
            width: 100%; 
            background: var(--primary); 
            color: white; 
            border: none; 
            padding: 16px; 
            border-radius: 14px; 
            cursor: pointer; 
            font-size: 16px; 
            font-weight: 700; 
            margin-top: 10px; 
            box-shadow: 0 10px 20px rgba(52, 152, 219, 0.2);
            transition: 0.3s;
        }
        button:hover { 
            background: var(--primary-dark); 
            transform: translateY(-2px); 
        }
        button:active { 
            transform: translateY(0); 
        }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

<div class="card">
    <a href="index.php" class="back-nav">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 5px;"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        Kembali ke Dashboard
    </a>

    <h2>Upload Denah Baru</h2>
    <p class="subtitle">Kelola denah dan aset Shinsei Map</p>

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
            <span class="hint">*Gunakan Sub-Layer untuk item khusus (APAR/Hydrant).</span>
        </div>

        <div class="form-group">
            <label id="label_nama">3. Identitas Layer</label>
            <input type="text" name="nama_lantai" id="input_nama" required>
        </div>

        <div class="form-group">
            <label>4. Berkas Gambar</label>
            <input type="file" name="gambar" accept="image/*" required>
        </div>
        
        <button type="submit" name="submit">SIMPAN</button>
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
        inputNama.placeholder = "Contoh: Master (Lantai 1)";
    } else {
        labelNama.innerText = "3. Label Aset / Sub-Layer";
        inputNama.placeholder = "Contoh: Titik APAR";
    }
}
window.onload = updatePlaceholder;
</script>
</body>
</html>