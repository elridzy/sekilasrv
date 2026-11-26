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

// mengambil dan membersihkan nama kategori
$namaKategori = trim($_POST['nama_kategori'] ?? '');

// memvalidasi id dan nama kategori
if (
    !$id ||
    $id < 1 ||
    $namaKategori === '' ||
    mb_strlen($namaKategori) > 100
) {
    header('Location: dashboard.php');
    exit;
}

try {
    // memperbarui nama kategori di database
    $query = $pdo->prepare(
        "UPDATE kategori_jasa
         SET nama_kategori = :nama_kategori
         WHERE id_kategori_jasa = :id"
    );

    $query->execute([
        'nama_kategori' => $namaKategori,
        'id' => $id
    ]);

    // memeriksa apakah kategori masih tersedia
    if ($query->rowCount() === 0) {
        $cek = $pdo->prepare(
            "SELECT id_kategori_jasa
             FROM kategori_jasa
             WHERE id_kategori_jasa = :id"
        );

        $cek->execute([
            'id' => $id
        ]);

        // kembali jika kategori tidak ditemukan
        if (!$cek->fetch()) {
            header('Location: dashboard.php');
            exit;
        }
    }

    // kembali ke halaman pengelolaan kategori
    header('Location: dashboard.php');
    exit;

} catch (PDOException $e) {
    // kembali ke halaman pengelolaan jika terjadi kesalahan
    header('Location: dashboard.php');
    exit;
}