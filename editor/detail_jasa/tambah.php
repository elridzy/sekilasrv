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

    <title>Tambah Portofolio Editor</title>

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

            <form
                action="proses_tambah.php"
                method="POST"
                enctype="multipart/form-data"
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
                    <label for="nama_jasa">Nama Jasa</label>

                    <input
                        type="text"
                        id="nama_jasa"
                        name="nama_jasa"
                        maxlength="100"
                        value="<?= e($old['nama_jasa'] ?? '') ?>"
                        placeholder="Masukkan nama jasa"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="deskripsi">Deskripsi Jasa</label>

                    <textarea
                        id="deskripsi"
                        name="deskripsi"
                        rows="5"
                        placeholder="Tambahkan deskripsi jasa"
                    ><?= e($old['deskripsi'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="harga">Harga Jasa</label>

                    <input
                        type="number"
                        id="harga"
                        name="harga"
                        min="0"
                        max="2147483647"
                        step="1"
                        value="<?= e($old['harga'] ?? '') ?>"
                        placeholder="Masukkan harga jasa"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="gambar">Gambar Portofolio</label>

                    <input
                        type="file"
                        id="gambar"
                        name="gambar"
                        accept=".jpeg,.png,image/jpeg,image/png"
                        required
                    >

                    <small>
                        Format JPEG dan PNG. Maksimal 5 MB.
                    </small>
                </div>

                <div class="form-actions">

                    <button type="submit" class="submit-btn">
                        Simpan Portofolio
                    </button>

                </div>

            </form>

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