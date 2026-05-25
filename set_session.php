<?php
session_start();
include 'koneksi.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (isset($data['nama'])) {
    $nama_terdeteksi = $data['nama'];
    $stmt = $conn->prepare("SELECT id, nama, session_token FROM data_wajah WHERE nama = ?");
    $stmt->bind_param("s", $nama_terdeteksi);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $user_id = $row['id'];
        
        $is_conflict = !empty($row['session_token']); 

        session_regenerate_id(true);
        $new_session_id = session_id();

        mysqli_query($conn, "UPDATE data_wajah SET session_token = '$new_session_id' WHERE id = '$user_id'");

        $app_id = "2154227";
        $key = "3de5179020ffea29784d";
        $secret = "06338ee8ece6e5aee8e3";
        $cluster = "ap1";

        $pusher_data = [
            'name' => 'kick-event',
            'data' => json_encode([
                'new_session_id' => $new_session_id,
                'message' => 'Conflict detected'
            ]),
            'channels' => ['user-channel-' . $user_id]
        ];

        $s_data = json_encode($pusher_data);
        $path = "/apps/$app_id/events";
        $auth_timestamp = time();
        $auth_version = '1.0';
        $body_md5 = md5($s_data);
        $auth_signature = hash_hmac('sha256', "POST\n$path\nauth_key=$key&auth_timestamp=$auth_timestamp&auth_version=$auth_version&body_md5=$body_md5", $secret);
        
        $url = "https://api-$cluster.pusher.com$path?auth_key=$key&auth_timestamp=$auth_timestamp&auth_version=$auth_version&body_md5=$body_md5&auth_signature=$auth_signature";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $s_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_exec($ch);
        curl_close($ch);

        $_SESSION['terverifikasi'] = true;
        $_SESSION['user_id'] = $user_id;
        $_SESSION['nama_user'] = $row['nama'];

        echo json_encode([
            'status' => 'success',
            'conflict' => $is_conflict
        ]);
    }
}