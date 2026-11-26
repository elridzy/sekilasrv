<?php
// memulai session
session_start();

// menghapus seluruh data session
session_unset();
session_destroy();

// mengarahkan kembali ke halaman beranda
header('Location: ../../../beranda/backend/beranda.php');
exit;
?>