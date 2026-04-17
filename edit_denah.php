<?php
session_start();
if (!isset($_SESSION['terverifikasi'])) { header("Location: verifikasi.php"); exit(); }
include 'koneksi.php';

$id = $_GET['id'] ?? '';
$table = $_GET['table'] ?? '';
if (!$id || !$table) { die("Data tidak ditemukan."); }

$query = mysqli_query($conn, "SELECT * FROM $table WHERE id = '$id'");
$data = mysqli_fetch_assoc($query);
if (!$data) { die("Data ID $id tidak ditemukan di tabel $table."); }

$filePath = 'uploads/' . $data['file_gambar'];
$namaDenah = !empty($data['nama_lantai']) ? $data['nama_lantai'] : ($data['keterangan'] ?? 'Denah');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Editor Denah - <?= htmlspecialchars($namaDenah) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { 
            --blue: #3498db; 
            --blue-hover: #2980b9;
            --panel: #1a1c1e; 
            --dark: #ffff; 
            --border: #333;
            --text-muted: #aaa;
            --sidebar-width: 280px;
            --toolbar-height: 60px;
            --dark-bg: #2c2e31; /* Abu-abu gelap untuk kontras */
            --viewport-bg: #3d3f42;
        }

        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: var(--dark); 
            color: white; 
            margin: 0; 
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden; 
        }

        /* Toolbar Responsive */
        .toolbar { 
            height: var(--toolbar-height);
            background: var(--panel); 
            padding: 0 15px; 
            display: flex; 
            gap: 8px; 
            align-items: center; 
            border-bottom: 1px solid var(--border); 
            z-index: 100; 
            overflow-x: auto;
            white-space: nowrap;
        }

        .toolbar::-webkit-scrollbar { display: none; }

        .btn { 
            padding: 8px 12px; 
            border-radius: 8px; 
            border: none; 
            cursor: pointer; 
            font-weight: 600; 
            font-size: 13px;
            display: inline-flex; 
            align-items: center; 
            gap: 6px; 
            transition: all 0.2s ease; 
            background: #25282c;
            color: white;
        }

        .btn-blue { background: var(--blue); }
        .btn-blue:hover { background: var(--blue-hover); }
        .btn.active { background: var(--blue); box-shadow: 0 0 10px rgba(52, 152, 219, 0.4); }

        /* Workspace Layout */
        .workspace { 
            display: flex; 
            flex: 1; 
            overflow: hidden; 
            position: relative;
        }

        /* Sidebar Responsive */
        .sidebar { 
            width: var(--sidebar-width); 
            background: var(--panel); 
            border-right: 1px solid var(--border); 
            padding: 15px; 
            display: flex; 
            flex-direction: column; 
            z-index: 50; 
            transition: transform 0.3s ease;
        }

        .item-grid { 
            display: grid; 
            grid-template-columns: repeat(2, 1fr); 
            gap: 10px; 
            margin-top: 15px; 
            overflow-y: auto;
            padding-right: 5px;
        }

        .stamped-item { 
            width: 100%; aspect-ratio: 1/1; background: #25282c; border: 2px solid transparent; 
            border-radius: 10px; padding: 10px; cursor: pointer; object-fit: contain;
            transition: 0.2s;
        }
        .stamped-item:hover, .stamped-item.selected { border-color: var(--blue); background: #2c3e50; }

        /* Viewport */
        .viewport { 
            flex: 1; 
            position: relative; 
            overflow: hidden; 
            background: var(--viewport-bg);
            touch-action: none; /* Penting untuk mobile pinch-zoom kustom */
        }
        
        #canvas-wrapper { position: absolute; transform-origin: 0 0; will-change: transform; }
        canvas { background: white; display: block; box-shadow: 0 0 30px rgba(0,0,0,0.5); 
    z-index: 1; /* Jangan biarkan terlalu tinggi */}

        /* Floating Input */
        #text-bubble {
            position: fixed; display: none; background: var(--panel); 
            padding: 15px; border-radius: 12px; border: 1px solid var(--blue);
            z-index: 2000; box-shadow: 0 15px 35px rgba(0,0,0,0.6); width: 220px;
        }
        #text-bubble input {
            width: 100%; background: #000; color: white; border: 1px solid var(--border);
            padding: 10px; margin-bottom: 10px; border-radius: 6px;
        }

        /* UI Overlays */
        #ghost-preview { position: fixed; pointer-events: none; opacity: 0.6; display: none; z-index: 1000; transform: translate(-50%, -50%); }
        #eraser-cursor { position: fixed; pointer-events: none; border: 2px solid white; border-radius: 50%; display: none; z-index: 1001; transform: translate(-50%, -50%); mix-blend-mode: difference; }
        .zoom-info { position: absolute; bottom: 20px; right: 20px; background: rgba(0,0,0,0.7); padding: 5px 15px; border-radius: 30px; font-size: 12px; border: 1px solid rgba(255,255,255,0.1); }

        /* Mobile View (max-width: 768px) */
        @media (max-width: 768px) {
            :root { --sidebar-width: 80px; }
            .sidebar { padding: 10px; align-items: center; }
            .sidebar h4, .sidebar p, .sidebar .btn span, .sidebar .cara-pakai, .sidebar .btn-add-text { display: none; }
            .item-grid { grid-template-columns: 1fr; width: 100%; }
            .toolbar { height: 50px; gap: 5px; padding: 0 10px; }
            .toolbar .btn span { display: none; }
            .toolbar .btn { padding: 8px; }
            #text-bubble { width: 180px; left: 50% !important; top: 20% !important; transform: translateX(-50%); }
        }

        /* Utilities */
        .loading-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.85); z-index: 9999; justify-content: center; align-items: center; flex-direction: column; }
        .spinner { border: 3px solid rgba(255,255,255,0.1); border-top: 3px solid var(--blue); border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; }
        @keyframes spin { 100% { transform: rotate(360deg); } }
        .mode-move { cursor: grab; }
        .mode-move:active { cursor: grabbing; }
        .btn-reset-view {
    position: absolute;
    bottom: 60px; /* Di atas zoom info */
    right: 20px;
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: var(--blue);
    color: white;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    z-index: 100;
    transition: transform 0.2s;
}

