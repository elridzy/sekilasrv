<?php
session_start();

if (!isset($_SESSION['editor']) || !isset($_SESSION['id_editor'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../../database/koneksi.php';

function e($nilai)
{
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
}

$id_editor = $_SESSION['id_editor'];
$id_kategori_jasa = (int) ($_GET['id_kategori_jasa'] ?? 0);

if ($id_kategori_jasa <= 0) {
    die('Kategori jasa tidak valid.');
}

$stmt = $pdo->prepare("
    SELECT nama_kategori
    FROM kategori_jasa
    WHERE id_kategori_jasa = ?
");

$stmt->execute([$id_kategori_jasa]);
$kategori = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$kategori) {
    die('Kategori jasa tidak ditemukan.');
}

$stmt = $pdo->prepare("
    SELECT
        id_detail_jasa,
        id_kategori_jasa,
        id_editor,
        nama_jasa,
        deskripsi_jasa,
        harga_jasa,
        gambar_portofolio,
        tanggal_upload
    FROM detail_jasa
    WHERE id_kategori_jasa = ?
    AND id_editor = ?
    ORDER BY id_detail_jasa DESC
");

$stmt->execute([$id_kategori_jasa, $id_editor]);
$data_jasa = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pesan = $_GET['pesan'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kelola Portofolio Editor</title>

    <link rel="stylesheet" href="../tampilan/detail_jasa.css">
</head>
<body>

<div class="container">

    <div class="page-header">
        <div>
            <h1>Kelola Portofolio Editor</h1>
            <p>Kategori: <?= e($kategori['nama_kategori']) ?></p>
        </div>
    </div>

    <?php if ($pesan === 'berhasil'): ?>

        <div class="message success-message">
            Portofolio berhasil ditambahkan.
        </div>

    <?php elseif ($pesan === 'diubah'): ?>

        <div class="message success-message">
            Portofolio berhasil diperbarui.
        </div>

    <?php elseif ($pesan === 'dihapus'): ?>

        <div class="message success-message">
            Portofolio berhasil dihapus.
        </div>

    <?php endif; ?>

    <div class="action-header">

        <a
            href="tambah.php?id_kategori_jasa=<?= $id_kategori_jasa ?>"
            class="add-btn"
        >
            Tambah Portofolio
        </a>

        <a
            href="../../customer/informasi/detail_jasa.php?id_kategori_jasa=<?= $id_kategori_jasa ?>"
            class="customer-btn"
            target="_blank"
            rel="noopener noreferrer"
        >
            Cek Halaman Customer
        </a>

    </div>

    <?php if (empty($data_jasa)): ?>

        <div class="empty-message">
            <h3>Portofolio Belum Tersedia</h3>
        </div>

    <?php else: ?>

        <div class="service-list">

            <?php foreach ($data_jasa as $jasa): ?>

                <div class="service-card">

                    <div class="portfolio-section">
                        <h4>Portofolio</h4>

                        <?php if (!empty($jasa['gambar_portofolio'])): ?>

                            <div class="portfolio-list">
                                <img
                                    src="../../<?= e($jasa['gambar_portofolio']) ?>"
                                    alt="Portofolio <?= e($jasa['nama_jasa']) ?>"
                                >
                            </div>

                        <?php else: ?>

                            <p>Belum ada gambar portofolio.</p>

                        <?php endif; ?>
                    </div>

                    <div class="service-header">
                        <h3><?= e($jasa['nama_jasa']) ?></h3>
                    </div>

                    <div class="service-info">

                        <p>
                            <strong>Deskripsi:</strong><br>
                            <?= nl2br(e($jasa['deskripsi_jasa'])) ?>
                        </p>

                        <p>
                            <strong>Harga:</strong><br>
                            Rp<?= number_format((float) $jasa['harga_jasa'], 0, ',', '.') ?>
                        </p>

                        <p>
                            <strong>Tanggal Upload:</strong><br>
                            <?= e($jasa['tanggal_upload']) ?>
                        </p>

                    </div>

                    <div class="service-actions">

                        <a
                            href="edit.php?id=<?= (int) $jasa['id_detail_jasa'] ?>&id_kategori_jasa=<?= $id_kategori_jasa ?>"
                            class="edit-btn"
                        >
                            Edit
                        </a>

                        <form
                            action="hapus.php"
                            method="POST"
                            onsubmit="return confirm('Yakin ingin menghapus portofolio?')"
                        >
                            <input
                                type="hidden"
                                name="id_detail_jasa"
                                value="<?= (int) $jasa['id_detail_jasa'] ?>"
                            >

                            <input
                                type="hidden"
                                name="id_kategori_jasa"
                                value="<?= $id_kategori_jasa ?>"
                            >

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= e($_SESSION['csrf_token']) ?>"
                            >

                            <button type="submit" class="delete-btn">
                                Hapus
                            </button>
                        </form>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>

<script>
    const pesanBerhasil = document.querySelector('.success-message');

    if (pesanBerhasil) {
        setTimeout(() => {
            pesanBerhasil.remove();
        }, 3000);
    }
</script>

</body>
</html>