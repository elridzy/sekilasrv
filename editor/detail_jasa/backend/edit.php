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

// mengambil identitas editor dan jasa
$id_editor = $_SESSION['id_editor'];
$id_detail_jasa = (int) ($_GET['id'] ?? 0);
$id_kategori_jasa = (int) ($_GET['id_kategori_jasa'] ?? 0);

// mengambil data jasa berdasarkan id jasa, editor, dan kategori
$stmt = $pdo->prepare("
    SELECT *
    FROM detail_jasa
    WHERE id_detail_jasa = ?
    AND id_editor = ?
    AND id_kategori_jasa = ?
");

$stmt->execute([
    $id_detail_jasa,
    $id_editor,
    $id_kategori_jasa
]);

$jasa = $stmt->fetch(PDO::FETCH_ASSOC);

// memeriksa keberadaan data jasa
if (!$jasa) {
    die('Data jasa tidak ditemukan.');
}

// membuat token csrf jika belum tersedia
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// menyiapkan path gambar portofolio
$gambar_awal = !empty($jasa['gambar_portofolio'])
    ? '../../../' . $jasa['gambar_portofolio']
    : '';

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <!-- pengaturan dasar halaman -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Portofolio</title>

    <!-- menghubungkan file css -->
    <link rel="stylesheet" href="../frontend/edit.css">
</head>

<body>

    <!-- container utama halaman -->
    <div class="container">

        <!-- kartu form edit portofolio -->
        <div class="form-card">

            <!-- header form -->
            <div class="form-header">
                <h1>Edit Portofolio</h1>
            </div>

            <!-- form untuk mengubah data jasa -->
            <form action="proses_edit.php" method="POST" enctype="multipart/form-data">

                <!-- menyimpan id jasa -->
                <input
                    type="hidden"
                    name="id_detail_jasa"
                    value="<?= $id_detail_jasa ?>"
                >

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
                            Ganti Portofolio
                        </label>

                        <input
                            type="file"
                            id="gambar"
                            name="gambar"
                            accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                            hidden
                        >

                    </div>

                </div>

                <!-- informasi format dan ukuran gambar -->
                <small class="upload-info">
                    Format JPG, JPEG, dan PNG. Maksimal 5 MB.
                </small>

                <!-- preview gambar portofolio -->
                <div class="portfolio-list" id="preview-container">

                    <img
                        src="<?= e($gambar_awal) ?>"
                        alt="Portofolio <?= e($jasa['nama_jasa']) ?>"
                        class="preview-image"
                        id="preview-image"
                        style="<?= $gambar_awal ? '' : 'display: none;' ?>"
                    >

                    <p id="gambar-kosong" style="<?= $gambar_awal ? 'display: none;' : '' ?>">
                        Belum ada gambar portofolio.
                    </p>

                </div>

                <!-- input nama jasa -->
                <div class="form-group">
                    <input
                        type="text"
                        id="nama_jasa"
                        name="nama_jasa"
                        maxlength="100"
                        value="<?= e($jasa['nama_jasa']) ?>"
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
                        min="1"
                        max="2147483647"
                        step="1"
                        value="<?= e($jasa['harga_jasa']) ?>"
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
                    ><?= e($jasa['deskripsi_jasa']) ?></textarea>
                </div>

                <!-- tombol simpan perubahan -->
                <div class="form-actions">
                    <button type="submit" class="submit-btn">
                        Simpan Perubahan
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
        const previewImage = document.getElementById('preview-image');
        const gambarKosong = document.getElementById('gambar-kosong');

        const lightbox = document.getElementById('lightbox');
        const gambarPenuh = document.getElementById('gambar-penuh');

        // mengambil path gambar awal dari php
        const gambarAwal = <?= json_encode($gambar_awal) ?>;

        let urlGambar = null;

        // menampilkan preview ketika gambar diganti
        inputGambar.addEventListener('change', function() {
            const file = this.files[0];

            // menghapus url gambar sementara sebelumnya
            if (urlGambar) {
                URL.revokeObjectURL(urlGambar);
                urlGambar = null;
            }

            // mengembalikan gambar awal jika tidak ada file dipilih
            if (!file) {
                previewImage.src = gambarAwal;
                previewImage.style.display = gambarAwal ? 'block' : 'none';

                gambarKosong.style.display = gambarAwal ? 'none' : 'block';

                return;
            }

            // membuat url sementara untuk gambar baru
            urlGambar = URL.createObjectURL(file);

            previewImage.src = urlGambar;
            previewImage.style.display = 'block';

            gambarKosong.style.display = 'none';
        });

        // membuka lightbox ketika gambar diklik
        previewImage.addEventListener('click', function() {
            if (this.src) {
                bukaGambar(this.src);
            }
        });

        // menampilkan gambar dalam ukuran besar
        function bukaGambar(src) {
            gambarPenuh.src = src;
            lightbox.style.display = 'flex';
        }

        // menutup lightbox
        function tutupGambar() {
            lightbox.style.display = 'none';
            gambarPenuh.src = '';
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