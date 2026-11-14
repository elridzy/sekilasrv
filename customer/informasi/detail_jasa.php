<?php
require_once '../../database/koneksi.php';

function e($nilai)
{
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
}

$id_kategori_jasa = filter_input(
    INPUT_GET,
    'id_kategori_jasa',
    FILTER_VALIDATE_INT
);

if (!$id_kategori_jasa || $id_kategori_jasa <= 0) {
    die('Kategori jasa tidak valid.');
}

$stmt = $pdo->prepare("
    SELECT id_kategori_jasa, nama_kategori
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
        nama_jasa,
        deskripsi_jasa,
        harga_jasa,
        gambar_portofolio,
        tanggal_upload
    FROM detail_jasa
    WHERE id_kategori_jasa = ?
    ORDER BY id_detail_jasa DESC
");

$stmt->execute([$id_kategori_jasa]);
$data_jasa = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Informasi Portofolio Editor</title>

    <link rel="stylesheet" href="../tampilan/detail_jasa.css">
</head>

<body>

    <div class="container">

        <div class="page-header">
            <h1>Portofolio Editor</h1>
            <p>Kategori: <?= e($kategori['nama_kategori']) ?></p>
        </div>

        <div class="action-header">
            <a href="kategori_jasa.php" class="back-btn">
                Kembali ke kategori jasa
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

                                <div class="no-image">
                                    Belum ada gambar portofolio.
                                </div>

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
                                <span class="price">
                                    Rp<?= number_format(
                                        (float) $jasa['harga_jasa'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>
                                </span>
                            </p>

                            <p>
                                <strong>Tanggal Upload:</strong><br>
                                <?= e($jasa['tanggal_upload']) ?>
                            </p>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>

</body>

</html>