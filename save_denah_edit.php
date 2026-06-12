<?php
session_start();
include 'koneksi.php';

header('Content-Type: application/json');

$file = $_FILES['image'] ?? null;
$id = $_POST['id'] ?? '';
$table = $_POST['table'] ?? '';

if ($file && $id && $table) {
    $allowed_tables = ['denah_lantai_1', 'denah_lantai_2'];
    if (!in_array($table, $allowed_tables)) {
        echo json_encode(['success' => false, 'message' => 'Tabel tidak valid!']);
        exit;
    }

    $id = mysqli_real_escape_string($conn, $id);
    $q = mysqli_query($conn, "SELECT file_gambar FROM $table WHERE id = '$id'");
    
    if ($res = mysqli_fetch_assoc($q)) {
        $fileName = $res['file_gambar'];
        $targetPath = "uploads/" . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            
            list($width, $height) = getimagesize($targetPath);
            mysqli_query($conn, "UPDATE $table SET lebar_px = '$width', tinggi_px = '$height' WHERE id = '$id'");

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menulis file ke server. Periksa izin folder uploads.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan di database.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap.']);
}
?>