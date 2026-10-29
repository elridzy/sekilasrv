<?php
session_start();

if (!isset($_SESSION['editor'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../../database/koneksi.php';

$query = $pdo->query(
    "SELECT id_kategori_jasa, nama_kategori
     FROM kategori_jasa
     ORDER BY id_kategori_jasa ASC"
);

$daftarKategori = $query->fetchAll(PDO::FETCH_ASSOC);

$pesan = $_GET['pesan'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kategori Jasa | Editor</title>

    <link rel="stylesheet" href="../tampilan/kategori_jasa.css">

    <style>
        .content-container {
            width: 100%;
            max-width: 650px;
        }

        .content-container .back-btn {
            display: table;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <div class="page-container">
        <div class="content-container">

            <a
                href="../../customer/informasi/kategori_jasa.php"
                class="back-btn"
                target="_blank"
                rel="noopener noreferrer"
            >
                Cek Halaman Customer
            </a>

            <div class="category-card">
                <div class="card-header">
                    <h1 class="title">Kategori Jasa</h1>

                    <p class="subtitle">
                        Kelola kategori jasa yang ingin dipromosikan.
                    </p>
                </div>

                <?php if ($pesan === 'berhasil'): ?>
                    <div class="success-message" id="pesan">
                        Kategori jasa berhasil ditambahkan.
                    </div>
                <?php elseif ($pesan === 'diedit'): ?>
                    <div class="success-message" id="pesan">
                        Nama kategori berhasil diperbarui.
                    </div>
                <?php elseif ($pesan === 'dihapus'): ?>
                    <div class="success-message" id="pesan">
                        Kategori jasa berhasil dihapus.
                    </div>
                <?php elseif ($pesan === 'gagal'): ?>
                    <div class="error-message" id="pesan">
                        Proses gagal. Kategori mungkin masih digunakan oleh jasa.
                    </div>
                <?php endif; ?>

                <form action="proses_tambah.php" method="POST">
                    <div class="form-group">
                        <label for="nama_kategori">
                            Masukkan nama kategori
                        </label>

                        <input
                            type="text"
                            id="nama_kategori"
                            name="nama_kategori"
                            placeholder="Contoh: Design Logo"
                            maxlength="100"
                            required
                        >
                    </div>

                    <button type="submit" class="submit-btn">
                        Tambah Kategori
                    </button>
                </form>

                <div class="category-list">
                    <h2>Daftar Kategori Jasa</h2>

                    <?php if (!empty($daftarKategori)): ?>
                        <div class="category-items">
                            <?php foreach ($daftarKategori as $kategori): ?>
                                <div class="category-item">
                                    <div class="category-info">
                                        <p class="category-name">
                                            <?= htmlspecialchars(
                                                $kategori['nama_kategori'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </p>
                                    </div>

                                    <div class="category-actions">
                                        <a
                                            href="../detail_jasa/tambah.php?id_kategori_jasa=<?= (int) $kategori['id_kategori_jasa'] ?>"
                                            class="manage-btn"
                                        >
                                            Kelola Jasa
                                        </a>

                                        <a
                                            href="edit.php?id=<?= (int) $kategori['id_kategori_jasa'] ?>"
                                            class="edit-btn"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            action="hapus.php"
                                            method="POST"
                                            onsubmit="return confirm('Yakin ingin menghapus kategori ini?')"
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
                            Belum ada kategori jasa
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        const pesan = document.getElementById('pesan');

        if (pesan) {
            setTimeout(() => {
                pesan.style.display = 'none';
            }, 3000);
        }
    </script>
</body>

</html>