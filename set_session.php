<?php
session_start();
include 'koneksi.php';

// 1. Cek apakah request datang dari domain Anda sendiri (CORS/Origin)
// 2. Gunakan pengecekan POST yang lebih ketat
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (isset($data['nama'])) {
    // Tambahkan pengaman: Pastikan IP user tidak melakukan request berulang kali dalam waktu singkat (Rate Limiting)
    
    $nama_terdeteksi = $data['nama'];

    $stmt = $conn->prepare("SELECT id, nama FROM data_wajah WHERE nama = ?");
    $stmt->bind_param("s", $nama_terdeteksi);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Berikan ID unik untuk session ini agar tidak mudah dibajak
        session_regenerate_id(true); 
        
        $_SESSION['terverifikasi'] = true;
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['nama_user'] = $row['nama'];
        $_SESSION['last_activity'] = time(); // Simpan waktu login

        echo json_encode(['status' => 'success']);
    }
}