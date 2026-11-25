<?php
session_start();

if (!isset($_SESSION['editor']) || !isset($_SESSION['id_editor'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../../database/koneksi.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$modeEdit = false;
$kategoriEdit = null;

if ($id !== null) {
    if (!$id || $id < 1) {
        header('Location: tambah_edit.php');
        exit;
    }

    $queryEdit = $pdo->prepare(
        "SELECT id_kategori_jasa, nama_kategori
         FROM kategori_jasa
         WHERE id_kategori_jasa = :id"
    );

    $queryEdit->execute([
        'id' => $id
    ]);

    $kategoriEdit = $queryEdit->fetch(PDO::FETCH_ASSOC);

    if (!$kategoriEdit) {
        header('Location: tambah_edit.php');
        exit;
    }

    $modeEdit = true;
}

$query = $pdo->query(
    "SELECT id_kategori_jasa, nama_kategori
     FROM kategori_jasa
     ORDER BY id_kategori_jasa ASC"
);

$daftarKategori = $query->fetchAll(PDO::FETCH_ASSOC);

function e($nilai)
{
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kelola Kategori Jasa</title>

    <link rel="stylesheet" href="../tampilan/kategori_jasa.css">
</head>

<body>
    <div class="page-container">
        <div class="content-container">

            <div class="top-actions">
                <a
                    href="../../customer/informasi/kategori_jasa.php"
                    class="back-btn"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    Cek Halaman Customer
                </a>

                <a
                    href="../auth/logout.php"
                    class="logout-btn"
                    onclick="return confirm('Apakah anda ingin logout?')"
                >
                    Logout
                </a>
            </div>

            <div class="category-card">
                <div class="card-header">
                    <h1 class="title">Kelola Kategori Jasa</h1>
                </div>

                <?php if ($modeEdit): ?>

                    <h2 class="form-title">Mengubah Nama Kategori</h2>

                    <form action="proses_edit.php" method="POST">
                        <input
                            type="hidden"
                            name="id_kategori_jasa"
                            value="<?= (int) $kategoriEdit['id_kategori_jasa'] ?>"
                        >

                        <div class="form-group">
                            <input
                                type="text"
                                id="nama_kategori"
                                name="nama_kategori"
                                value="<?= e($kategoriEdit['nama_kategori']) ?>"
                                maxlength="100"
                                required
                            >

                            <button type="submit" class="submit-btn">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>

                    <a href="tambah_edit.php" class="back-btn">
                        Batal Edit
                    </a>

                <?php else: ?>

                    <h2 class="form-title">Tambah Kategori Jasa</h2>

                    <form action="proses_tambah.php" method="POST">
                        <div class="form-group">
                            <input
                                type="text"
                                id="nama_kategori"
                                name="nama_kategori"
                                placeholder="Contoh: Design Logo"
                                maxlength="100"
                                required
                            >

                            <button type="submit" class="submit-btn">
                                Tambah
                            </button>
                        </div>
                    </form>

                <?php endif; ?>

                <div class="category-list">
                    <h2>Daftar Kategori Jasa</h2>

                    <?php if (!empty($daftarKategori)): ?>

                        <div class="category-items">

                            <?php foreach ($daftarKategori as $kategori): ?>

                                <div class="category-item">

                                    <div class="category-info">
                                        <p class="category-name">
                                            <?= e($kategori['nama_kategori']) ?>
                                        </p>
                                    </div>

                                    <div class="category-actions">

                                        <a
                                            href="../detail_jasa/portofolio.php?id_kategori_jasa=<?= (int) $kategori['id_kategori_jasa'] ?>"
                                            class="manage-btn"
                                        >
                                            Kelola Jasa
                                        </a>

                                        <a
                                            href="tambah_edit.php?id=<?= (int) $kategori['id_kategori_jasa'] ?>"
                                            class="edit-btn"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            action="hapus.php"
                                            method="POST"
                                            onsubmit="return confirm('Apakah anda ingin menghapus kategori ini?')"
                                        >
                                            <input
                                                type="hidden"
                                                name="id_kategori_jasa"
                                                value="<?= (int) $kategori['id_kategori_jasa'] ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="delete-btn"
                                            >
                                                Hapus
                                            </button>
                                        </form>

                                    </div>
                                </div>

                            <?php endforeach; ?>

                        </div>

                    <?php else: ?>

                        <p class="empty-message">
                            Belum ada kategori jasa yang tersedia.
                        </p>

                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</body>

</html>