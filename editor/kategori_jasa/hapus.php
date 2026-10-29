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

if (!$id || $id < 1) {
    header('Location: tambah.php?pesan=gagal');
    exit;
}

try {
    $query = $pdo->prepare(
        "DELETE FROM kategori_jasa
         WHERE id_kategori_jasa = :id"
    );

    $query->execute([
        'id' => $id
    ]);

    if ($query->rowCount() === 0) {
        header('Location: tambah.php?pesan=gagal');
        exit;
    }

    header('Location: tambah.php?pesan=dihapus');
    exit;
} catch (PDOException $e) {
    header('Location: tambah.php?pesan=gagal');
    exit;
}