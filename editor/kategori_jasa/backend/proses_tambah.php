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

// mengambil dan membersihkan nama kategori
$namaKategori = trim($_POST['nama_kategori'] ?? '');

// memvalidasi nama kategori
if (
    $namaKategori === '' ||
    mb_strlen($namaKategori) > 100
) {
    header('Location: dashboard.php');
    exit;
}

try {
    // menyimpan kategori baru ke database
    $query = $pdo->prepare(
        "INSERT INTO kategori_jasa (nama_kategori)
         VALUES (:nama_kategori)"
    );

    $query->execute([
        'nama_kategori' => $namaKategori
    ]);

    // kembali ke halaman pengelolaan kategori
    header('Location: dashboard.php');
    exit;

} catch (PDOException $e) {
    // kembali ke halaman pengelolaan jika terjadi kesalahan
    header('Location: dashboard.php');
    exit;
}