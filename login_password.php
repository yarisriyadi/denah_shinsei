<?php
session_start();
include 'koneksi.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

$username = isset($input['username']) ? trim($input['username']) : '';
$password = isset($input['password']) ? $input['password'] : '';

if (empty($username) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'Semua kolom wajib diisi.']);
    exit;
}

$query = "SELECT nama, password, role FROM data_wajah WHERE nama = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    if (password_verify($password, $user['password']) || $password === $user['password']) {
        
        $fixedRole = trim(strtolower($user['role']));

        $_SESSION['nama'] = $user['nama'];
        $_SESSION['role'] = $fixedRole;
        
        echo json_encode([
            'status' => 'success',
            'nama' => $user['nama'],
            'role' => $fixedRole
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Kata sandi salah.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Akun dengan nama tersebut tidak ditemukan.']);
}
exit;