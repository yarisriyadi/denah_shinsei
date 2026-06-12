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
<link rel="icon" type="image/svg+xml" href="assets/logo2.svg" sizes="any">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Editor Denah - <?= htmlspecialchars($namaDenah) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --blue: #2563eb;
            --blue-light: #dbeafe;
            --blue-hover: #1d4ed8;
            --panel: #ffffff;
            --bg-workspace: #d1d5db;
            --dark-text: #1e293b;
            --border: #e2e8f0;
            --text-muted: #64748b;
            --sidebar-width: 300px;
            --toolbar-height: 70px;
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }

        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg-workspace);
            color: var(--dark-text);
            margin: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .toolbar {
            height: var(--toolbar-height);
            background: var(--panel);
            padding: 0 24px;
            display: flex;
            gap: 12px;
            align-items: center;
            border-bottom: 1px solid var(--border);
            z-index: 1000;
            box-shadow: var(--shadow);
        }

        .btn {
            padding: 10px 16px;
            border-radius: 10px;
            border: 1px solid var(--border);
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            background: white;
            color: var(--dark-text);
        }

        .btn:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
            transform: translateY(-1px);
        }

        .btn-blue { 
            background: var(--blue); 
            color: white; 
            border: none;
        }

        .btn-blue:hover { 
            background: var(--blue-hover); 
            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3);
        }

        .btn.active { 
            background: var(--blue-light); 
            color: var(--blue); 
            border-color: var(--blue); 
        }

        .denah-title {
            flex: 1;
            font-weight: 700;
            color: var(--dark-text);
            font-size: 16px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            padding: 0 10px;
        }

        .workspace {
            display: flex;
            flex: 1;
            overflow: hidden;
            position: relative;
        }

        .sidebar {
            width: var(--sidebar-width);
            background: var(--panel);
            border-right: 1px solid var(--border);
            padding: 24px;
            display: flex;
            flex-direction: column;
            z-index: 500;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid var(--border);
}

.sidebar-header h4 {
    margin: 0;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-muted);
}
        .sidebar-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}

