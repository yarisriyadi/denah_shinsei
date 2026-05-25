<?php
session_start();
session_unset();
session_destroy();

header("Location: verifikasi.php?status=logout");
exit();
?>