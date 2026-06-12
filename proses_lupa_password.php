<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
set_time_limit(120);

header('Content-Type: application/json');

if (file_exists('config_maintenance.php')) {
    require_once 'config_maintenance.php';
}

if (isset($maintenance_mode) && $maintenance_mode === true) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        echo json_encode(['status' => 'error', 'message' => 'Sistem sedang dalam pemeliharaan (Maintenance Mode).']);
        exit();
    }
}

include "koneksi.php";

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$input = json_decode(file_get_contents('php://input'), true);

$action   = isset($input['action']) ? $input['action'] : (isset($_POST['action']) ? $_POST['action'] : '');
$nama     = isset($input['nama']) ? trim($input['nama']) : (isset($_POST['nama']) ? trim($_POST['nama']) : '');
$email    = isset($input['email']) ? trim($input['email']) : (isset($_POST['email']) ? trim($_POST['email']) : '');
$otp      = isset($input['otp']) ? trim($input['otp']) : (isset($_POST['otp']) ? trim($_POST['otp']) : '');
$password = isset($input['password']) ? $input['password'] : (isset($_POST['password']) ? $_POST['password'] : '');


if ($action === 'request_otp' && !empty($nama) && !empty($email)) {
    
    $email_tujuan = mysqli_real_escape_string($conn, $email);
    $nama_user    = mysqli_real_escape_string($conn, $nama);
    
    $cek_user = mysqli_query($conn, "SELECT * FROM data_wajah WHERE nama = '$nama_user'");

    if (mysqli_num_rows($cek_user) > 0) {
        $data_user = mysqli_fetch_assoc($cek_user);
        
        $otp_code = rand(100000, 999999);
        date_default_timezone_set('Asia/Jakarta');
        $expiry = date("Y-m-d H:i:s", strtotime("+2 minutes")); 

        $update = mysqli_query($conn, "UPDATE data_wajah SET reset_otp = '$otp_code', otp_expiry = '$expiry' WHERE nama = '$nama_user'");

        if ($update) {
            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'shinseidenshi1@gmail.com'; 
                $mail->Password   = 'pagmibejobiwgqls'; 
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; 
                $mail->Port       = 465; 
                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );

                $mail->setFrom('shinseidenshi1@gmail.com', 'SDI SYSTEM');
                $mail->addAddress($email_tujuan); 
                
                $mail->isHTML(true);
$mail->Subject = 'S-SDI SYSTEM - OTP Verification Code';
$mail->Body = "
<!DOCTYPE html>
<html>
<head>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <style>
        body, table, td { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif !important; }
        
        /* --- STYLING UNTUK DETAILS/SUMMARY (ANTI-BLOKIR GMAIL) --- */
        details { display: block; width: 100%; text-align: center; margin: 0 auto; }
        
        /* Menghilangkan segitiga bawaan browser (Chrome, Safari, Firefox) */
        summary { display: block; list-style: none; outline: none; cursor: pointer; }
        summary::-webkit-details-marker { display: none; }
        
        .dots-btn { color: #bdc1c6; font-weight: bold; font-size: 22px; letter-spacing: 2px; display: inline-block; padding: 0 20px; -webkit-user-select: none; user-select: none; line-height: 1; }
        .dots-btn:hover { color: #0066cc; }
        
        /* Ketika diklik dan terbuka, sembunyikan tanda titik tiga (...) */
        details[open] summary { display: none !important; }

        @media only screen and (max-width: 480px) {
            .email-container { padding: 24px 16px !important; }
            .otp-code-text { font-size: 38px !important; letter-spacing: 8px !important; }
        }
    </style>
</head>
<body style='margin: 0; padding: 0; background-color: #f9fafb; min-height: 100%; width: 100%; -webkit-font-smoothing: antialiased;'>
    <table width='100%' border='0' cellspacing='0' cellpadding='0' style='background-color: #f9fafb; table-layout: fixed;'>
        <tr>
            <td align='center' style='padding: 50px 10px;'>
                
                <table class='email-container' width='100%' border='0' cellspacing='0' cellpadding='0' style='max-width: 450px; background-color: #ffffff; border-radius: 16px; border: 1px solid #eaeaea; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03); overflow: hidden; padding: 40px;'>
                    
                    <tr>
                        <td align='left' style='padding-bottom: 32px;'>
                            <table border='0' cellspacing='0' cellpadding='0'>
                                <tr>
                                    <td align='center' style='background-color: #f0f7ff; width: 44px; height: 44px; border-radius: 10px; color: #0066cc; font-size: 18px; font-weight: bold;'>
                                        <span style='line-height: 44px;'>🔑</span>
                                    </td>
                                    <td style='padding-left: 14px; vertical-align: middle;'>
                                        <div style='color: #111111; font-size: 16px; font-weight: 700; letter-spacing: -0.3px; margin: 0;'>S-SDI OTP</div>
                                        <div style='color: #888888; font-size: 11px; font-weight: 500; letter-spacing: 0.5px; text-transform: uppercase; margin-top: 2px;'>Security Service</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style='border-top: 1px solid #f0f0f0; padding-top: 28px;'>
                            <p style='font-size: 15px; color: #1a1a1a; font-weight: 600; margin: 0 0 10px 0;'>Halo " . htmlspecialchars($nama_user) . ",</p>
                            <p style='font-size: 14px; color: #555555; line-height: 1.6; margin: 0;'>Kami menerima permintaan pengaturan ulang kata sandi untuk akun Anda. Masukkan kode verifikasi berikut untuk melanjutkan:</p>
                        </td>
                    </tr>

                    <tr>
                        <td align='center' style='padding: 28px 0;'>
                            <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                                <tr>
                                    <td align='center' style='background-color: #fafafa; border: 1px solid #e5e5e5; padding: 22px; border-radius: 12px;'>
                                        <span class='otp-code-text' style='font-size: 42px; font-weight: 700; letter-spacing: 10px; color: #0066cc; display: block; font-family: ui-monospace, SFMono-Regular, SF Pro Text, Menlo, Consolas, monospace !important; padding-left: 10px;'>$otp_code</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td align='center' style='padding-bottom: 20px;'>
                            <div style='height: 1px; width: 80px; background: linear-gradient(90deg, rgba(0,0,0,0) 0%, rgba(0,0,0,0.06) 50%, rgba(0,0,0,0) 100%); box-shadow: 0 1px 2px rgba(0,0,0,0.02);'></div>
                        </td>
                    </tr>

                    <tr>
                        <td align='center' style='padding-bottom: 12px;'>
                            <p style='font-size: 13px; color: #666666; line-height: 1.6; margin: 0; max-width: 380px;'>
                                Kode keamanan ini berlaku selama <strong style='color: #111111; font-weight: 600;'>2 menit</strong>. Demi menjaga keamanan akun Anda, jangan membagikan kode ini kepada siapa pun termasuk pihak atau tim dari S-SDI.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td align='center'>
                            <details>             
                                <div style='border-top: 1px solid #f0f0f0; padding-top: 20px; margin-top: 10px;'>
                                    <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                                        <tr>
                                            <td align='center'>
                                                <table border='0' cellspacing='0' cellpadding='0' style='margin: 0 auto;'>
                                                    <tr>
                                                        <td align='center' style='background-color: #f3f4f6; padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 600; color: #6b7280; letter-spacing: 0.5px; text-transform: uppercase;'>
                                                            AUTOMATED MESSAGE
                                                        </td>
                                                    </tr>
                                                </table>
                                                
                                                <p style='font-size: 11px; color: #bdc1c6; margin: 12px 0 0 0; text-align: center;'>
                                                    &copy; " . date('Y') . " IT Department - PT. Shinsei Denshi Indonesia.
                                                </p>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </details>
                        </td>
                    </tr>

                </table>
                
            </td>
        </tr>
    </table>
</body>
</html>";
                $mail->AltBody = "Halo $nama_user, Kode OTP Anda adalah: $otp_code. Kunci keamanan ini berlaku selama 2 menit.";

                if ($mail->send()) {
                    echo json_encode(['status' => 'success', 'message' => 'Silakan periksa kotak masuk email yang Anda masukkan.']);
                    exit();
                }

            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'message' => 'Gagal berinteraksi dengan SMTP Relay. Error: ' . $mail->ErrorInfo]);
                exit();
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menulis token OTP baru ke basis data.']);
            exit();
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Nama akun (username) tidak terdaftar di dalam sistem kami.']);
        exit();
    }


} elseif ($action === 'verify_otp' && !empty($nama) && !empty($otp) && !empty($password)) {
    $nama_user     = mysqli_real_escape_string($conn, $nama);
    $otp_input     = mysqli_real_escape_string($conn, $otp);
    $password_baru = $password;

    date_default_timezone_set('Asia/Jakarta');
    $waktu_sekarang = date("Y-m-d H:i:s");

    $cek_otp = mysqli_query($conn, "SELECT * FROM data_wajah WHERE nama = '$nama_user'");
    if (mysqli_num_rows($cek_otp) > 0) {
        $data_user = mysqli_fetch_assoc($cek_otp);

        if ($data_user['reset_otp'] === $otp_input) {
            
            if (strtotime($waktu_sekarang) <= strtotime($data_user['otp_expiry'])) {
                $password_secure = password_hash($password_baru, PASSWORD_BCRYPT);
                $update_password = mysqli_query($conn, "UPDATE data_wajah SET password = '$password_secure', reset_otp = NULL, otp_expiry = NULL WHERE nama = '$nama_user'");

                if ($update_password) {
                    echo json_encode(['status' => 'success', 'message' => 'Password berhasil diperbarui berdasarkan nama akun Anda.']);
                    exit();
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Gagal mengubah record kata sandi pada database.']);
                    exit();
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Kode OTP Anda telah kedaluwarsa (Batas 2 menit).']);
                exit();
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Kode OTP yang dimasukkan salah.']);
            exit();
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Sesi identitas validasi nama user tidak ditemukan.']);
        exit();
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Parameter tindakan tidak lengkap atau tidak valid.']);
    exit();
}
?>