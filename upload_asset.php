<?php
session_start();
if (!isset($_SESSION['terverifikasi'])) { 
    header("Location: verifikasi.php"); 
    exit(); 
}

$back_id = $_GET['id'] ?? ($_POST['back_id'] ?? '');
$back_table = $_GET['table'] ?? ($_POST['back_table'] ?? '');

if (isset($_GET['delete'])) {
    $file_name = basename($_GET['delete']);
    $file_path = "assets/items/" . $file_name;
    
    if (!empty($file_name) && file_exists($file_path) && is_file($file_path)) {
        unlink($file_path);
        $status = "deleted";
    } else {
        $status = "not_found";
    }
    header("Location: upload_asset.php?status=$status&id=" . urlencode($back_id) . "&table=" . urlencode($back_table));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['new_asset'])) {
    $targetDir = "assets/items/";
    if (!file_exists($targetDir)) { 
        mkdir($targetDir, 0777, true); 
    }
    
    $fileName = time() . '_' . basename($_FILES["new_asset"]["name"]);
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
    
    header("Location: upload_asset.php?status=$status&id=" . urlencode($back_id) . "&table=" . urlencode($back_table));
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Library - Pro Manager</title>
    <link rel="icon" type="image/svg+xml" href="assets/logo2.svg" sizes="any">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary: #3b82f6;
            --danger: #ef4444;
            --bg-dark: #0b0f1a;
            --bg-panel: #161e2d;
            --border: #2d3748;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: var(--bg-dark); 
            color: var(--text-main); 
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }

        .main-card { 
            width: 100%;
            max-width: 850px; 
            margin: 40px auto;
            background: var(--bg-panel); 
            padding: 40px; 
            border-radius: 28px; 
            border: 1px solid var(--border);
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.7);
            position: relative;
        }

        .close-wrapper {
            position: absolute;
            top: 25px;
            right: 25px;
        }
        .btn-x {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-muted);
            text-decoration: none;
            border-radius: 14px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid var(--border);
        }
        .btn-x:hover {
            background: var(--danger);
            color: white;
            border-color: var(--danger);
            transform: rotate(90deg);
        }

        .header-content h2 { margin: 0; font-size: 28px; font-weight: 700; letter-spacing: -0.5px; }
        .header-content p { color: var(--text-muted); margin: 8px 0 35px; font-size: 15px; }

        .upload-container {
            background: rgba(0, 0, 0, 0.2);
            border: 2px dashed var(--border);
            border-radius: 20px;
            padding: 40px 20px;
            text-align: center;
            margin-bottom: 45px;
            transition: 0.3s ease;
        }
        .upload-container:hover { border-color: var(--primary); background: rgba(59, 130, 246, 0.02); }

        .file-input-label {
            display: block;
            margin-bottom: 20px;
            color: var(--text-muted);
            font-size: 14px;
        }

        .btn-upload {
            background: var(--primary);
            color: white;
            padding: 14px 28px;
            border-radius: 12px;
            border: none;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 4px 14px 0 rgba(59, 130, 246, 0.39);
        }
        .btn-upload:hover { transform: translateY(-2px); filter: brightness(1.1); }

        .grid-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 0 5px;
        }
        .grid-info span { font-weight: 600; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); }

        .asset-grid { 
    display: grid; 
    grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); 
    gap: 12px; 
    max-height: 500px; 
    overflow-y: auto;
    padding: 10px 5px;
}
        .asset-grid::-webkit-scrollbar { width: 6px; }
        .asset-grid::-webkit-scrollbar-thumb { background: var(--border); border-radius: 10px; }

        .asset-item { 
    background: #1c2638; 
    padding: 12px; 
    border-radius: 16px; 
    text-align: center; 
    border: 1px solid var(--border); 
    transition: all 0.2s ease;
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}
        .asset-item:hover { border-color: var(--primary); transform: translateY(-5px); background: #222d42; }

        .asset-item img { 
    width: 100%; 
    height: 70px; 
    object-fit: contain; 
    margin-bottom: 8px;
    filter: drop-shadow(0 4px 6px rgba(0,0,0,0.3));
}

        .asset-item .name {
    font-size: 10px; 
    color: var(--text-muted);
    width: 100%;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis; 
    opacity: 0.8;
}

        .btn-delete {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 36px;
            height: 36px;
            background: rgba(239, 68, 68, 0.15);
            color: var(--danger);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: 0.2s;
            opacity: 0.9; 
            z-index: 10;
        }
        .btn-delete i { font-size: 16px; pointer-events: none; }
        
        @media (hover: hover) {
            .btn-delete { opacity: 0; }
            .asset-item:hover .btn-delete { opacity: 1; }
        }

        .btn-delete:hover { background: var(--danger); color: white; transform: scale(1.1); }
        .btn-delete:active { transform: scale(0.9); }

        @media (max-width: 600px) {
    .main-card { 
        padding: 20px 15px; 
        margin: 0; 
        border-radius: 0;
    }
    
    .asset-grid { 
        /* Di HP dipaksa 3 kolom jika layar cukup, atau minimal 2 kolom bersih */
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); 
        gap: 10px;
    }

    .header-content h2 { font-size: 22px; }
    
    .upload-container {
        padding: 25px 15px;
        margin-bottom: 30px;
    }
}
    </style>
