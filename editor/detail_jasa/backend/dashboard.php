<?php

// memulai session
session_start();

// memeriksa session editor
if (!isset($_SESSION['editor']) || !isset($_SESSION['id_editor'])) {
    header('Location: ../../auth/backend/login.php');
    exit;
}

// menghubungkan halaman dengan database
require_once '../../../database/koneksi.php';

// mengamankan data sebelum ditampilkan
function e($nilai)
{
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
}

// mengambil identitas editor dan kategori jasa
$id_editor = $_SESSION['id_editor'];
$id_kategori_jasa = (int) ($_GET['id_kategori_jasa'] ?? 0);

// memeriksa id kategori jasa
if ($id_kategori_jasa <= 0) {
    die('Kategori jasa tidak valid.');
}

// membuat token csrf jika belum tersedia
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// memproses penyimpanan whatsapp dan instagram
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';

    // memeriksa token keamanan
    if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        die('Token keamanan tidak valid.');
    }

    $aksi = $_POST['aksi'] ?? '';

    // memproses perubahan nomor whatsapp
    if ($aksi === 'whatsapp') {
        $no_wa = trim($_POST['no_wa'] ?? '');

        if (!preg_match('/^\+?[0-9]{8,15}$/', $no_wa)) {
            header("Location: dashboard.php?id_kategori_jasa=$id_kategori_jasa");
            exit;
        }

        $stmt = $pdo->prepare("
            UPDATE editor
            SET no_wa = ?
            WHERE id_editor = ?
        ");

        $stmt->execute([$no_wa, $id_editor]);

    // memproses perubahan username instagram
    } elseif ($aksi === 'instagram') {
        $username_ig = ltrim(trim($_POST['username_ig'] ?? ''), '@');

        if (!preg_match('/^[A-Za-z0-9._]{1,30}$/', $username_ig)) {
            header("Location: dashboard.php?id_kategori_jasa=$id_kategori_jasa");
            exit;
        }

        $stmt = $pdo->prepare("
            UPDATE editor
            SET username_ig = ?
            WHERE id_editor = ?
        ");

        $stmt->execute([$username_ig, $id_editor]);
    }

    // kembali ke dashboard setelah proses selesai
    header("Location: dashboard.php?id_kategori_jasa=$id_kategori_jasa");
    exit;
}

// mengambil data kategori jasa
$stmt = $pdo->prepare("
    SELECT nama_kategori
    FROM kategori_jasa
    WHERE id_kategori_jasa = ?
");

$stmt->execute([$id_kategori_jasa]);
$kategori = $stmt->fetch(PDO::FETCH_ASSOC);

// memeriksa keberadaan kategori
if (!$kategori) {
    die('Kategori jasa tidak ditemukan.');
}

// mengambil data kontak editor
$stmt = $pdo->prepare("
    SELECT no_wa, username_ig
    FROM editor
    WHERE id_editor = ?
");

$stmt->execute([$id_editor]);
$editor = $stmt->fetch(PDO::FETCH_ASSOC);

// memeriksa keberadaan data editor
if (!$editor) {
    die('Data editor tidak ditemukan.');
}

// mengambil seluruh data jasa berdasarkan kategori dan editor
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

$stmt->execute([
    $id_kategori_jasa,
    $id_editor
]);

$data_jasa = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <!-- pengaturan dasar halaman -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kelola Detail Jasa</title>

    <!-- menghubungkan file css -->
    <link rel="stylesheet" href="../frontend/dashboard.css">
</head>

<body>

    <!-- container utama halaman -->
    <div class="container">

        <!-- bagian header halaman -->
        <div class="header-layout">

            <!-- informasi kategori jasa -->
            <div class="page-header">

                <h1>Kelola Detail Jasa</h1>

                <div class="header-info">
                    <p>Kategori: <?= e($kategori['nama_kategori']) ?></p>
                </div>

            </div>

            <!-- kumpulan tombol dan form aksi -->
            <div class="action-header">

                <!-- tombol tambah portofolio -->
                <a
                    href="tambah.php?id_kategori_jasa=<?= $id_kategori_jasa ?>"
                    class="add-btn"
                >
                    Tambah Portofolio
                </a>

                <!-- form penyimpanan nomor whatsapp -->
                <form method="POST" class="whatsapp-form">

                    <div class="whatsapp-input">

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= e($_SESSION['csrf_token']) ?>"
                        >

                        <input
                            type="hidden"
                            name="aksi"
                            value="whatsapp"
                        >

                        <input
                            type="tel"
                            id="no_wa"
                            name="no_wa"
                            placeholder="Nomor WhatsApp Editor"
                            value="<?= e($editor['no_wa'] ?? '') ?>"
                            pattern="\+?[0-9]{8,15}"
                            maxlength="16"
                            required
                        >

                        <button type="submit" class="whatsapp-btn">
                            Simpan
                        </button>

                    </div>

                </form>

                <!-- form penyimpanan username instagram -->
                <form method="POST" class="instagram-form">

                    <div class="instagram-input">

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= e($_SESSION['csrf_token']) ?>"
                        >

                        <input
                            type="hidden"
                            name="aksi"
                            value="instagram"
                        >

                        <input
                            type="text"
                            id="username_ig"
                            name="username_ig"
                            placeholder="Username Instagram"
                            value="<?= e($editor['username_ig'] ?? '') ?>"
                            maxlength="30"
                            required
                        >

                        <button type="submit" class="instagram-btn">
                            Simpan
                        </button>

                    </div>

                </form>

                <!-- tombol melihat halaman customer -->
                <a
                    href="../../../customer/backend/detail_jasa.php?id_kategori_jasa=<?= $id_kategori_jasa ?>"
                    class="customer-btn"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    Cek Halaman Customer
                </a>

                <!-- tombol logout -->
                <a
                    href="../../auth/backend/proses_logout.php"
                    class="logout-btn"
                    onclick="return confirm('Apakah anda ingin logout?')"
                >
                    Logout
                </a>

            </div>

            <!-- baris pencarian terpisah -->
            <div class="search-row">

                <div class="search-form">

                    <input
                        type="text"
                        id="search-input"
                        class="search-input"
                        placeholder="🔍 Cari portofolio berdasarkan nama jasa"
                        autocomplete="off"
                    >

                </div>

            </div>

        </div>

        <!-- menampilkan pesan jika belum ada jasa -->
        <?php if (empty($data_jasa)): ?>

            <div class="empty-message" id="empty-message">

                <h3>Belum ada detail jasa yang tersedia.</h3>

            </div>

        <?php else: ?>

            <!-- daftar seluruh jasa -->
            <div class="service-list" id="service-list">

                <?php foreach ($data_jasa as $jasa): ?>

                    <!-- kartu detail jasa -->
                    <div
                        class="service-card"
                        data-nama="<?= e($jasa['nama_jasa']) ?>"
                    >

                        <!-- bagian gambar portofolio -->
                        <div class="portfolio-section">

                            <?php if (!empty($jasa['gambar_portofolio'])): ?>

                                <div class="portfolio-list">

                                    <img
                                        src="../../../<?= e($jasa['gambar_portofolio']) ?>"
                                        alt="Portofolio <?= e($jasa['nama_jasa']) ?>"
                                        class="preview-image"
                                        onclick="bukaGambar(this.src)"
                                    >

                                </div>

                            <?php else: ?>

                                <p>Belum ada gambar portofolio.</p>

                            <?php endif; ?>

                        </div>

                        <!-- nama jasa -->
                        <div class="service-header">
                            <h3><?= e($jasa['nama_jasa']) ?></h3>
                        </div>

                        <!-- informasi detail jasa -->
                        <div class="service-info">

                            <?php if (!empty(trim($jasa['deskripsi_jasa'] ?? ''))): ?>

                                <p>
                                    <strong>Deskripsi Jasa:</strong><br>
                                    <?= nl2br(e($jasa['deskripsi_jasa'])) ?>
                                </p>

                            <?php endif; ?>

                            <p>
                                <strong>Harga Jasa:</strong><br>
                                Rp <?= number_format((float) $jasa['harga_jasa'], 0, ',', '.') ?>
                            </p>

                            <p>
                                <strong>Tanggal Upload:</strong><br>
                                <?= e(date('d-m-Y', strtotime($jasa['tanggal_upload']))) ?>
                            </p>

                        </div>

                        <!-- tombol edit dan hapus jasa -->
                        <div class="service-actions">

                            <a
                                href="edit.php?id=<?= (int) $jasa['id_detail_jasa'] ?>&id_kategori_jasa=<?= $id_kategori_jasa ?>"
                                class="edit-btn"
                            >
                                Edit
                            </a>

                            <!-- form hapus portofolio -->
                            <form
                                action="proses_hapus.php"
                                method="POST"
                                onsubmit="return confirm('Apakah anda ingin menghapus portofolio ini?')"
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

            <!-- pesan jika hasil pencarian tidak ditemukan -->
            <div class="empty-message" id="empty-message" style="display: none;">
                <h3 id="empty-text">Portofolio tidak ditemukan.</h3>
            </div>

        <?php endif; ?>

    </div>

    <!-- tampilan lightbox untuk memperbesar gambar -->
    <div class="lightbox" id="lightbox" onclick="tutupGambar()">

        <div class="lightbox-content" onclick="event.stopPropagation()">

            <img
                src=""
                alt=""
                id="gambar-besar"
            >

        </div>

    </div>

    <!-- fitur pencarian dan lightbox -->
    <script>

        // fitur live search
        const searchInput = document.getElementById('search-input');
        const serviceCards = document.querySelectorAll('.service-card');
        const emptyMessage = document.getElementById('empty-message');
        const serviceList = document.getElementById('service-list');

        searchInput.addEventListener('input', function () {

            const kataKunci = this.value.toLowerCase().trim();
            let jumlahDitemukan = 0;

            serviceCards.forEach(function (card) {

                const namaJasa = card.dataset.nama.toLowerCase();

                if (namaJasa.includes(kataKunci)) {
                    card.style.display = '';
                    jumlahDitemukan++;
                } else {
                    card.style.display = 'none';
                }

            });

            // menampilkan pesan jika hasil pencarian kosong
            if (serviceCards.length > 0) {

                if (jumlahDitemukan === 0) {
                    emptyMessage.style.display = 'block';
                    serviceList.style.display = 'none';
                } else {
                    emptyMessage.style.display = 'none';
                    serviceList.style.display = '';
                }

            }

        });

        // mengatur lightbox gambar
        const lightbox = document.getElementById('lightbox');
        const gambarBesar = document.getElementById('gambar-besar');

        // membuka gambar dalam ukuran besar
        function bukaGambar(sumber) {
            gambarBesar.src = sumber;
            lightbox.style.display = 'flex';
        }

        // menutup lightbox
        function tutupGambar() {
            lightbox.style.display = 'none';
            gambarBesar.removeAttribute('src');
        }

        // menutup lightbox menggunakan tombol escape
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                tutupGambar();
            }
        });

    </script>

</body>

</html>