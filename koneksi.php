<?php
$host = "localhost"; 
$user = "root";      
$pass = "";          
$db   = "denah-sdi"; 

$conn = @mysqli_connect($host, $user, $pass, $db);

$koneksi = $conn;

if (!$conn) {
    if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error', 
            'message' => 'Koneksi database hosting gagal: ' . mysqli_connect_error()
        ]);
        exit;
    } else {
        // Jika dipanggil halaman biasa
        die("Koneksi gagal: " . mysqli_connect_error());
    }
}

// Set charset agar karakter khusus terbaca dengan benar
mysqli_set_charset($conn, "utf8");
mysqli_query($conn, "SET time_zone = '+07:00'");

?>