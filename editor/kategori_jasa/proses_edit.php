<?php
session_start();

if (!isset($_SESSION['editor'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../../database/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: tambah.php');
    exit;
}

$id = filter_input(
    INPUT_POST,
    'id_kategori_jasa',
    FILTER_VALIDATE_INT
);

$namaKategori = trim($_POST['nama_kategori'] ?? '');

if (
    !$id ||
    $id < 1 ||
    $namaKategori === '' ||
    mb_strlen($namaKategori) > 100
) {
    header('Location: tambah.php?pesan=gagal');
    exit;
}

try {
    $query = $pdo->prepare(
        "UPDATE kategori_jasa
         SET nama_kategori = :nama_kategori
         WHERE id_kategori_jasa = :id"
    );

    $query->execute([
        'nama_kategori' => $namaKategori,
        'id' => $id
    ]);

    if ($query->rowCount() === 0) {
        $cek = $pdo->prepare(
            "SELECT id_kategori_jasa
             FROM kategori_jasa
             WHERE id_kategori_jasa = :id"
        );

        $cek->execute([
            'id' => $id
        ]);

        if (!$cek->fetch()) {
            header('Location: tambah.php?pesan=gagal');
            exit;
        }
    }

    header('Location: tambah.php?pesan=diedit');
    exit;
} catch (PDOException $e) {
    header('Location: tambah.php?pesan=gagal');
    exit;
}