<?php

// menghubungkan database
require_once '../../database/koneksi.php';

// fungsi untuk mengamankan output
function e($nilai)
{
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
}

// mengambil id kategori dari url
$id_kategori_jasa = filter_input(
    INPUT_GET,
    'id_kategori_jasa',
    FILTER_VALIDATE_INT
);

// memvalidasi id kategori
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

// memeriksa keberadaan kategori
if (!$kategori) {
    die('Kategori jasa tidak ditemukan.');
}

// mengambil nomor whatsapp dan username instagram editor
$stmt = $pdo->query("
    SELECT no_wa, username_ig
    FROM editor
    ORDER BY id_editor DESC
    LIMIT 1
");

$editor = $stmt->fetch(PDO::FETCH_ASSOC);

// mengubah nomor whatsapp ke format link
$nomor_wa_link = preg_replace('/\D+/', '', $editor['no_wa'] ?? '');

if (str_starts_with($nomor_wa_link, '0')) {
    $nomor_wa_link = '62' . substr($nomor_wa_link, 1);
} elseif (str_starts_with($nomor_wa_link, '8')) {
    $nomor_wa_link = '62' . $nomor_wa_link;
}

// menyiapkan username instagram untuk link
$username_ig = ltrim(trim($editor['username_ig'] ?? ''), '@');

// mengambil data jasa berdasarkan kategori
$stmt = $pdo->prepare("
    SELECT
        id_detail_jasa,
        nama_jasa,
        deskripsi_jasa,
        harga_jasa,
        gambar_portofolio
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

    <!-- pengaturan halaman -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informasi Detail Jasa</title>

    <!-- menghubungkan file css -->
    <link rel="stylesheet" href="../frontend/detail_jasa.css">

</head>

<body>

    <!-- container utama -->
    <div class="container">

        <!-- layout panel informasi dan portofolio -->
        <div class="dashboard-layout">

            <!-- panel informasi di sebelah kiri -->
            <aside class="action-header">

                <!-- informasi kategori -->
                <div class="page-header">

                    <h1>Detail Jasa</h1>

                    <p>
                        Kategori: <?= e($kategori['nama_kategori']) ?>
                    </p>

                </div>

                <!-- tombol kembali ke kategori jasa -->
                <a href="kategori_jasa.php" class="back-btn">
                    Kategori Jasa
                </a>

                <?php if (!empty($nomor_wa_link)): ?>

                    <!-- tombol whatsapp editor -->
                    <a
                        href="https://wa.me/<?= e($nomor_wa_link) ?>"
                        class="whatsapp-btn"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        Chat Editor Via WhatsApp
                    </a>

                <?php endif; ?>

                <?php if (!empty($username_ig)): ?>

                    <!-- tombol instagram editor -->
                    <a
                        href="https://www.instagram.com/<?= e(rawurlencode($username_ig)) ?>/"
                        class="instagram-btn"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        Kunjungi Instagram Editor
                    </a>

                <?php endif; ?>

            </aside>

            <!-- bagian daftar portofolio di sebelah kanan -->
            <main class="portfolio-content">

                <!-- baris pencarian -->
                <div class="search-row">

                    <input
                        type="text"
                        id="search-input"
                        class="search-input"
                        placeholder="🔍 Cari portofolio berdasarkan nama jasa"
                        autocomplete="off"
                    >

                </div>

                <!-- menampilkan pesan jika jasa kosong -->
                <?php if (empty($data_jasa)): ?>

                    <div class="empty-message">

                        <h3>Belum ada detail jasa yang tersedia.</h3>

                    </div>

                <?php else: ?>

                    <!-- daftar kartu jasa -->
                    <div class="service-list" id="service-list">

                        <?php foreach ($data_jasa as $jasa): ?>

                            <!-- kartu detail jasa -->
                            <div
                                class="service-card"
                                data-nama="<?= e($jasa['nama_jasa']) ?>"
                            >

                                <!-- bagian portofolio -->
                                <div class="portfolio-section">

                                    <?php if (!empty($jasa['gambar_portofolio'])): ?>

                                        <!-- gambar portofolio -->
                                        <div class="portfolio-list">

                                            <img
                                                src="../../<?= e($jasa['gambar_portofolio']) ?>"
                                                alt="Portofolio <?= e($jasa['nama_jasa']) ?>"
                                                onclick="bukaGambar(this.src)"
                                            >

                                        </div>

                                    <?php else: ?>

                                        <!-- pesan jika gambar belum tersedia -->
                                        <p>Belum ada gambar portofolio.</p>

                                    <?php endif; ?>

                                </div>

                                <!-- nama jasa -->
                                <div class="service-header">

                                    <h3><?= e($jasa['nama_jasa']) ?></h3>

                                </div>

                                <!-- informasi deskripsi dan harga -->
                                <div class="service-info">

                                    <?php if (!empty(trim($jasa['deskripsi_jasa'] ?? ''))): ?>

                                        <p>
                                            <strong>Deskripsi Jasa:</strong><br>
                                            <?= nl2br(e($jasa['deskripsi_jasa'])) ?>
                                        </p>

                                    <?php endif; ?>

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

                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                    <!-- pesan jika hasil pencarian tidak ditemukan -->
                    <div class="empty-message" id="search-empty" style="display: none;">

                        <h3>Portofolio tidak ditemukan.</h3>

                    </div>

                <?php endif; ?>

            </main>

        </div>

    </div>

    <!-- tampilan preview gambar -->
    <div class="lightbox" id="lightbox" onclick="tutupGambar()">

        <div class="lightbox-content" onclick="event.stopPropagation()">

            <!-- gambar ukuran penuh -->
            <img
                src=""
                alt="Preview gambar portofolio"
                id="gambar-penuh"
            >

        </div>

    </div>

    <!-- fitur pencarian dan preview gambar -->
    <script>

        // mengambil elemen pencarian
        const searchInput = document.getElementById('search-input');
        const serviceCards = document.querySelectorAll('.service-card');
        const serviceList = document.getElementById('service-list');
        const searchEmpty = document.getElementById('search-empty');

        // menjalankan pencarian nama jasa
        if (searchInput) {

            searchInput.addEventListener('input', function() {

                const kataKunci = this.value.toLowerCase().trim();
                let jumlahDitemukan = 0;

                // memeriksa setiap kartu jasa
                serviceCards.forEach(function(card) {

                    const namaJasa = card.dataset.nama.toLowerCase();

                    if (namaJasa.includes(kataKunci)) {
                        card.style.display = '';
                        jumlahDitemukan++;
                    } else {
                        card.style.display = 'none';
                    }

                });

                // menampilkan hasil pencarian
                if (jumlahDitemukan === 0) {
                    serviceList.style.display = 'none';
                    searchEmpty.style.display = 'block';
                } else {
                    serviceList.style.display = 'grid';
                    searchEmpty.style.display = 'none';
                }

            });

        }

        // mengambil elemen lightbox
        const lightbox = document.getElementById('lightbox');
        const gambarPenuh = document.getElementById('gambar-penuh');

        // membuka preview gambar
        function bukaGambar(src) {
            gambarPenuh.src = src;
            lightbox.style.display = 'flex';
        }

        // menutup preview gambar
        function tutupGambar() {
            lightbox.style.display = 'none';
            gambarPenuh.removeAttribute('src');
        }

        // menutup preview menggunakan tombol escape
        document.addEventListener('keydown', function(event) {

            if (event.key === 'Escape') {
                tutupGambar();
            }

        });

    </script>

</body>

</html>