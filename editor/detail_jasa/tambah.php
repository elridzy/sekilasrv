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

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = $_SESSION['error_jasa'] ?? '';
$old = $_SESSION['old_jasa'] ?? [];

unset($_SESSION['error_jasa'], $_SESSION['old_jasa']);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tambah Portofolio</title>

    <link rel="stylesheet" href="../tampilan/detail_jasa.css">
</head>

<body>

    <div class="container">

        <div class="form-card">

            <div class="form-header">
                <h1>Tambah Portofolio</h1>
                <p>Kategori: <?= e($kategori['nama_kategori']) ?></p>
            </div>

            <?php if ($error): ?>
                <div class="message error-message" id="pesan">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <form action="proses_tambah.php" method="POST" enctype="multipart/form-data">

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

                <div class="form-top-row">

                    <div class="form-group upload-group">

                        <label for="gambar" class="upload-btn">
                            Gambar Portofolio
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

                <small class="upload-info">
                    Format JPG, JPEG, dan PNG. Maksimal 5 MB.
                </small>

                <div class="portfolio-list" id="preview-container" style="display: none;">
                    <img
                        src=""
                        alt="Preview Portofolio"
                        id="preview-image"
                        class="preview-image"
                    >
                </div>

                <div class="form-row">

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

                </div>

                <div class="form-group">
                    <textarea
                        id="deskripsi"
                        name="deskripsi"
                        rows="5"
                        placeholder="Deskripsi Jasa (Opsional)"
                    ><?= e($old['deskripsi'] ?? '') ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="submit-btn">
                        Simpan Portofolio
                    </button>
                </div>

            </form>

        </div>

    </div>

    <div class="lightbox" id="lightbox" onclick="tutupGambar()">

        <div class="lightbox-content" onclick="event.stopPropagation()">

            <img
                id="gambar-penuh"
                src=""
                alt="Gambar Portofolio"
            >

        </div>

    </div>

    <script>
        const inputGambar = document.getElementById('gambar');
        const previewContainer = document.getElementById('preview-container');
        const previewImage = document.getElementById('preview-image');

        let urlGambar = null;

        inputGambar.addEventListener('change', function() {
            const file = this.files[0];

            if (urlGambar) {
                URL.revokeObjectURL(urlGambar);
                urlGambar = null;
            }

            if (!file) {
                previewContainer.style.display = 'none';
                previewImage.src = '';
                return;
            }

            urlGambar = URL.createObjectURL(file);

            previewImage.src = urlGambar;
            previewContainer.style.display = 'flex';
        });

        previewImage.addEventListener('click', function() {
            bukaGambar(this.src);
        });

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

        const pesan = document.getElementById('pesan');

        if (pesan) {
            setTimeout(() => {
                pesan.style.display = 'none';
            }, 3000);
        }

        window.addEventListener('beforeunload', function() {
            if (urlGambar) {
                URL.revokeObjectURL(urlGambar);
            }
        });
    </script>

</body>

</html>