.btn-reset-view:hover {
    transform: scale(1.1);
    background: var(--blue-hover);
}

.btn-reset-view:active {
    transform: scale(0.9);
}
    </style>
</head>
<body class="mode-move">

<div class="loading-overlay" id="loading">
    <div class="spinner"></div>
    <p style="margin-top: 15px; color: var(--blue); font-weight: 500;">Memuat Denah...</p>
</div>

<img id="ghost-preview" src="" alt="">
<div id="eraser-cursor"></div>

<header class="toolbar">
    <button class="btn" onclick="location.href='index.php'" title="Kembali">⬅ <span>Kembali</span></button>
    <div style="flex: 1; overflow: hidden; text-overflow: ellipsis; font-weight: 700; color: var(--blue); font-size: 14px;"><?= htmlspecialchars($namaDenah) ?></div>
    
    <button id="btn-move" class="btn active" onclick="setMode('move')">🖐 <span>Geser</span></button>
    <button id="btn-text" class="btn" onclick="setMode('text')"><span>T</span> <span>Teks</span></button>
    <button id="btn-erase" class="btn" onclick="setMode('erase')">🧽 <span>Hapus</span></button>
    <button id="btn-undo" class="btn" onclick="undo()">↩ <span>Undo</span></button>
    
    <button class="btn btn-blue" onclick="saveImage()">💾 <span>Simpan</span></button>
</header>

