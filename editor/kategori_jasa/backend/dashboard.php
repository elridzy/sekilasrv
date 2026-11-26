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

// mengambil id kategori dari url
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// menentukan mode halaman
$modeEdit = false;
$kategoriEdit = null;

// memeriksa apakah halaman sedang dalam mode edit
if ($id !== null) {
    if (!$id || $id < 1) {
        header('Location: dashboard.php');
        exit;
    }

    // mengambil data kategori yang akan diedit
    $queryEdit = $pdo->prepare(
        "SELECT id_kategori_jasa, nama_kategori
         FROM kategori_jasa
         WHERE id_kategori_jasa = :id"
    );

    $queryEdit->execute([
        'id' => $id
    ]);

    $kategoriEdit = $queryEdit->fetch(PDO::FETCH_ASSOC);

    // mengarahkan kembali jika kategori tidak ditemukan
    if (!$kategoriEdit) {
        header('Location: dashboard.php');
        exit;
    }

    $modeEdit = true;
}

// mengambil seluruh daftar kategori jasa
$query = $pdo->query(
    "SELECT id_kategori_jasa, nama_kategori
     FROM kategori_jasa
     ORDER BY id_kategori_jasa ASC"
);

$daftarKategori = $query->fetchAll(PDO::FETCH_ASSOC);

// mengamankan output html
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

    <!-- menghubungkan file css -->
    <link rel="stylesheet" href="../frontend/dashboard.css">
</head>

<body>
    <div class="page-container">
        <div class="content-container">

            <!-- tombol navigasi halaman -->
            <div class="top-actions">
                <a
                    href="../../../customer/backend/kategori_jasa.php"
                    class="back-btn"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    Cek Halaman Customer
                </a>

                <a
                    href="../../auth/backend/proses_logout.php"
                    class="logout-btn"
                    onclick="return confirm('Apakah anda ingin logout?')"
                >
                    Logout
                </a>
            </div>

            <!-- kartu utama pengelolaan kategori -->
            <div class="category-card">
                <div class="card-header">
                    <h1 class="title">Kelola Kategori Jasa</h1>
                </div>

                <!-- form edit kategori -->
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

                    <a href="dashboard.php" class="back-btn">
                        Batal Edit
                    </a>

                <!-- form tambah kategori -->
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

                <!-- menampilkan daftar kategori jasa -->
                <div class="category-list">
                    <h2>Daftar Kategori Jasa</h2>

                    <?php if (!empty($daftarKategori)): ?>

                        <div class="category-items">

                            <?php $nomor = 1; ?>

                            <?php foreach ($daftarKategori as $kategori): ?>

                                <div class="category-item">

                                    <!-- informasi kategori -->
                                    <div class="category-info">

                                        <span class="category-number">
                                            <?= $nomor++ ?>.
                                        </span>

                                        <p class="category-name">
                                            <?= e($kategori['nama_kategori']) ?>
                                        </p>

                                    </div>

                                    <!-- tombol pengelolaan kategori -->
                                    <div class="category-actions">

                                        <a
                                            href="../../detail_jasa/backend/dashboard.php?id_kategori_jasa=<?= (int) $kategori['id_kategori_jasa'] ?>"
                                            class="manage-btn"
                                        >
                                            Kelola Jasa
                                        </a>

                                        <a
                                            href="dashboard.php?id=<?= (int) $kategori['id_kategori_jasa'] ?>"
                                            class="edit-btn"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            action="proses_hapus.php"
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

                        <!-- pesan jika kategori masih kosong -->
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