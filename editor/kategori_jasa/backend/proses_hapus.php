<?php
// memulai session editor
session_start();

// memeriksa status login editor
if (!isset($_SESSION['editor']) || !isset($_SESSION['id_editor'])) {
    header('Location: ../../auth/backend/login.php');
    exit;
}

// menghubungkan database
require_once '../../../database/koneksi.php';

// memastikan proses hanya dilakukan melalui method post
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

// mengambil id kategori dari form
$id = filter_input(
    INPUT_POST,
    'id_kategori_jasa',
    FILTER_VALIDATE_INT
);

// memvalidasi id kategori
if (!$id || $id < 1) {
    header('Location: dashboard.php');
    exit;
}

try {
    // menghapus kategori dari database
    $query = $pdo->prepare(
        "DELETE FROM kategori_jasa
         WHERE id_kategori_jasa = :id"
    );

    $query->execute([
        'id' => $id
    ]);

    // kembali ke halaman pengelolaan kategori
    header('Location: dashboard.php');
    exit;

} catch (PDOException $e) {
    // mencatat kesalahan ke log server
    error_log($e->getMessage());

    // kembali ke halaman pengelolaan kategori
    header('Location: dashboard.php');
    exit;
}
?>