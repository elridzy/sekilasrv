<?php

// memulai session
session_start();

// memeriksa session editor
if (!isset($_SESSION['editor']) || !isset($_SESSION['id_editor'])) {
    header('Location: ../../auth/backend/login.php');
    exit;
}

// menghubungkan halaman dengan database
require_once '../../../database/koneksi.php';

// memeriksa metode request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Akses tidak diizinkan.');
}

// memvalidasi token csrf
if (
    !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
    !is_string($_POST['csrf_token']) ||
    !is_string($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    exit('Permintaan tidak valid.');
}

// mengambil identitas editor
$id_editor = $_SESSION['id_editor'];

// mengambil id jasa dan kategori
$id_detail_jasa = (int) ($_POST['id_detail_jasa'] ?? 0);
$id_kategori_jasa = (int) ($_POST['id_kategori_jasa'] ?? 0);

// memeriksa validitas id
if ($id_detail_jasa <= 0 || $id_kategori_jasa <= 0) {
    exit('Data tidak valid.');
}

// mengambil data jasa berdasarkan kepemilikan editor
$stmt = $pdo->prepare("
    SELECT id_detail_jasa, gambar_portofolio
    FROM detail_jasa
    WHERE id_detail_jasa = ?
    AND id_editor = ?
    AND id_kategori_jasa = ?
");

$stmt->execute([
    $id_detail_jasa,
    $id_editor,
    $id_kategori_jasa
]);

$jasa = $stmt->fetch(PDO::FETCH_ASSOC);

// memeriksa keberadaan data jasa
if (!$jasa) {
    exit('Data jasa tidak ditemukan.');
}

// menyimpan path gambar sebelum data dihapus
$gambar = $jasa['gambar_portofolio'];

try {

    // memulai transaksi database
    $pdo->beginTransaction();

    // menghapus data jasa milik editor
    $stmt = $pdo->prepare("
        DELETE FROM detail_jasa
        WHERE id_detail_jasa = ?
        AND id_editor = ?
        AND id_kategori_jasa = ?
    ");

    $stmt->execute([
        $id_detail_jasa,
        $id_editor,
        $id_kategori_jasa
    ]);

    // menyimpan perubahan database
    $pdo->commit();

    // menghapus file gambar dari folder penyimpanan
    if (!empty($gambar)) {
        $lokasi_file = __DIR__ . '/../../' . $gambar;

        if (is_file($lokasi_file)) {
            unlink($lokasi_file);
        }
    }

    // kembali ke dashboard detail jasa
    header(
        'Location: dashboard.php?id_kategori_jasa=' . $id_kategori_jasa
    );

    exit;

} catch (Throwable $e) {

    // membatalkan transaksi jika terjadi kesalahan
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // mencatat kesalahan ke log server
    error_log($e->getMessage());

    // menampilkan pesan kegagalan
    die('Gagal menghapus data jasa.');
}

?>