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

// mengambil id kategori jasa
$id_kategori_jasa = (int) ($_GET['id_kategori_jasa'] ?? 0);

// memeriksa validitas id kategori
if ($id_kategori_jasa <= 0) {
    die('Kategori jasa tidak valid.');
}

// mengambil nama kategori jasa
$stmt = $pdo->prepare("
    SELECT nama_kategori
    FROM kategori_jasa
    WHERE id_kategori_jasa = ?
");

$stmt->execute([$id_kategori_jasa]);
$kategori = $stmt->fetch(PDO::FETCH_ASSOC);

// memeriksa keberadaan kategori jasa
if (!$kategori) {
    die('Kategori jasa tidak ditemukan.');
}

// membuat token csrf jika belum tersedia
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// mengambil data lama jika proses sebelumnya gagal
$old = $_SESSION['old_jasa'] ?? [];

// membersihkan session data sementara
unset($_SESSION['error_jasa'], $_SESSION['old_jasa']);

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <!-- pengaturan dasar halaman -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tambah Portofolio</title>

    <!-- menghubungkan file css -->
    <link rel="stylesheet" href="../frontend/tambah.css">
</head>

<body>

    <!-- container utama halaman -->
    <div class="container">

        <!-- kartu form tambah portofolio -->
        <div class="form-card">

            <!-- header form -->
            <div class="form-header">
                <h1>Tambah Portofolio</h1>
            </div>

            <!-- form untuk menambahkan jasa -->
            <form action="proses_tambah.php" method="POST" enctype="multipart/form-data">

                <!-- menyimpan id kategori jasa -->
                <input
                    type="hidden"
                    name="id_kategori_jasa"
                    value="<?= $id_kategori_jasa ?>"
                >

                <!-- menyimpan token csrf -->
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= e($_SESSION['csrf_token']) ?>"
                >

                <!-- bagian tombol upload gambar -->
                <div class="form-top-row">

                    <div class="form-group upload-group">

                        <label for="gambar" class="upload-btn">
                            Upload Portofolio
                        </label>

                        <input
                            type="file"
                            id="gambar"
                            name="gambar"
                            accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                            required
                            hidden
                        >

                    </div>

                </div>

                <!-- informasi format dan ukuran gambar -->
                <small class="upload-info">
                    Format JPG, JPEG, dan PNG. Maksimal 5 MB.
                </small>

                <!-- tempat preview gambar -->
                <div class="portfolio-list" id="preview-container" style="display: none;">

                    <img
                        src=""
                        alt="Preview Portofolio"
                        id="preview-image"
                        class="preview-image"
                    >

                </div>

                <!-- input nama jasa -->
                <div class="form-group">
                    <input
                        type="text"
                        id="nama_jasa"
                        name="nama_jasa"
                        maxlength="100"
                        value="<?= e($old['nama_jasa'] ?? '') ?>"
                        placeholder="Nama Jasa"
                        required
                    >
                </div>

                <!-- input harga jasa -->
                <div class="form-group">
                    <input
                        type="number"
                        id="harga"
                        name="harga"
                        min="0"
                        max="2147483647"
                        step="1"
                        value="<?= e($old['harga'] ?? '') ?>"
                        placeholder="Harga Jasa"
                        required
                    >
                </div>

                <!-- input deskripsi jasa -->
                <div class="form-group">
                    <textarea
                        id="deskripsi"
                        name="deskripsi"
                        rows="5"
                        placeholder="Deskripsi Jasa (Opsional)"
                    ><?= e($old['deskripsi'] ?? '') ?></textarea>
                </div>

                <!-- tombol simpan portofolio -->
                <div class="form-actions">
                    <button type="submit" class="submit-btn">
                        Simpan Portofolio
                    </button>
                </div>

            </form>

        </div>

    </div>

    <!-- tampilan lightbox untuk memperbesar gambar -->
    <div class="lightbox" id="lightbox" onclick="tutupGambar()">

        <div class="lightbox-content" onclick="event.stopPropagation()">

            <img
                id="gambar-penuh"
                src=""
                alt="Gambar Portofolio"
            >

        </div>

    </div>

    <!-- fitur preview gambar dan lightbox -->
    <script>
        // mengambil elemen halaman
        const inputGambar = document.getElementById('gambar');
        const previewContainer = document.getElementById('preview-container');
        const previewImage = document.getElementById('preview-image');

        let urlGambar = null;

        // menampilkan preview ketika gambar dipilih
        inputGambar.addEventListener('change', function() {
            const file = this.files[0];

            // menghapus url gambar sementara sebelumnya
            if (urlGambar) {
                URL.revokeObjectURL(urlGambar);
                urlGambar = null;
            }

            // menyembunyikan preview jika tidak ada gambar
            if (!file) {
                previewContainer.style.display = 'none';
                previewImage.src = '';
                return;
            }

            // membuat url sementara untuk gambar
            urlGambar = URL.createObjectURL(file);

            previewImage.src = urlGambar;
            previewContainer.style.display = 'flex';
        });

        // membuka lightbox ketika gambar diklik
        previewImage.addEventListener('click', function() {
            bukaGambar(this.src);
        });

        // menampilkan gambar dalam ukuran besar
        function bukaGambar(src) {
            document.getElementById('gambar-penuh').src = src;
            document.getElementById('lightbox').style.display = 'flex';
        }

        // menutup lightbox
        function tutupGambar() {
            document.getElementById('lightbox').style.display = 'none';
            document.getElementById('gambar-penuh').src = '';
        }

        // menutup lightbox menggunakan tombol escape
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                tutupGambar();
            }
        });

        // membersihkan url gambar sementara
        window.addEventListener('beforeunload', function() {
            if (urlGambar) {
                URL.revokeObjectURL(urlGambar);
            }
        });
    </script>

</body>

</html>