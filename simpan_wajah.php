<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
include 'koneksi.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['foto']) || !isset($data['nama']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Payload data tidak lengkap.']);
    exit;
}

$nama = mysqli_real_escape_string($conn, $data['nama']);

$passwordRaw = $data['password'];
$passwordHash = password_hash($passwordRaw, PASSWORD_BCRYPT);

$descriptor = $data['descriptor'];
$descriptorJson = json_encode($descriptor);

$fotoBase64 = $data['foto']; 
$folderPath = "assets/foto/";

if (!file_exists($folderPath)) {
    mkdir($folderPath, 0777, true);
}

$image_parts = explode(";base64,", $fotoBase64);
if (count($image_parts) < 2) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Format Base64 gambar tidak valid.']);
    exit;
}

$image_type_aux = explode("image/", $image_parts[0]);
$image_type = isset($image_type_aux[1]) ? $image_type_aux[1] : 'jpg';
$image_base64 = base64_decode($image_parts[1]);

$safeNama = preg_replace('/[^A-Za-z0-9\-]/', '_', $nama);
$fileName = $safeNama . "_" . time() . ".jpg";
$fileFullDir = $folderPath . $fileName;

$fileSaved = file_put_contents($fileFullDir, $image_base64);

$result = mysqli_query($conn, "SELECT descriptor FROM data_wajah");
$threshold = 0.40;

while ($row = mysqli_fetch_assoc($result)) {
    $dbDesc = json_decode($row['descriptor']);
    if (!$dbDesc) continue; 
    
    $sum = 0;
    for ($i = 0; $i < count($descriptor); $i++) {
        $diff = $descriptor[$i] - $dbDesc[$i];
        $sum += $diff * $diff;
    }
    if (sqrt($sum) < $threshold) {
        if($fileSaved && file_exists($fileFullDir)) unlink($fileFullDir);
        
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Wajah sudah terdaftar!']);
        exit;
    }
}

$query = "INSERT INTO data_wajah (nama, password, descriptor, foto) VALUES ('$nama', '$passwordHash', '$descriptorJson', '$fileName')";

if (ob_get_length()) ob_clean();
if (mysqli_query($conn, $query)) {
    echo json_encode(['status' => 'success']);
} else {
    if($fileSaved && file_exists($fileFullDir)) unlink($fileFullDir);
    
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
}
exit;
?>