<?php
include 'koneksi.php';

$response = ['success' => false, 'message' => 'Invalid request'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $table = mysqli_real_escape_string($conn, $_POST['table']);
    $newName = mysqli_real_escape_string($conn, $_POST['new_name']);
    
    if ($table === 'denah_lantai_1' || $table === 'denah_lantai_2') {
        $check = mysqli_query($conn, "SELECT parent_id FROM $table WHERE id = '$id'");
        $row = mysqli_fetch_assoc($check);
        
        if ($row['parent_id'] == 0) {
            $query = "UPDATE $table SET nama_lantai = '$newName' WHERE id = '$id'";
        } else {
            $query = "UPDATE $table SET keterangan = '$newName' WHERE id = '$id'";
        }

        if (mysqli_query($conn, $query)) {
            $response = ['success' => true];
        } else {
            $response = ['success' => false, 'message' => mysqli_error($conn)];
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response); 