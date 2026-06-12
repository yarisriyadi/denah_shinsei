<?php
error_reporting(0);
ini_set('display_errors', 0);

date_default_timezone_set('Asia/Jakarta');

header('Content-Type: application/json');
include 'koneksi.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !array_key_exists('nama', $data) || !array_key_exists('password', $data) || !array_key_exists('descriptor', $data)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Payload data tidak lengkap atau rusak saat diunggah.']);
    exit;
}

$nama = mysqli_real_escape_string($conn, trim($data['nama']));
$passwordRaw = $data['password'];
$passwordHash = password_hash($passwordRaw, PASSWORD_BCRYPT);
$descriptor = $data['descriptor'];
$fotoBase64 = $data['foto'] ?? ''; 


$cekNamaQuery = mysqli_query($conn, "SELECT nama FROM data_wajah WHERE nama = '$nama' LIMIT 1");
if (mysqli_num_rows($cekNamaQuery) > 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Nama sudah terdaftar. Silakan gunakan nama lain!']);
    exit;
}

$isBiometric = is_array($descriptor) && count($descriptor) > 0;

$descriptorJson = null;
$fileName = null;

if ($isBiometric) {
    
    $descriptorJson = json_encode($descriptor);
    $folderPath = __DIR__ . "/assets/foto/";

    if (!file_exists($folderPath)) {
        mkdir($folderPath, 0777, true);
    }

    $result = mysqli_query($conn, "SELECT nama, descriptor FROM data_wajah WHERE descriptor IS NOT NULL AND descriptor != ''");
    $threshold = 0.40;

    while ($row = mysqli_fetch_assoc($result)) {
        $dbDesc = json_decode($row['descriptor']);
        if (!is_array($dbDesc) || count($dbDesc) !== count($descriptor)) continue; 
        
        $sum = 0;
        for ($i = 0; $i < count($descriptor); $i++) {
            $diff = $descriptor[$i] - $dbDesc[$i];
            $sum += $diff * $diff;
        }
        
        if (sqrt($sum) < $threshold) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Wajah ini sudah terdaftar atas nama: ' . $row['nama']]);
            exit;
        }
    }

    $image_parts = explode(";base64,", $fotoBase64);
    if (count($image_parts) < 2) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Format Base64 gambar tidak valid.']);
        exit;
    }

    $image_base64 = base64_decode($image_parts[1]);
    if (!$image_base64) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Dekode gambar Base64 gagal. File rusak.']);
        exit;
    }

    $safeNama = preg_replace('/[^A-Za-z0-9\-]/', '_', $nama);
    $fileName = $safeNama . "_" . time() . ".jpg";
    $fileFullDir = $folderPath . $fileName;

    $fileSaved = file_put_contents($fileFullDir, $image_base64);

    if ($fileSaved === false || filesize($fileFullDir) === 0) {
        if (file_exists($fileFullDir)) unlink($fileFullDir);
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Gagal menulis file ke direktori server. Periksa hak akses folder assets/foto/.']);
        exit;
    }
} else {

    $descriptorJson = null; 
    $fileName = null;
}

$dbDescriptor = is_null($descriptorJson) ? "NULL" : "'$descriptorJson'";
$dbFoto = is_null($fileName) ? "NULL" : "'$fileName'";

$query = "INSERT INTO data_wajah (nama, password, descriptor, foto) VALUES ('$nama', '$passwordHash', $dbDescriptor, $dbFoto)";

if (ob_get_length()) ob_clean();

if (mysqli_query($conn, $query)) {
    echo json_encode(['status' => 'success']);
} else {
    if ($isBiometric && isset($fileFullDir) && file_exists($fileFullDir)) {
        unlink($fileFullDir);
    }
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan ke database: ' . mysqli_error($conn)]);
}
exit;
?>