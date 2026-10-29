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

$namaKategori = trim($_POST['nama_kategori'] ?? '');

if (
    $namaKategori === '' ||
    mb_strlen($namaKategori) > 100
) {
    header('Location: tambah.php?pesan=gagal');
    exit;
}

try {
    $query = $pdo->prepare(
        "INSERT INTO kategori_jasa (nama_kategori)
         VALUES (:nama_kategori)"
    );

    $query->execute([
        'nama_kategori' => $namaKategori
    ]);

    header('Location: tambah.php?pesan=berhasil');
    exit;
} catch (PDOException $e) {
    header('Location: tambah.php?pesan=gagal');
    exit;
}