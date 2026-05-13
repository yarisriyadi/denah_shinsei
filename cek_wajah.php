<?php
header('Content-Type: application/json');
include 'koneksi.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['descriptor']) || !is_array($data['descriptor'])) {
    echo json_encode(['error' => 'Data biometrik tidak valid']);
    exit;
}

$newDescriptor = $data['descriptor'];

$query = "SELECT nama, descriptor FROM data_wajah";
$result = mysqli_query($conn, $query);
$found = false;
$foundNama = "";
$threshold = 0.50; 

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $dbDescriptor = json_decode($row['descriptor'], true);
        
        if (!$dbDescriptor || count($dbDescriptor) !== count($newDescriptor)) continue;

        $sum = 0;
        for ($i = 0; $i < count($newDescriptor); $i++) {
            $diff = (float)$newDescriptor[$i] - (float)$dbDescriptor[$i];
            $sum += $diff * $diff;
        }
        $distance = sqrt($sum);

        if ($distance < $threshold) {
            $found = true;
            $foundNama = $row['nama'];
            break;
        }
    }
}

echo json_encode([
    'exists' => $found, 
    'nama' => $foundNama,
    'status' => 'success'
]);
?>