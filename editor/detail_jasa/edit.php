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
$id_detail_jasa = (int) ($_GET['id'] ?? 0);
$id_kategori_jasa = (int) ($_GET['id_kategori_jasa'] ?? 0);

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

if (!$jasa) {
    die('Data jasa tidak ditemukan.');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = $_SESSION['error_jasa'] ?? '';

unset($_SESSION['error_jasa']);

$gambar_awal = !empty($jasa['gambar_portofolio'])
    ? '../../' . $jasa['gambar_portofolio']
    : '';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Portofolio</title>

    <link rel="stylesheet" href="../tampilan/detail_jasa.css">
</head>

<body>

    <div class="container">

        <div class="form-card">

            <div class="form-header">
                <h1>Edit Portofolio</h1>
            </div>

            <?php if ($error): ?>
                <div class="message error-message" id="pesan">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <form
                action="proses_edit.php"
                method="POST"
                enctype="multipart/form-data"
            >

                <input
                    type="hidden"
                    name="id_detail_jasa"
                    value="<?= $id_detail_jasa ?>"
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

                <div class="form-group">
                    <label>Gambar Portofolio</label>

                    <div class="portfolio-list">

                        <img
                            src="<?= e($gambar_awal) ?>"
                            alt="Portofolio <?= e($jasa['nama_jasa']) ?>"
                            class="preview-image"
                            id="preview-image"
                            style="<?= $gambar_awal ? '' : 'display: none;' ?>"
                            onclick="bukaGambar(this.src)"
                        >

                        <p id="gambar-kosong" style="<?= $gambar_awal ? 'display: none;' : '' ?>">
                            Belum ada gambar portofolio.
                        </p>

                    </div>
                </div>

                <div class="form-group">
                    <label for="gambar">Ganti Gambar Portofolio</label>

                    <input
                        type="file"
                        id="gambar"
                        name="gambar"
                        accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                    >

                    <small>
                        Format JPG, JPEG, dan PNG. Maksimal 5 MB.
                    </small>
                </div>

                <div class="form-group">
                    <label for="nama_jasa">Nama Jasa</label>

                    <input
                        type="text"
                        id="nama_jasa"
                        name="nama_jasa"
                        maxlength="100"
                        value="<?= e($jasa['nama_jasa']) ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="deskripsi">Deskripsi Jasa</label>

                    <textarea
                        id="deskripsi"
                        name="deskripsi"
                        rows="5"
                    ><?= e($jasa['deskripsi_jasa']) ?></textarea>
                </div>

                <div class="form-group">
                    <label for="harga">Harga Jasa</label>

                    <input
                        type="number"
                        id="harga"
                        name="harga"
                        min="1"
                        step="1"
                        value="<?= e($jasa['harga_jasa']) ?>"
                        required
                    >
                </div>

                <div class="form-actions">

                    <button type="submit" class="submit-btn">
                        Simpan Perubahan
                    </button>

                </div>

            </form>

        </div>

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
                id="gambar-penuh"
                src=""
                alt="Gambar Portofolio"
            >

        </div>

    </div>

    <script>
        const inputGambar = document.getElementById('gambar');
        const previewImage = document.getElementById('preview-image');
        const gambarKosong = document.getElementById('gambar-kosong');

        const lightbox = document.getElementById('lightbox');
        const gambarPenuh = document.getElementById('gambar-penuh');

        const gambarAwal = <?= json_encode($gambar_awal) ?>;

        let urlGambar = null;

        inputGambar.addEventListener('change', function () {
            const file = this.files[0];

            if (urlGambar) {
                URL.revokeObjectURL(urlGambar);
                urlGambar = null;
            }

            if (!file) {
                previewImage.src = gambarAwal;
                previewImage.style.display = gambarAwal ? 'block' : 'none';
                gambarKosong.style.display = gambarAwal ? 'none' : 'block';
                return;
            }

            urlGambar = URL.createObjectURL(file);

            previewImage.src = urlGambar;
            previewImage.style.display = 'block';
            gambarKosong.style.display = 'none';
        });

        function bukaGambar(src) {
            gambarPenuh.src = src;
            lightbox.style.display = 'flex';
        }

        function tutupGambar() {
            lightbox.style.display = 'none';
            gambarPenuh.src = '';
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                tutupGambar();
            }
        });

        const pesan = document.getElementById('pesan');

        if (pesan) {
            setTimeout(() => {
                pesan.style.display = 'none';
            }, 3000);
        }

        window.addEventListener('beforeunload', function () {
            if (urlGambar) {
                URL.revokeObjectURL(urlGambar);
            }
        });
    </script>

</body>

</html>