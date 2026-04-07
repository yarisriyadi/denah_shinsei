<?php
include 'koneksi.php';

header('Content-Type: application/json');

if (isset($_GET['id']) && isset($_GET['table'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $table = mysqli_real_escape_string($conn, $_GET['table']);

    $query_file = mysqli_query($conn, "SELECT file_gambar, parent_id FROM $table WHERE id = '$id'");
    $data = mysqli_fetch_assoc($query_file);

    if ($data) {
        if ($data['parent_id'] == 0) {
            $query_sub = mysqli_query($conn, "SELECT file_gambar FROM $table WHERE parent_id = '$id'");
            
            while ($sub = mysqli_fetch_assoc($query_sub)) {
                $sub_file_path = "uploads/" . $sub['file_gambar'];
                if (!empty($sub['file_gambar']) && file_exists($sub_file_path)) {
                    unlink($sub_file_path); 
                }
            }
            mysqli_query($conn, "DELETE FROM $table WHERE parent_id = '$id'");
        }

        $main_file_path = "uploads/" . $data['file_gambar'];
        if (!empty($data['file_gambar']) && file_exists($main_file_path)) {
            unlink($main_file_path);
        }
        $delete = mysqli_query($conn, "DELETE FROM $table WHERE id = '$id'");

        if ($delete) {
            echo json_encode([
                'success' => true,
                'message' => 'Data dan file fisik berhasil dihapus sepenuhnya!'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Gagal menghapus data dari database: ' . mysqli_error($conn)
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Data tidak ditemukan di database.'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Parameter ID atau Table tidak valid.'
    ]);
}
?>