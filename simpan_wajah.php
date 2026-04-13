<?php
header('Content-Type: application/json');
include 'koneksi.php';

$data = json_decode(file_get_contents('php://input'), true);
$nama = mysqli_real_escape_string($conn, $data['nama']);
$descriptor = $data['descriptor'];
$descriptorJson = json_encode($descriptor);

$result = mysqli_query($conn, "SELECT descriptor FROM data_wajah");
$threshold = 0.40;

while ($row = mysqli_fetch_assoc($result)) {
    $dbDesc = json_decode($row['descriptor']);
    $sum = 0;
    for ($i = 0; $i < count($descriptor); $i++) {
        $diff = $descriptor[$i] - $dbDesc[$i];
        $sum += $diff * $diff;
    }
    if (sqrt($sum) < $threshold) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Wajah sudah terdaftar!']);
        exit;
    }
}

$query = "INSERT INTO data_wajah (nama, descriptor) VALUES ('$nama', '$descriptorJson')";

if (mysqli_query($conn, $query)) {
    echo json_encode(['status' => 'success']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
}
?>