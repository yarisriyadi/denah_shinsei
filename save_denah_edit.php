<?php
session_start();
include 'koneksi.php';

$data = $_POST['image'] ?? '';
$id = $_POST['id'] ?? ''; // Kirim ID saja
$table = $_POST['table'] ?? ''; // Kirim Tabel saja

if ($data && $id && $table) {
    // 1. Ambil path asli dari DB untuk keamanan
    $q = mysqli_query($conn, "SELECT file_gambar FROM $table WHERE id = '$id'");
    $res = mysqli_fetch_assoc($q);
    $targetPath = "uploads/" . $res['file_gambar'];

    // 2. Proses Base64
    $imgData = str_replace('data:image/jpeg;base64,', '', $data);
    $imgData = str_replace(' ', '+', $imgData);
    $fileData = base64_decode($imgData);

    // 3. Simpan (Overwrite)
    if (file_put_contents($targetPath, $fileData)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menulis file.']);
    }
}