<main class="workspace">
    <aside class="sidebar">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
            <h4 style="margin: 0; font-size: 11px; color: var(--text-muted); letter-spacing: 1px;">ASSET</h4>
            <button class="btn btn-add-text" onclick="location.href='upload_asset.php'" style="padding: 4px 8px; font-size: 10px;">+ Upload</button>
        </div>
        
        <div class="item-grid">
            <?php
            $assets = glob("assets/items/*.{jpg,png,jpeg,gif,webp}", GLOB_BRACE);
            foreach($assets as $assetPath): ?>
                <img src="<?= $assetPath ?>" class="stamped-item" onclick="selectItem(this)" title="<?= basename($assetPath) ?>">
            <?php endforeach; ?>
        </div>
        
        <div style="margin-top: 20px; border-top: 1px solid var(--border); padding-top: 15px; width: 100%;">
            <p style="font-size: 10px; color: var(--blue); font-weight: 700; margin-bottom: 10px;">UKURAN</p>
            <input type="range" id="sizeRange" min="10" max="600" value="150" style="width: 100%; accent-color: var(--blue);">
            <div id="sizeVal" style="text-align: center; font-size: 12px; margin-top: 5px; color: var(--text-muted);">150px</div>
        </div>

        <div class="cara-pakai" style="margin-top: auto; font-size: 11px; color: #666; background: #131517; padding: 10px; border-radius: 8px;">
            <b>Tips:</b><br>
            • Scroll/Cubit untuk Zoom<br>
            • Pilih item lalu klik di denah<br>
            • Klik Teks untuk geser posisi
        </div>
    </aside>

    <section class="viewport" id="viewport">
        <div id="canvas-wrapper">
            <canvas id="canvasEditor"></canvas>
        </div>
        <button class="btn-reset-view" onclick="fitToView()" title="Kembali ke Tengah">
        🎯
    </button>

    <div class="zoom-info">Zoom: <span id="zoomPercent">100%</span></div>
        
        <div id="text-bubble">
            <input type="text" id="target-text-input" placeholder="Isi teks...">
            <div style="display: flex; gap: 8px;">
                <button class="btn btn-blue" onclick="confirmText()" style="flex: 1; justify-content: center;">OK</button>
                <button class="btn" onclick="cancelText()" style="flex: 1; justify-content: center;">Batal</button>
            </div>
        </div>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const viewport = document.getElementById('viewport');
const wrapper = document.getElementById('canvas-wrapper');
const canvas = document.getElementById('canvasEditor');
const ctx = canvas.getContext('2d', { alpha: false });
const ghost = document.getElementById('ghost-preview');
const eraserCursor = document.getElementById('eraser-cursor');
const sizeRange = document.getElementById('sizeRange');
const sizeVal = document.getElementById('sizeVal');
const textBubble = document.getElementById('text-bubble');
const textInput = document.getElementById('target-text-input');

let mode = 'move', scale = 1, originX = 0, originY = 0;
let isPanning = false, startX, startY, brushSize = 150, selectedItemSrc = null;
let undoStack = [];
const maxUndo = 20;

let activeText = null; 
let isDraggingText = false;

const img = new Image();
img.src = '<?= $filePath ?>?' + new Date().getTime();
document.getElementById('loading').style.display = 'flex';

ctx.fillStyle = "white";
ctx.fillRect(0, 0, canvas.width, canvas.height);
img.onload = () => {
    canvas.width = img.width;
    canvas.height = img.height;
    redrawCanvas();
    saveState(); 
    fitToView();
    document.getElementById('loading').style.display = 'none';
};

function redrawCanvas() {
    // 1. Bersihkan background ke putih
    ctx.fillStyle = "white";
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    
    // 2. Gambar base image asli
    ctx.drawImage(img, 0, 0);

    // 3. Jika ada histori (undo), gambar state terakhir
    if (undoStack.length > 0) {
        let lastState = new Image();
        lastState.src = undoStack[undoStack.length - 1];
        lastState.onload = () => {
            ctx.drawImage(lastState, 0, 0);
            drawFloatingText(); // Gambar teks setelah state siap
        }
    } else {
        drawFloatingText();
    }
}

function drawFloatingText() {
if (!activeText || !activeText.content || activeText.content.trim() === "") return;    ctx.save();
    ctx.font = `bold ${activeText.size}px 'Plus Jakarta Sans', sans-serif`;
    ctx.textBaseline = "middle";
    ctx.textAlign = "center";
    
    // Border Putih
    ctx.strokeStyle = "white";
    ctx.lineWidth = activeText.size * 0.1;
    ctx.strokeText(activeText.content, activeText.x, activeText.y);
    
    // Teks Hitam
    ctx.fillStyle = "black";
    ctx.fillText(activeText.content, activeText.x, activeText.y);
    ctx.restore();
}

function saveState() {
    if (undoStack.length >= maxUndo) undoStack.shift();
    undoStack.push(canvas.toDataURL('image/png'));
}

