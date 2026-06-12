<?php
header('Content-Type: application/json');

include 'koneksi.php';

$data = json_decode(file_get_contents('php://input'), true);
$nama = isset($data['nama']) ? trim($data['nama']) : '';

if (empty($nama)) {
    echo json_encode(['status' => 'error', 'message' => 'Nama tidak boleh kosong.']);
    exit;
}

$stmt = $conn->prepare("SELECT nama, descriptor, foto FROM data_wajah WHERE nama = ?");
$stmt->bind_param("s", $nama);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    if (!empty($user['descriptor']) || !empty($user['foto'])) {
        echo json_encode([
            'status' => 'already_registered',
            'nama' => $user['nama'],
            'message' => 'Wajah sudah terdaftar pada username ' . $user['nama']
        ]);
        exit;
    }

    echo json_encode([
        'status' => 'success',
        'nama' => $user['nama']
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Nama yang Anda masukkan belum terdaftar di sistem.'
    ]);
}

$stmt->close();
$conn->close();
?>