.btn-close-sidebar {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: #f1f5f9;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-close-sidebar:hover {
    background: #fee2e2;
    color: #ef4444;
    border-color: #fecaca;
}

        .item-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            overflow-y: auto;
            padding-bottom: 20px;
        }

        .stamped-item {
            width: 100%;
            aspect-ratio: 1/1;
            background: #f8fafc;
            border: 2px solid transparent;
            border-radius: 12px;
            padding: 12px;
            cursor: pointer;
            object-fit: contain;
            transition: all 0.2s ease;
        }

        .stamped-item:hover {
            transform: scale(1.05);
            background: white;
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1);
        }

        .stamped-item.selected {
            border-color: var(--blue);
            background: var(--blue-light);
        }

        input[type="range"] {
            -webkit-appearance: none;
            width: 100%;
            height: 6px;
            background: #e2e8f0;
            border-radius: 5px;
            outline: none;
        }

        input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 18px;
            height: 18px;
            background: var(--blue);
            border-radius: 50%;
            cursor: pointer;
            transition: 0.2s;
        }

        @media (max-width: 768px) {
            :root { --toolbar-height: 60px; --sidebar-width: 260px; }
            
            .toolbar { padding: 0 12px; gap: 8px; }
            .toolbar .btn span { display: none; }
            .toolbar .btn { padding: 10px; border-radius: 8px; }
            
            .sidebar {
                position: absolute;
                left: 0;
                top: 0;
                height: 100%;
                box-shadow: 20px 0 50px rgba(0,0,0,0.1);
                transform: translateX(0);
            }
            
            .sidebar.collapsed {
                transform: translateX(-100%);
            }
            
.toggle-overlay {
    display: flex;
    position: absolute;
    top: 50%;
    left: 10px;
    transform: translateY(-50%);
    z-index: 1100;
}

.btn-toggle-custom {
    width: 32px; 
    height: 32px;
    border-radius: 8px;
    background: var(--panel);
    color: var(--blue);
    border: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: var(--shadow);
    transition: all 0.2s ease;
}

.btn-toggle-custom:hover {
    background: var(--blue-light);
    transform: scale(1.1);
}

@media (min-width: 769px) {
    .toggle-overlay {
        left: 20px;
    }
}         
            .denah-title { font-size: 14px; }
        }
        .viewport { flex: 1; position: relative; overflow: hidden; background: #d1d5db; touch-action: none; }
        #canvas-wrapper { 
            position: absolute; 
            transform-origin: 0 0; 
            will-change: transform; 
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
    		perspective: 1000;
    		-webkit-perspective: 1000;
        }
        canvas { background: white; display: block; box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25); }
        
        #text-bubble {
            position: fixed; display: none; background: white;
            padding: 20px; border-radius: 16px; border: 1px solid var(--blue);
            z-index: 2000; box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1); width: 260px;
        }
        #text-bubble input {
            width: 100%; border: 1px solid var(--border);
            padding: 12px; margin-bottom: 12px; border-radius: 8px; font-family: inherit;
        }

        #ghost-preview { 
            position: fixed; 
            pointer-events: none; 
            opacity: 0.6; display: none; 
            z-index: 9999; 
            transform: translate(-50%, -50%); 
        }
        #ghost-preview, #eraser-cursor, #canvas-wrapper {
    will-change: transform, left, top;
    pointer-events: none; 
}
        #eraser-cursor { position: fixed; pointer-events: none; border: 2px solid var(--blue); border-radius: 50%; display: none; z-index: 1001; transform: translate(-50%, -50%); background: rgba(52, 152, 219, 0.1); }
        .zoom-info { position: absolute; bottom: 20px; right: 80px; background: rgba(255,255,255,0.8); backdrop-filter: blur(4px); padding: 6px 16px; border-radius: 30px; font-size: 12px; font-weight: 600; border: 1px solid var(--border); color: var(--text-muted); }
   
        .loading-overlay { display: none; position: fixed; inset: 0; background: rgba(255,255,255,0.9); z-index: 9999; justify-content: center; align-items: center; flex-direction: column; }
        .spinner { border: 3px solid #f3f3f3; border-top: 3px solid var(--blue); border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; }
        @keyframes spin { 100% { transform: rotate(360deg); } }
        
        .btn-reset-view {
            position: absolute; bottom: 15px; right: 15px; width: 48px; height: 48px; border-radius: 14px;
            background: white; color: var(--dark-text); border: 1px solid var(--border); cursor: pointer;
            box-shadow: var(--shadow); display: flex; align-items: center; justify-content: center; font-size: 18px; z-index: 100; transition: 0.2s;
        }
        .btn-reset-view:hover { background: #f8fafc; color: var(--blue); }
        
.toggle-overlay button i {
    transition: transform 0.3s ease;
}

@media (min-width: 769px) {
    .toggle-overlay {
        display: flex;
        position: absolute;
        top: 24px;
        left: 24px;
        z-index: 1100;
    }
    .sidebar.collapsed {
        margin-left: calc(var(--sidebar-width) * -1);
    }
}

    </style>
</head>
<body class="mode-move">

<div class="loading-overlay" id="loading">
    <div class="spinner"></div>
    <p style="margin-top: 15px; color: var(--blue); font-weight: 600;">Memuat Editor...</p>
</div>

<img id="ghost-preview" src="" alt="">
<div id="eraser-cursor"></div>

<header class="toolbar">
    <button class="btn" onclick="location.href='index.php'" title="Kembali">
        <i class="fa-solid fa-arrow-left"></i> <span>Kembali</span>
    </button>
    <div class="denah-title"><?= htmlspecialchars($namaDenah) ?></div>
    
    <button id="btn-move" class="btn active" onclick="setMode('move')">
        <i class="fa-solid fa-hand"></i> <span>Geser</span>
    </button>
    <button id="btn-text" class="btn" onclick="setMode('text')">
        <i class="fa-solid fa-font"></i> <span>Teks</span>
    </button>
    <button id="btn-erase" class="btn" onclick="setMode('erase')">
        <i class="fa-solid fa-eraser"></i> <span>Hapus</span>
    </button>
    <button id="btn-undo" class="btn" onclick="undo()">
        <i class="fa-solid fa-rotate-left"></i> <span>Undo</span>
    </button>
    <button class="btn btn-blue" onclick="saveImage()">
        <i class="fa-solid fa-floppy-disk"></i> <span>Simpan</span>
    </button>
</header>

<main class="workspace">
    <div class="toggle-overlay" id="toggleOverlay">
    <button class="btn-toggle-custom" onclick="toggleSidebar()" title="Menu Asset">
        <i id="toggleIcon" class="fa-solid fa-bars"></i>
    </button>
</div>

    <aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h4>ASSET</h4>
        <div class="sidebar-actions">
            <button class="btn" onclick="location.href='upload_asset.php?id=<?= urlencode($id) ?>&table=<?= urlencode($table) ?>'" style="padding: 6px 12px; font-size: 12px; background: var(--blue); color: white; border: none;">
                <i class="fa-solid fa-plus"></i> Upload
            </button>
            <button class="btn-close-sidebar" onclick="toggleSidebar()" title="Tutup Menu">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    </div>

        <div class="item-grid">
            <?php
            $assets = glob("assets/items/*.{jpg,png,jpeg,gif,webp}", GLOB_BRACE);
            foreach($assets as $assetPath): ?>
                <img src="<?= $assetPath ?>" class="stamped-item" onclick="selectItem(this)" title="<?= basename($assetPath) ?>">
            <?php endforeach; ?>
        </div>
        
        <div style="margin-top: 24px; border-top: 1px solid var(--border); padding-top: 20px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <span style="font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Ukuran Item</span>
                <span id="sizeVal" style="font-size: 12px; font-weight: 700; color: var(--blue);">150px</span>
            </div>
            <input type="range" id="sizeRange" min="10" max="600" value="150">
        </div>

        <div style="margin-top: auto; font-size: 12px; color: var(--text-muted); background: #f8fafc; padding: 15px; border-radius: 12px; line-height: 1.6; border: 1px solid var(--border);">
            <strong style="color: var(--dark-text); display: block; margin-bottom: 4px;">Instruksi Cepat:</strong>
            • Pilih asset lalu klik pada denah<br>
            • Gunakan 2 jari untuk zoom (HP)<br>
            • Klik simpan jika sudah selesai
        </div>
    </aside>

    <section class="viewport" id="viewport">
        <div id="canvas-wrapper">
            <canvas id="canvasEditor"></canvas>
        </div>
        <button class="btn-reset-view" onclick="fitToView()" title="Fokus Denah">
            <i class="fa-solid fa-expand"></i>
        </button>
        <div class="zoom-info"><i class="fa-solid fa-magnifying-glass"></i> <span id="zoomPercent">100%</span></div>

        <div id="text-bubble">
    <div style="margin-bottom: 10px; display: flex; gap: 10px; align-items: center;">
        <span style="font-size: 11px; font-weight: 700; color: var(--text-muted);">WARNA:</span>
        <button onclick="setTextColor('black')" id="color-black" style="width: 24px; height: 24px; border-radius: 50%; background: black; border: 2px solid var(--blue); cursor: pointer;"></button>
        <button onclick="setTextColor('red')" id="color-red" style="width: 24px; height: 24px; border-radius: 50%; background: red; border: 2px solid transparent; cursor: pointer;"></button>
    </div>
    <input type="text" id="target-text-input" placeholder="Ketik teks di sini...">
    <div style="display: flex; gap: 8px;">
        <button class="btn btn-blue" onclick="confirmText()" style="flex: 1; justify-content: center;">Terapkan</button>
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

let stampRotation = 0; 
let initialTouchAngle = 0;
let initialStampAngle = 0;
let lastTouchDistance = 0;
let touchStartTime = 0;
let isMultiTouch = false; 

const img = new Image();
img.src = '<?= $filePath ?>?' + new Date().getTime();
document.getElementById('loading').style.display = 'flex';

img.onload = () => {
    canvas.width = img.width;
    canvas.height = img.height;
    redrawCanvas();
    saveState();
    fitToView();
    document.getElementById('loading').style.display = 'none';
};

function redrawCanvas() {
    ctx.fillStyle = "white";
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    ctx.drawImage(img, 0, 0);

    if (undoStack.length > 0) {
        let lastState = new Image();
        lastState.src = undoStack[undoStack.length - 1];
        lastState.onload = () => {
            ctx.drawImage(lastState, 0, 0);
            drawFloatingText(); 
        }
    } else {
        drawFloatingText();
    }
}

function drawFloatingText() {
    if (!activeText || !activeText.content || activeText.content.trim() === "") return;
    ctx.save();
    ctx.font = `bold ${activeText.size}px 'Plus Jakarta Sans', sans-serif`;
    ctx.textBaseline = "middle";
    ctx.textAlign = "center";
    ctx.strokeStyle = "white";
    ctx.lineWidth = activeText.size * 0.1;
    ctx.strokeText(activeText.content, activeText.x, activeText.y);
    ctx.fillStyle = activeText.color || 'black';
    ctx.fillText(activeText.content, activeText.x, activeText.y);
    ctx.restore();
}

function saveState() {
    if (undoStack.length >= maxUndo) undoStack.shift();
    undoStack.push(canvas.toDataURL('image/webp'));
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
        const x = Math.round(originX);
        const y = Math.round(originY);
        wrapper.style.transform = `translate3d(${x}px, ${y}px, 0) scale(${scale})`;
        document.getElementById('zoomPercent').innerText = Math.round(scale * 100) + '%';
    });
}

