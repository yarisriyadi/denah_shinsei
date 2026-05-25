<?php
session_start();
include 'koneksi.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'invalid']);
    exit;
}

$user_id = $_SESSION['user_id'];
$current_session = session_id();

$query = "SELECT session_token FROM data_wajah WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row && $row['session_token'] !== $current_session) {    

$_SESSION = array();
    
if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
    
    echo json_encode(['status' => 'conflict']);
} else {
    echo json_encode(['status' => 'valid']);
}