</head>
<body>

    <div class="main-card">
        <div class="close-wrapper">
            <a href="edit_denah.php?id=<?= urlencode($back_id) ?>&table=<?= urlencode($back_table) ?>" class="btn-x" title="Close Library">
                <i class="fa-solid fa-xmark fa-lg"></i>
            </a>
        </div>

        <div class="header-content">
            <h2>Asset Library</h2>
            <p>Unggah dan kelola koleksi ikon denah digital Anda</p>
        </div>
        
        <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="back_id" value="<?= htmlspecialchars($back_id) ?>">
            <input type="hidden" name="back_table" value="<?= htmlspecialchars($back_table) ?>">
            
            <div class="upload-container">
                <i class="fa-solid fa-cloud-arrow-up" style="font-size: 38px; color: var(--primary); margin-bottom: 20px; display: block;"></i>
                <label class="file-input-label">Pilih gambar (PNG, JPG, atau WEBP)</label>
                <input type="file" name="new_asset" accept="image/*" required style="margin-bottom: 20px;"><br>
                <button type="submit" class="btn-upload">
                    <i class="fa-solid fa-plus"></i> Tambah Ke Library
                </button>
            </div>
        </form>

        <div class="grid-info">
            <span>Koleksi Aset</span>
            <span style="font-size: 11px; opacity: 0.6;"><?= count(glob("assets/items/*.*")) ?> Items</span>
        </div>

        <div class="asset-grid">
            <?php
            $files = glob("assets/items/*.{jpg,png,jpeg,gif,webp}", GLOB_BRACE);
            if(empty($files)) {
                echo "<div style='grid-column: 1/-1; text-align: center; padding: 60px 0; color: var(--text-muted); opacity: 0.5;'>
                        <i class='fa-regular fa-folder-open' style='font-size: 40px; margin-bottom: 15px;'></i>
                        <p>Belum ada aset tersedia</p>
                      </div>";
            } else {
                array_multisort(array_map('filemtime', $files), SORT_DESC, $files);
                
                foreach($files as $file) {
                    $name = basename($file);
                    ?>
                    <div class="asset-item">
    <button type="button" class="btn-delete" onclick="handleDelete('<?= $name ?>')" aria-label="Hapus Aset">
        <i class="fa-solid fa-trash-can"></i>
    </button>
    <img src="<?= $file ?>" alt="<?= $name ?>" loading="lazy">
    <div class="name" title="<?= $name ?>"><?= $name ?></div>
</div>
                    <?php
                }
            }
            ?>
        </div>
    </div>

    <script>
        function handleDelete(fileName) {
            Swal.fire({
                title: 'Hapus Aset?',
                text: "File " + fileName + " akan dihapus selamanya.",
                icon: 'warning',
                showCancelButton: true,
                reverseButtons: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#2d3748',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                background: '#161e2d',
                color: '#f8fafc',
                backdrop: `rgba(0,0,0,0.8)`
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `upload_asset.php?delete=${fileName}&id=<?= urlencode($back_id) ?>&table=<?= urlencode($back_table) ?>`;
                }
            })
        }

        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');
        const toastConfig = {
            background: '#161e2d',
            color: '#f8fafc',
            confirmButtonColor: '#3b82f6',
        };

        if(status === 'success') {
            Swal.fire({ ...toastConfig, title: 'Berhasil!', text: 'Aset baru telah ditambahkan.', icon: 'success' });
        } else if(status === 'deleted') {
            Swal.fire({ ...toastConfig, title: 'Terhapus', text: 'Aset berhasil dibuang dari library.', icon: 'success' });
        } else if(status === 'invalid') {
            Swal.fire({ ...toastConfig, title: 'Format Salah', text: 'Mohon unggah file gambar yang valid.', icon: 'error' });
        } else if(status === 'error') {
            Swal.fire({ ...toastConfig, title: 'Gagal', text: 'Terjadi gangguan saat mengunggah.', icon: 'error' });
        }
    </script>

</body>
</html>