function fitToView() {
    if (img.width === 0) return;
    scale = 0.25; 
    originX = (viewport.clientWidth - canvas.width * scale) / 2;
    originY = (viewport.clientHeight - canvas.height * scale) / 2;
    updateTransform();
}

function getAngle(t1, t2) {
    return Math.atan2(t2.clientY - t1.clientY, t2.clientX - t1.clientX) * 180 / Math.PI;
}

function handleStart(e) {
    if (e.touches && e.touches.length === 2) {
        isMultiTouch = true;
        lastTouchDistance = Math.hypot(
            e.touches[0].clientX - e.touches[1].clientX,
            e.touches[0].clientY - e.touches[1].clientY
        );
        if (mode === 'stamp') {
            initialTouchAngle = getAngle(e.touches[0], e.touches[1]);
            initialStampAngle = stampRotation;
        }
        isPanning = false;
        return;
    }

    isMultiTouch = false;
    touchStartTime = Date.now();
    const clientX = e.touches ? e.touches[0].clientX : e.clientX;
    const clientY = e.touches ? e.touches[0].clientY : e.clientY;
    const p = getCoords(clientX, clientY);

    if (mode === 'text') {
        if (!activeText) {
            textBubble.style.display = 'block';
            textBubble.style.left = Math.min(clientX, window.innerWidth - 250) + 'px';
            textBubble.style.top = Math.min(clientY, window.innerHeight - 150) + 'px';
            activeText = { x: p.x, y: p.y, content: '', size: brushSize / 2, color: selectedTextColor };
            textInput.focus();
        } else {
            isDraggingText = true;
        }
    } 
    else if (mode === 'move') {
        isPanning = true;
        startX = clientX - originX;
        startY = clientY - originY;
    } else if (mode === 'erase') {
        isPanning = true;
        doErase(clientX, clientY);
    }
}

