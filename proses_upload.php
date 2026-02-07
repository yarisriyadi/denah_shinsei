<?php
include 'koneksi.php';

if (isset($_POST['submit'])) {
    $table = $_POST['target_table'];
    $input_nama = mysqli_real_escape_string($conn, $_POST['nama_lantai']);
    $parent_id = intval($_POST['parent_id']);
    
    if ($parent_id == 0) {
        $nama_lantai = $input_nama;
        $keterangan = "NULL"; 
    } else {
        $nama_lantai = ""; 
        $keterangan = "'$input_nama'";
    }

    $filename = $_FILES['gambar']['name'];
    $target_dir = "uploads/";
    $unique_name = time() . "_" . str_replace(' ', '_', $filename);

    if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_dir . $unique_name)) {
        list($width, $height) = getimagesize($target_dir . $unique_name);

        $sql = "INSERT INTO $table (nama_lantai, file_gambar, lebar_px, tinggi_px, parent_id, keterangan) 
                VALUES ('$nama_lantai', '$unique_name', '$width', '$height', '$parent_id', $keterangan)";

        if (mysqli_query($conn, $sql)) {
            echo "<script>alert('Berhasil!'); window.location='index.php';</script>";
        }
    }
}
?>