function undo() {
    if (undoStack.length > 1) {
        undoStack.pop();
        activeText = null; 
        const lastImg = new Image();
        lastImg.src = undoStack[undoStack.length - 1];
        lastImg.onload = () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(lastImg, 0, 0);
        };
    }
}

function updateTransform() {
requestAnimationFrame(() => {
        wrapper.style.transform = `translate(${originX}px, ${originY}px) scale(${scale})`;
        document.getElementById('zoomPercent').innerText = Math.round(scale * 100) + '%';
    });
            }
    

function fitToView() {
if (img.width === 0) return; // Jangan jalankan jika gambar belum ada
    const pad = window.innerWidth < 768 ? 20 : 40;
    scale = Math.min((viewport.clientWidth - pad) / canvas.width, (viewport.clientHeight - pad) / canvas.height);
    originX = (viewport.clientWidth - canvas.width * scale) / 2;
    originY = (viewport.clientHeight - canvas.height * scale) / 2;
    updateTransform();
}

// Handlers for Mouse & Touch
function handleStart(e) {
    const clientX = e.touches ? e.touches[0].clientX : e.clientX;
    const clientY = e.touches ? e.touches[0].clientY : e.clientY;
    const p = getCoords(clientX, clientY);
    
    if (mode === 'text') {
        if (!activeText) {
            textBubble.style.display = 'block';
            textBubble.style.left = Math.min(clientX, window.innerWidth - 250) + 'px';
            textBubble.style.top = Math.min(clientY, window.innerHeight - 150) + 'px';
            activeText = { x: p.x, y: p.y, content: '', size: brushSize / 2 };
            textInput.focus();
        } else {
            isDraggingText = true;
        }
    } else if (mode === 'move') {
        isPanning = true;
        startX = clientX - originX;
        startY = clientY - originY;
    } else if (mode === 'stamp' && selectedItemSrc) {
        placeStamp(clientX, clientY);
    } else if (mode === 'erase') {
        isPanning = true;
        doErase(clientX, clientY);
    }
}

function handleMove(e) {
    const clientX = e.touches ? e.touches[0].clientX : e.clientX;
    const clientY = e.touches ? e.touches[0].clientY : e.clientY;
    const p = getCoords(clientX, clientY);

    if (isDraggingText && activeText) {
        activeText.x = p.x;
        activeText.y = p.y;
        redrawCanvas();
    } else if (isPanning) {
        if (mode === 'move') {
            originX = clientX - startX;
            originY = clientY - startY;
            updateTransform();
        } else if (mode === 'erase') { 
            doErase(clientX, clientY); 
        }
    }

    // Cursor Visuals
    if (mode === 'stamp' && selectedItemSrc) {
        ghost.style.display = 'block';
        ghost.style.left = clientX + 'px'; ghost.style.top = clientY + 'px';
        ghost.style.width = (brushSize * scale) + 'px';
    } else if (mode === 'erase') {
        eraserCursor.style.display = 'block';
        eraserCursor.style.left = clientX + 'px'; eraserCursor.style.top = clientY + 'px';
        eraserCursor.style.width = (brushSize * scale) + 'px';
        eraserCursor.style.height = (brushSize * scale) + 'px';
    } else {
        ghost.style.display = 'none';
        eraserCursor.style.display = 'none';
    }
}

viewport.addEventListener('mousedown', handleStart);
viewport.addEventListener('touchstart', (e) => { if(e.touches.length === 1) handleStart(e); }, {passive: false});
window.addEventListener('mousemove', handleMove);
window.addEventListener('touchmove', (e) => { if(e.touches.length === 1) handleMove(e); }, {passive: false});
window.addEventListener('mouseup', () => {
    if (isPanning) {
        if (mode === 'erase') saveState();
        
        // Cek jika gambar keluar batas (Auto Center)
        const rect = canvas.getBoundingClientRect();
        const vW = viewport.clientWidth;
        const vH = viewport.clientHeight;

        // Jika gambar sama sekali tidak terlihat di layar
        if (rect.right < 100 || rect.left > vW - 100 || rect.bottom < 100 || rect.top > vH - 100) {
            fitToView(); // Kembalikan ke tengah otomatis
        }
    }
    isPanning = false;
    isDraggingText = false;
});window.addEventListener('touchend', () => { if (isPanning && mode === 'erase') saveState(); isPanning = false; isDraggingText = false; });

