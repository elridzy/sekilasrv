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

// mengambil data kategori
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

// mengambil nomor whatsapp editor
$stmt = $pdo->prepare("
    SELECT e.no_wa
    FROM detail_jasa dj
    JOIN editor e
        ON dj.id_editor = e.id_editor
    WHERE dj.id_kategori_jasa = ?
    ORDER BY dj.id_detail_jasa DESC
    LIMIT 1
");

$stmt->execute([$id_kategori_jasa]);
$editor = $stmt->fetch(PDO::FETCH_ASSOC);

// mengambil data jasa
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

$no_wa = $editor['no_wa'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Informasi Detail Jasa</title>

    <link rel="stylesheet" href="../tampilan/detail_jasa.css">
</head>

<body>

    <div class="container">

        <div class="page-header">

            <h1>Portofolio Editor</h1>

            <p>
                Kategori: <?= e($kategori['nama_kategori']) ?>
            </p>

        </div>

        <div class="action-header">

            <a href="kategori_jasa.php" class="back-btn">
                Kembali ke Kategori Jasa
            </a>

            <?php if (!empty($no_wa)): ?>

                <p class="whatsapp-info">
                    Jika berminat, hubungi WA editor: <?= e($no_wa) ?>
                </p>

            <?php endif; ?>

        </div>

        <?php if (empty($data_jasa)): ?>

            <div class="empty-message">
                <h3>Belum ada portofolio yang tersedia.</h3>
            </div>

        <?php else: ?>

            <div class="service-list">

                <?php foreach ($data_jasa as $jasa): ?>

                    <div class="service-card">

                        <div class="portfolio-section">

                            <?php if (!empty($jasa['gambar_portofolio'])): ?>

                                <div class="portfolio-list">

                                    <img
                                        src="../../<?= e($jasa['gambar_portofolio']) ?>"
                                        alt="Portofolio <?= e($jasa['nama_jasa']) ?>"
                                        onclick="bukaGambar(this.src)"
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
                                <strong>Deskripsi Jasa:</strong><br>
                                <?= nl2br(e($jasa['deskripsi_jasa'])) ?>
                            </p>

                            <p>
                                <strong>Harga Jasa:</strong><br>

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

    <div class="lightbox" id="lightbox" onclick="tutupGambar()">

        <div class="lightbox-content" onclick="event.stopPropagation()">

            <button
                type="button"
                class="lightbox-close"
                onclick="tutupGambar()"
                aria-label="Tutup preview"
            >
                &times;
            </button>

            <img
                src=""
                alt="Preview gambar portofolio"
                id="gambar-penuh"
            >

        </div>

    </div>

    <script>
        function bukaGambar(src) {
            document.getElementById('gambar-penuh').src = src;
            document.getElementById('lightbox').style.display = 'flex';
        }

        function tutupGambar() {
            document.getElementById('lightbox').style.display = 'none';
            document.getElementById('gambar-penuh').src = '';
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                tutupGambar();
            }
        });
    </script>

</body>

</html>