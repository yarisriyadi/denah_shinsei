<?php include 'koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
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
            padding: 35px 25px; 
        }
        .brand { 
            font-size: 22px; 
            font-weight: 800; 
            letter-spacing: 1px; 
            display: flex; 
            align-items: center; 
            gap: 8px; color: white; 
            margin: 0; }
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
    position: relative;
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
            position: absolute; 
            bottom: 20px; 
            left: 20px; 
            z-index: 2500;
            background: var(--blue); 
            color: white; 
            border: none;
            width: 50px; 
            height: 50px; 
            border-radius: 50%;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3); 
            font-size: 20px; 
            cursor: pointer;
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
            #mobile-menu-btn { display: block; }
            .map-label { 
                left: 15px; top: 15px; font-size: 11px; padding: 6px 12px;
                max-width: 160px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            }
            .btn-print { 
                padding: 8px 12px; font-size: 11px; top: 10px; right: 10px;
                max-width: 120px;
            }
        @media print {
            #sidebar, .btn-print, #mobile-menu-btn { display: none !important; }
        }
        body.swal2-shown {
    height: 100vh !important;
    overflow: hidden !important;
}
}

    </style>
</head>
<body>

<button id="mobile-menu-btn" onclick="toggleSidebar()">☰</button>

<div id="sidebar">
    <div class="sidebar-header">
        <h1 class="brand">SHINSEI <span>MAP</span></h1>
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
<a href="javascript:void(0)" class="btn-delete" onclick="deleteItem(event, '<?= $sub['id'] ?>', '<?= $tableName ?>', '<?= htmlspecialchars($sub['keterangan']) ?>')">×</a>                                    </div>
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
    <div class="map-label" id="current-label">Pilih Denah</div>
    
    <button class="btn-print" id="btnExport" onclick="printUltraHighResDenah()">
        <svg xmlns="http://www.w3.org/2000/svg" height="20" viewBox="0 -960 960 960" width="20">
            <path d="M640-640v-120H320v120h-80v-200h480v200h-80Zm-480 80h640-640Zm560 100q17 0 28.5-11.5T760-500q0-17-11.5-28.5T720-540q-17 0-28.5 11.5T680-500q0 17 11.5 28.5T720-460Zm-80 260v-160H320v160h320Zm80 80H240v-160H80v-240q0-51 35-85.5t85-34.5h560q51 0 85.5 34.5T880-520v240H720v160Zm80-240v-160q0-17-11.5-28.5T760-560H200q-17 0-28.5 11.5T160-520v160h80v-80h480v80h80Z"/>
        </svg>
        <span>Export PDF</span>
    </button>

    <div id="map"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/@geoman-io/leaflet-geoman-free@latest/dist/leaflet-geoman.css" />
<script src="https://unpkg.com/@geoman-io/leaflet-geoman-free@latest/dist/leaflet-geoman.min.js"></script>
<script>
    
    var map = L.map('map', {
        crs: L.CRS.Simple, 
        minZoom: 0,
        maxZoom: 4, 
        zoomSnap: 0.1,
        attributionControl: false, 
        zoomControl: true 
    });

    var currentOverlay = null;

    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('active');
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
        document.querySelectorAll('.child-container').forEach(el => { if(el.id !== 'child-' + id) el.style.display = 'none'; });
        document.querySelectorAll('.denah-group').forEach(el => { if(el.id !== 'group-' + id) el.classList.remove('open'); });
        if (container.style.display === "block") { container.style.display = "none"; group.classList.remove('open'); }
        else { container.style.display = "block"; group.classList.add('open'); }
    }

    function loadMap(file, w, h, judul, element) {
        if (window.event && (window.event.target.classList.contains('btn-delete') || window.event.target.classList.contains('btn-edit'))) return;
        
        document.getElementById('current-label').innerText = judul;
        document.querySelectorAll('.sub-item, .sub-child').forEach(i => i.classList.remove('active'));
        element.classList.add('active');

        if(currentOverlay) map.removeLayer(currentOverlay);
        var sw = map.unproject([0, h], map.getMaxZoom());
        var ne = map.unproject([w, 0], map.getMaxZoom());
        var bounds = new L.LatLngBounds(sw, ne);
        
        currentOverlay = L.imageOverlay('uploads/' + file, bounds).addTo(map);
        map.fitBounds(bounds);
    }
var preloadedImages = [];

function preloadDenahImages() {
    const items = document.querySelectorAll('[onclick*="handleMapLoad"]');
    
    items.forEach(item => {
        const onclickAttr = item.getAttribute('onclick');
        const match = onclickAttr.match(/'([^']+)'/);
        
        if (match && match[1]) {
            const imgUrl = 'uploads/' + match[1];
            const img = new Image();
            img.src = imgUrl;
            preloadedImages.push(img); 
        }
    });
    console.log("Preloading " + preloadedImages.length + " gambar selesai.");
}

    function renameItem(event, id, table, oldName) {
    event.stopPropagation(); 

    Swal.fire({
        title: 'Ubah Nama',
        input: 'text',
        inputValue: oldName,
        inputLabel: 'Masukkan nama baru untuk denah/layer ini',
        showCancelButton: true,
        reverseButtons: true,
        confirmButtonText: 'Simpan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#3498db',
        cancelButtonColor: '#ff4757',
        background: '#1a1c1e', 
        color: '#ffffff',
        inputAttributes: {
            autocapitalize: 'off'
        },
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
            .then(response => {
                if (!response.ok) throw new Error(response.statusText);
                return response.json();
            })
            .catch(error => {
                Swal.showValidationMessage(`Request failed: ${error}`);
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed) {
            if (result.value.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Nama telah diperbarui.',
                    timer: 1500,
                    showConfirmButton: false,
                    background: '#1a1c1e',
                    color: '#ffffff'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: result.value.message,
                    background: '#1a1c1e',
                    color: '#ffffff'
                });
            }
        }
    });
}

    async function printUltraHighResDenah() {
        if (!currentOverlay) {
            alert("Pilih denah terlebih dahulu!");
            return;
        }

        const btn = document.getElementById('btnExport');
        const originalText = btn.innerHTML;
        const judul = document.getElementById('current-label').innerText;

        btn.disabled = true;
        btn.innerHTML = "Processing...";

        try {
            const imgElement = currentOverlay.getElement();

            const canvas = await html2canvas(imgElement, {
                useCORS: true,
                backgroundColor: null,
                scale: 5,             
                logging: false,
                imageTimeout: 0
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

            const xPos = (pageWidth - printWidth) / 2;
            const yPos = (pageHeight - printHeight) / 2;

            pdf.addImage(imgData, 'PNG', xPos, yPos, printWidth, printHeight, undefined, 'NONE');
            pdf.save(`Denah${judul.replace(/[^a-z0-9]/gi, '_')}.pdf`);

        } catch (err) {
            console.error(err);
            alert("Gagal Export: " + err.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }

   window.onload = function() {
    preloadDenahImages();
    const firstItem = document.querySelector('.sub-item');
    if(firstItem) firstItem.click();
};
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

</script>
</body>
</html>