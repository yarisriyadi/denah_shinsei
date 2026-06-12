<?php
error_reporting(0);
ini_set('display_errors', 0);

include 'koneksi.php';

header('Content-Type: application/json');

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Koneksi database terputus.']);
    exit;
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (!$data || !isset($data['nama']) || !isset($data['descriptor']) || !isset($data['foto'])) {
    echo json_encode(['status' => 'error', 'message' => 'Format payload data cacat atau tidak lengkap.']);
    exit;
}

$nama = $conn->real_escape_string($data['nama']);
$descriptorString = json_encode($data['descriptor']); 
$base64Image = $data['foto'];

if (strpos($base64Image, ',') !== false) {
    @list($type, $base64Image) = explode(';', $base64Image);
    @list(, $base64Image)      = explode(',', $base64Image);
}

$decodedImage = base64_decode($base64Image);
if (!$decodedImage) {
    echo json_encode(['status' => 'error', 'message' => 'Gagal melakukan dekode enkripsi gambar biometrik.']);
    exit;
}

$targetDir = "assets/foto/";
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}

$fileName = $nama . "_" . time() . ".jpg";
$targetFilePath = $targetDir . $fileName;

if (file_put_contents($targetFilePath, $decodedImage)) {
    
    $query = "UPDATE data_wajah SET descriptor = '$descriptorString', foto = '$fileName' WHERE nama = '$nama'";
    
    if ($conn->query($query)) {
        echo json_encode(['status' => 'success', 'message' => 'Biometrik wajah berhasil dikonfigurasi.']);
    } else {
        if (file_exists($targetFilePath)) {
            unlink($targetFilePath); // Bersihkan sisa file sampah jika query sql crash
        }
        echo json_encode(['status' => 'error', 'message' => 'Gagal mendaftarkan ke tabel database: ' . $conn->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal menulis file gambar. Cek izin tulis folder assets/foto/.']);
}

$conn->close();
?>