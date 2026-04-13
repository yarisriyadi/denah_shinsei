<?php
include 'koneksi.php';
$res = mysqli_query($conn, "SELECT * FROM data_wajah");
$rows = [];
while($row = mysqli_fetch_assoc($res)) {
    $row['descriptor'] = json_decode($row['descriptor']);
    $rows[] = $row;
}
echo json_encode($rows);
?>