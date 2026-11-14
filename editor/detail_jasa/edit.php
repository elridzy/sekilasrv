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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Data Jasa</title>

    <link rel="stylesheet" href="../tampilan/detail_jasa.css">
</head>
<body>

<div class="container">

    <div class="form-card">

        <div class="form-header">
            <h1>Edit Data Jasa</h1>
            <p>Ubah informasi jasa editing.</p>
        </div>

        <?php if ($error): ?>
            <div class="message error-message">
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

            <div class="form-group">
                <label>Gambar Portofolio Saat Ini</label>

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

            <div class="form-group">
                <label for="gambar">Ganti Gambar Portofolio</label>

                <input
                    type="file"
                    id="gambar"
                    name="gambar"
                    accept=".jpg,.jpeg,.png"
                >

                <small>
                    Pilih gambar baru jika ingin mengganti gambar lama.
                    Jika tidak memilih gambar, gambar lama tetap digunakan.
                    Format JPG, JPEG, dan PNG. Maksimal 5 MB.
                </small>
            </div>

            <div class="form-actions">

                <button type="submit" class="submit-btn">
                    Simpan Perubahan
                </button>

            </div>

        </form>

    </div>

</div>

</body>
</html>