function handleMove(e) {
    if (e.touches && e.touches.length === 2) {
        e.preventDefault();

        if (mode === 'stamp' && selectedItemSrc) {
            const currentAngle = getAngle(e.touches[0], e.touches[1]);
            stampRotation = initialStampAngle + (currentAngle - initialTouchAngle);
            ghost.style.transform = `translate(-50%, -50%) rotate(${stampRotation}deg)`;
            
            lastTouchDistance = Math.hypot(
                e.touches[0].clientX - e.touches[1].clientX,
                e.touches[0].clientY - e.touches[1].clientY
            );
            return; 
        }

        const currentDistance = Math.hypot(
            e.touches[0].clientX - e.touches[1].clientX,
            e.touches[0].clientY - e.touches[1].clientY
        );

        if (lastTouchDistance > 0) {
            const pinchScale = currentDistance / lastTouchDistance;
            const oldScale = scale;
            scale = Math.min(Math.max(0.2, scale * pinchScale), 5.0);
            if (scale !== oldScale) {
                const centerX = (e.touches[0].clientX + e.touches[1].clientX) / 2;
                const centerY = (e.touches[0].clientY + e.touches[1].clientY) / 2;
                const rect = viewport.getBoundingClientRect();
                originX = (centerX - rect.left) - ((centerX - rect.left) - originX) * (scale / oldScale);
                originY = (centerY - rect.top) - ((centerY - rect.top) - originY) * (scale / oldScale);
                updateTransform();
            }
        }
        lastTouchDistance = currentDistance;
        return;
    }
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

    if (mode === 'stamp' && selectedItemSrc) {
        ghost.style.display = 'block';
        ghost.style.left = clientX + 'px'; 
        ghost.style.top = clientY + 'px';
        ghost.style.width = (brushSize * scale) + 'px';
        ghost.style.transform = `translate(-50%, -50%) rotate(${stampRotation}deg)`;
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

function handleEnd(e) {
    if (isPanning && mode === 'erase') saveState();
    
    if (mode === 'stamp' && selectedItemSrc && !isMultiTouch) {
        const timeElapsed = Date.now() - touchStartTime;
        if (timeElapsed < 300) { 
            const clientX = e.changedTouches ? e.changedTouches[0].clientX : e.clientX;
            const clientY = e.changedTouches ? e.changedTouches[0].clientY : e.clientY;
            placeStamp(clientX, clientY);
        }
    }

    isPanning = false;
    isDraggingText = false;
    isMultiTouch = false;
}

viewport.addEventListener('mousedown', handleStart);
viewport.addEventListener('touchstart', (e) => { 
    if(e.touches.length <= 2) handleStart(e); 
}, {passive: false});

window.addEventListener('mousemove', handleMove);
window.addEventListener('touchmove', (e) => { 
    if(e.touches.length <= 2) handleMove(e); 
}, {passive: false});

window.addEventListener('mouseup', handleEnd);
window.addEventListener('touchend', handleEnd);

viewport.addEventListener('wheel', e => {
    e.preventDefault();

    if (mode === 'stamp' && selectedItemSrc) {
        const rotationStep = e.deltaY > 0 ? -5 : 5; 
        stampRotation = (stampRotation + rotationStep) % 360;
        ghost.style.transform = `translate(-50%, -50%) rotate(${stampRotation}deg)`;
        return; 
    }

    const delta = e.deltaY > 0 ? -0.1 : 0.1;
    const oldScale = scale;
    scale = Math.min(Math.max(0.2, scale + delta), 5.0);
    if (scale === oldScale) return;
    const rect = viewport.getBoundingClientRect();
    originX = (e.clientX - rect.left) - ((e.clientX - rect.left) - originX) * (scale / oldScale);
    originY = (e.clientY - rect.top) - ((e.clientY - rect.top) - originY) * (scale / oldScale);
    updateTransform();
}, { passive: false });


function updateToggleVisibility() {
    const sidebar = document.getElementById('sidebar');
    const toggleOverlay = document.getElementById('toggleOverlay');
    const toggleIcon = document.getElementById('toggleIcon');
    const isMobile = window.innerWidth <= 768;

    if (sidebar.classList.contains('collapsed')) {
        toggleOverlay.style.display = 'flex';
        toggleIcon.className = 'fa-solid fa-bars';
    } else {
        if (isMobile) {
            toggleOverlay.style.display = 'flex';
            toggleIcon.className = 'fa-solid fa-xmark';
        } else {
            toggleOverlay.style.display = 'none';
        }
    }
}

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('collapsed');
    updateToggleVisibility();
}

window.addEventListener('resize', updateToggleVisibility);

window.addEventListener('load', () => {
    if (window.innerWidth <= 768) {
        document.getElementById('sidebar').classList.add('collapsed');
    }
    updateToggleVisibility();
});

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
    document.querySelectorAll('.btn').forEach(b => b.classList.remove('active'));
    document.getElementById('btn-' + newMode)?.classList.add('active');
}