// Wheel Zoom
viewport.addEventListener('wheel', e => {
    e.preventDefault();
    const delta = e.deltaY > 0 ? -0.1 : 0.1;
    const oldScale = scale;
    scale = Math.min(Math.max(0.05, scale + delta), 5);
    const rect = viewport.getBoundingClientRect();
    originX = (e.clientX - rect.left) - ((e.clientX - rect.left) - originX) * (scale / oldScale);
    originY = (e.clientY - rect.top) - ((e.clientY - rect.top) - originY) * (scale / oldScale);
    updateTransform();
}, { passive: false });

function confirmText() {
    if (textInput.value.trim() !== "") {
        activeText.content = textInput.value;
        textBubble.style.display = 'none';
        redrawCanvas();
    } else {
        cancelText();
    }
}

function cancelText() {
    activeText = null; textBubble.style.display = 'none'; textInput.value = ""; redrawCanvas();
}

sizeRange.oninput = function() {
    brushSize = parseInt(this.value);
    sizeVal.innerText = brushSize + "px";
    if (activeText) { activeText.size = brushSize / 2; redrawCanvas(); }
};

function getCoords(clientX, clientY) {
    const rect = viewport.getBoundingClientRect();
    return {
        x: (clientX - rect.left - originX) / scale,
        y: (clientY - rect.top - originY) / scale
    };
}

function setMode(newMode) {
    if (activeText && activeText.content !== "" && newMode !== 'text') finalizeText();
    mode = newMode;
    document.body.className = 'mode-' + newMode;
    document.querySelectorAll('.btn').forEach(b => b.classList.remove('active'));
    document.getElementById('btn-' + newMode)?.classList.add('active');
}

function finalizeText() {
    if (!activeText) return;
    ctx.save();
    ctx.font = `bold ${activeText.size}px 'Plus Jakarta Sans', sans-serif`;
    ctx.textBaseline = "middle"; ctx.textAlign = "center";
    ctx.strokeStyle = "white"; ctx.lineWidth = activeText.size * 0.1;
    ctx.strokeText(activeText.content, activeText.x, activeText.y);
    ctx.fillStyle = "black"; ctx.fillText(activeText.content, activeText.x, activeText.y);
    ctx.restore();
    activeText = null; textInput.value = ""; saveState();
}

function selectItem(el) {
    if(activeText) finalizeText();
    document.querySelectorAll('.stamped-item').forEach(i => i.classList.remove('selected'));
    el.classList.add('selected');
    selectedItemSrc = el.src;
    ghost.src = el.src;
    setMode('stamp');
}

function placeStamp(clientX, clientY) {
    const p = getCoords(clientX, clientY);
    const stamp = new Image();
    stamp.src = selectedItemSrc;
    stamp.onload = () => {
        const ratio = stamp.naturalWidth / stamp.naturalHeight;
        let drawW = brushSize;
        let drawH = brushSize / ratio;
        ctx.drawImage(stamp, p.x - drawW/2, p.y - drawH/2, drawW, drawH);
        saveState();
    };
}

function doErase(clientX, clientY) {
    const p = getCoords(clientX, clientY);
    ctx.save();
    ctx.fillStyle = "white";
    ctx.beginPath();
    ctx.arc(p.x, p.y, brushSize / 2, 0, Math.PI * 2);
    ctx.fill();
    ctx.restore();
}

function saveImage() {
    if(activeText) finalizeText(); 
    Swal.fire({
        title: 'Simpan?',
        text: 'Denah akan diperbarui secara permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3498db'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            const dataURL = canvas.toDataURL('image/jpeg', 0.9);
            const formData = new URLSearchParams();
            formData.append('image', dataURL);
            formData.append('id', '<?= $id ?>');
            formData.append('table', '<?= $table ?>');

            fetch('save_denah_edit.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            })
            .then(res => res.json())
            .then(data => {
                Swal.close();
                if(data.success) Swal.fire('Berhasil!', '', 'success').then(() => location.reload());
                else Swal.fire('Gagal', data.message, 'error');
            });
        }
    });
}
</script>
</body>
</html>