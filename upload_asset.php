<?php
session_start();
if (!isset($_SESSION['terverifikasi'])) { 
    header("Location: verifikasi.php"); 
    exit(); 
}

// Menjaga parameter context tetap ada agar tidak salah saat kembali ke editor
$back_id = $_GET['id'] ?? ($_POST['back_id'] ?? '');
$back_table = $_GET['table'] ?? ($_POST['back_table'] ?? '');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['new_asset'])) {
    $targetDir = "assets/items/";
    if (!file_exists($targetDir)) { 
        mkdir($targetDir, 0777, true); 
    }
    
    $fileName = time() . '_' . basename($_FILES["new_asset"]["name"]); // Tambah prefix waktu agar nama unik
    $targetFilePath = $targetDir . $fileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

    $allowTypes = array('jpg', 'png', 'jpeg', 'gif', 'webp');
    if (in_array($fileType, $allowTypes)) {
        if (move_uploaded_file($_FILES["new_asset"]["tmp_name"], $targetFilePath)) {
            $status = "success";
        } else { 
            $status = "error"; 
        }
    } else { 
        $status = "invalid"; 
    }
    
    // Redirect kembali dengan status dan tetap membawa context ID & Table
    header("Location: upload_asset.php?status=$status&id=$back_id&table=$back_table");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Asset Library</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --blue: #3498db;
            --dark-bg: #0f1113;
            --panel-bg: #1a1c1e;
            --border: #333;
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: var(--dark-bg); 
            color: #e0e0e0; 
            margin: 0;
            padding: 20px;
            line-height: 1.6;
        }

        .container { 
            max-width: 900px; 
            margin: 20px auto; 
            background: var(--panel-bg); 
            padding: 30px; 
            border-radius: 16px; 
            border: 1px solid var(--border);
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        .header-area {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
        }

        h2 { margin: 0; color: white; font-size: 24px; }
        h3 { color: #888; font-size: 16px; font-weight: 500; margin-top: 5px; }

        .btn { 
            padding: 10px 20px; 
            border-radius: 8px; 
            border: none; 
            cursor: pointer; 
            font-weight: 600; 
            font-size: 14px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-blue { background: var(--blue); color: white; }
        .btn-blue:hover { background: #2980b9; transform: translateY(-2px); }

        .btn-back { 
            background: #25282c; 
            color: #ccc; 
            border: 1px solid var(--border);
        }
        .btn-back:hover { background: #2c3136; color: white; }

        .upload-box { 
            border: 2px dashed #444; 
            padding: 40px 20px; 
            text-align: center; 
            border-radius: 12px; 
            margin-bottom: 40px; 
            background: rgba(255, 255, 255, 0.02);
            transition: border-color 0.3s;
        }
        .upload-box:hover { border-color: var(--blue); }

        input[type="file"] {
            background: #25282c;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid var(--border);
            color: #aaa;
            margin-bottom: 15px;
            width: 100%;
            max-width: 300px;
        }

        .grid-title {
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); 
            gap: 20px; 
            max-height: 500px; 
            overflow-y: auto; 
            padding-right: 10px;
        }

        /* Custom Scrollbar */
        .grid::-webkit-scrollbar { width: 6px; }
        .grid::-webkit-scrollbar-track { background: transparent; }
        .grid::-webkit-scrollbar-thumb { background: #333; border-radius: 10px; }

        .asset-card { 
            background: #25282c; 
            padding: 15px; 
            border-radius: 12px; 
            text-align: center; 
            border: 1px solid var(--border); 
            transition: 0.3s;
        }
        .asset-card:hover { 
            border-color: var(--blue); 
            transform: translateY(-5px);
            background: #2c3136;
        }

        .asset-card img { 
            width: 100%; 
            height: 100px; 
            object-fit: contain; 
            filter: drop-shadow(0 5px 15px rgba(0,0,0,0.3));
        }

        .asset-name {
            font-size: 11px; 
            margin-top: 12px; 
            color: #777; 
            white-space: nowrap; 
            overflow: hidden; 
            text-overflow: ellipsis;
        }

        @media (max-width: 600px) {
            .container { padding: 20px; }
            .header-area { flex-direction: column; gap: 15px; }
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header-area">
            <div>
                <h2>Asset Library</h2>
                <h3>Kelola ikon untuk denah digital</h3>
            </div>
            <a href="edit_denah.php?id=<?= $back_id ?>&table=<?= $back_table ?>" class="btn btn-back">
                ⬅ Kembali ke Editor
            </a>
        </div>
        
        <form action="" method="post" enctype="multipart/form-data" class="upload-box">
            <input type="hidden" name="back_id" value="<?= $back_id ?>">
            <input type="hidden" name="back_table" value="<?= $back_table ?>">
            
            <p style="margin-bottom: 20px; color: #aaa;">Pilih file gambar (PNG transparan sangat disarankan)</p>
            <input type="file" name="new_asset" accept="image/*" required><br>
            <button type="submit" class="btn btn-blue">🚀 Unggah ke Library</button>
        </form>

        <div class="grid-title">
            <span style="font-weight: 600; color: #fff;">File yang tersedia</span>
            <span style="font-size: 12px; color: #666;">Format: PNG, JPG, WEBP</span>
        </div>

        <div class="grid">
            <?php
            $files = glob("assets/items/*.{jpg,png,jpeg,gif,webp}", GLOB_BRACE);
            if(empty($files)) {
                echo "<div style='grid-column: 1/-1; text-align: center; padding: 40px; color: #555;'>
                        <p>Library masih kosong.</p>
                      </div>";
            } else {
                // Urutkan berdasarkan file terbaru
                array_multisort(array_map('filemtime', $files), SORT_DESC, $files);
                
                foreach($files as $file) {
                    $name = basename($file);
                    echo "<div class='asset-card'>
                            <img src='$file' alt='$name'>
                            <div class='asset-name' title='$name'>$name</div>
                          </div>";
                }
            }
            ?>
        </div>
    </div>

    <?php if(isset($_GET['status'])): ?>
    <script>
        const status = "<?= $_GET['status'] ?>";
        if(status === 'success') {
            Swal.fire({
                title: 'Berhasil!',
                text: 'Asset baru telah ditambahkan ke library.',
                icon: 'success',
                confirmButtonColor: '#3498db'
            });
        } else if(status === 'invalid') {
            Swal.fire({
                title: 'Format Salah',
                text: 'Gunakan format gambar JPG, PNG, atau WEBP.',
                icon: 'error',
                confirmButtonColor: '#3498db'
            });
        } else if(status === 'error') {
            Swal.fire({
                title: 'Gagal',
                text: 'Terjadi kesalahan saat mengunggah file.',
                icon: 'error',
                confirmButtonColor: '#3498db'
            });
        }
    </script>
    <?php endif; ?>

</body>
</html>