function finalizeText() {
    if (!activeText) return;
    ctx.save();
    ctx.font = `bold ${activeText.size}px 'Plus Jakarta Sans', sans-serif`;
    ctx.textBaseline = "middle"; 
    ctx.textAlign = "center";
    ctx.strokeStyle = "white"; 
    ctx.lineWidth = activeText.size * 0.1;
    ctx.strokeText(activeText.content, activeText.x, activeText.y);
    ctx.fillStyle = activeText.color || 'black'; 
    ctx.fillText(activeText.content, activeText.x, activeText.y);
    ctx.restore();
    activeText = null; 
    textInput.value = ""; 
    saveState();
}

function selectItem(el) {
    if(activeText) finalizeText();
    document.querySelectorAll('.stamped-item').forEach(i => i.classList.remove('selected'));
    el.classList.add('selected');
    selectedItemSrc = el.src;
    ghost.src = el.src;
    stampRotation = 0; 
    setMode('stamp');
    const sidebar = document.getElementById('sidebar');
    if (!sidebar.classList.contains('collapsed')) toggleSidebar(); 
}

function placeStamp(clientX, clientY) {
    const p = getCoords(clientX, clientY);
    const stamp = new Image();
    stamp.src = selectedItemSrc;
    stamp.onload = () => {
        const ratio = stamp.naturalWidth / stamp.naturalHeight;
        let drawW = brushSize;
        let drawH = brushSize / ratio;
        
        ctx.save();
        ctx.translate(p.x, p.y);
        ctx.rotate(stampRotation * Math.PI / 180);
        ctx.drawImage(stamp, -drawW/2, -drawH/2, drawW, drawH);
        ctx.restore();
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
    
    if (!navigator.onLine) {
        Swal.fire('Offline', 'Tidak dapat menyimpan.', 'error');
        return;
    }

    Swal.fire({
        title: 'Simpan Denah?',
        text: 'Perubahan akan diterapkan pada database.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        reverseButtons: true,
        confirmButtonText: 'Ya, Simpan'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({ 
                title: 'Menyimpan...', 
                allowOutsideClick: false, 
                didOpen: () => Swal.showLoading() 
            });

            canvas.toBlob((blob) => {
                const formData = new FormData();
                formData.append('image', blob, 'denah.webp');
                formData.append('id', '<?= $id ?>');
                formData.append('table', '<?= $table ?>');

                fetch('save_denah_edit.php', {
                    method: 'POST',
                    body: formData // Mengirim FormData secara langsung
                })
                .then(res => res.json())
                .then(data => {
                    Swal.close();
                    if(data.success) {
                        Swal.fire('Berhasil!', 'Denah telah diperbarui.', 'success')
                        .then(() => { window.location.href = 'index.php'; });
                    } else {
                        Swal.fire('Gagal', data.message, 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.close();
                    Swal.fire({
                        title: 'Gagal Menyimpan',
                        text: 'Ukuran file terlalu besar atau server menolak permintaan.',
                        icon: 'error',
                        confirmButtonText: 'Coba Lagi'
                    });
                });
            }, 'image/jpeg', 0.85); // Kualitas 0.85 sudah cukup bagus
        }
    });
}

let selectedTextColor = 'black'; 
function setTextColor(color) {
    selectedTextColor = color;
    document.getElementById('color-black').style.borderColor = (color === 'black') ? 'var(--blue)' : 'transparent';
    document.getElementById('color-red').style.borderColor = (color === 'red') ? 'var(--blue)' : 'transparent';
    if (activeText) { activeText.color = color; redrawCanvas(); }
}

window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        if (mode === 'text' && activeText) cancelText();
        else { setMode('move'); selectedItemSrc = null; }
    }
    if (e.key.toLowerCase() === 'r' && mode === 'stamp') {
        stampRotation = (stampRotation + 45) % 360;
    }
    if (e.key === 'Enter') {
        if (document.activeElement === textInput) confirmText();
        else saveImage();
    }
});

function checkNetworkStatus() {
    if (!navigator.onLine) {
        Swal.fire({
            title: 'Koneksi Terputus!',
            text: 'Pastikan Anda terhubung ke internet untuk menyimpan perubahan.',
            icon: 'warning',
            showConfirmButton: true,
            confirmButtonText: 'Refresh Halaman',
            allowOutsideClick: false,
            confirmButtonColor: '#2563eb'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.reload();
            }
        });
    }
}

window.addEventListener('offline', checkNetworkStatus);
window.addEventListener('online', () => {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'Koneksi kembali terhubung',
        showConfirmButton: false,
        timer: 3000
    });
});
    
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
                    window.location.href = 'verifikasi.php'; 
                });
            }
        })
        .catch(error => console.error('Gagal memvalidasi status sesi:', error));
}

const intervalCekSesiEditor = setInterval(periksaSesiEditor, 5000);
</script>
</body>
</html>