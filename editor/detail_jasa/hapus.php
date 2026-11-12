<?php
session_start();

if (!isset($_SESSION['editor']) || !isset($_SESSION['id_editor'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../../database/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Akses tidak diizinkan.');
}

if (
    !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
    !is_string($_POST['csrf_token']) ||
    !is_string($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    exit('Permintaan tidak valid.');
}

$id_editor = $_SESSION['id_editor'];

$id_detail_jasa = (int) ($_POST['id_detail_jasa'] ?? 0);
$id_kategori_jasa = (int) ($_POST['id_kategori_jasa'] ?? 0);

if ($id_detail_jasa <= 0 || $id_kategori_jasa <= 0) {
    exit('Data tidak valid.');
}

$stmt = $pdo->prepare("
    SELECT id_detail_jasa, gambar
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

if (!$jasa) {
    exit('Data jasa tidak ditemukan.');
}

$gambar = $jasa['gambar'];

try {

    $pdo->beginTransaction();

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

    $pdo->commit();

    if (!empty($gambar)) {
        $lokasi_file = __DIR__ . '/../../' . $gambar;

        if (is_file($lokasi_file)) {
            unlink($lokasi_file);
        }
    }

    header(
        'Location: portofolio.php?id_kategori_jasa=' .
        $id_kategori_jasa . '&pesan=dihapus'
    );

    exit;

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($e->getMessage());

    die('Gagal menghapus data jasa.');
}