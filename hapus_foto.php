<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['terverifikasi']) || $_SESSION['terverifikasi'] !== true || !isset($_SESSION['user_id'])) {
    header("Location: verifikasi.php");
    exit();
}

$db = (isset($conn)) ? $conn : (isset($koneksi) ? $koneksi : null);
$id_user = $_SESSION['user_id'];

$query = "SELECT foto FROM data_wajah WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("s", $id_user);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row && !empty($row['foto'])) {
    $namaFile = $row['foto'];
    $pathFile = 'uploads/' . $namaFile;

    if (file_exists($pathFile)) {
        unlink($pathFile);
    }

    $update = "UPDATE data_wajah SET foto = NULL WHERE id = ?";
    $stmt_upd = $db->prepare($update);
    $stmt_upd->bind_param("s", $id_user);
    
    if ($stmt_upd->execute()) {
        header("Location: profile.php?delete=success");
        exit();
    }
}

header("Location: profile.php");
exit();
?>