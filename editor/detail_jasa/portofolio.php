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
$id_kategori_jasa = (int) ($_GET['id_kategori_jasa'] ?? 0);

if ($id_kategori_jasa <= 0) {
    die('Kategori jasa tidak valid.');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// proses simpan nomor whatsapp
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    $no_wa = trim($_POST['no_wa'] ?? '');

    if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        die('Token keamanan tidak valid.');
    }

    if (!preg_match('/^\+?[0-9]{8,15}$/', $no_wa)) {
        header("Location: portofolio.php?id_kategori_jasa=$id_kategori_jasa&pesan=wa_gagal");
        exit;
    }

    $stmt = $pdo->prepare("
        UPDATE editor
        SET no_wa = ?
        WHERE id_editor = ?
    ");

    $stmt->execute([$no_wa, $id_editor]);

    header("Location: portofolio.php?id_kategori_jasa=$id_kategori_jasa&pesan=wa_diubah");
    exit;
}

// mengambil kategori jasa
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

// mengambil nomor whatsapp editor
$stmt = $pdo->prepare("
    SELECT no_wa
    FROM editor
    WHERE id_editor = ?
");

$stmt->execute([$id_editor]);
$editor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$editor) {
    die('Data editor tidak ditemukan.');
}

// mengambil data jasa
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

$stmt->execute([$id_kategori_jasa, $id_editor]);
$data_jasa = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pesan = $_GET['pesan'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kelola Detail Jasa</title>

    <link rel="stylesheet" href="../tampilan/detail_jasa.css">
</head>

<body>

    <div class="container">

        <div class="page-header">

            <h1>Kelola Portofolio Editor</h1>

            <div class="header-info">
                <p>Kategori: <?= e($kategori['nama_kategori']) ?></p>
            </div>

        </div>

        <div class="action-header">

            <a
                href="tambah.php?id_kategori_jasa=<?= $id_kategori_jasa ?>"
                class="add-btn"
            >
                Tambah Portofolio
            </a>

            <form method="POST" class="whatsapp-form">

                <label for="no_wa">Nomor WhatsApp Editor</label>

                <div class="whatsapp-input">

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= e($_SESSION['csrf_token']) ?>"
                    >

                    <input
                        type="tel"
                        id="no_wa"
                        name="no_wa"
                        placeholder="Masukkan nomor wa anda"
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

            <a
                href="../../customer/informasi/detail_jasa.php?id_kategori_jasa=<?= $id_kategori_jasa ?>"
                class="customer-btn"
                target="_blank"
                rel="noopener noreferrer"
            >
                Cek Halaman Customer
            </a>

            <a href="../auth/logout.php" class="logout-btn">
                Logout
            </a>

        </div>

        <?php if ($pesan === 'berhasil'): ?>

            <div class="message success-message" id="pesan">
                Portofolio berhasil ditambahkan.
            </div>

        <?php elseif ($pesan === 'diubah'): ?>

            <div class="message success-message" id="pesan">
                Portofolio berhasil diperbarui.
            </div>

        <?php elseif ($pesan === 'dihapus'): ?>

            <div class="message success-message" id="pesan">
                Portofolio berhasil dihapus.
            </div>

        <?php elseif ($pesan === 'wa_diubah'): ?>

            <div class="message success-message" id="pesan">
                Nomor WhatsApp berhasil disimpan.
            </div>

        <?php elseif ($pesan === 'wa_gagal'): ?>

            <div class="message error-message" id="pesan">
                Format nomor WhatsApp tidak valid.
            </div>

        <?php endif; ?>

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
                                        class="preview-image"
                                        onclick="bukaGambar(this.src)"
                                    >
                                </div>

                            <?php else: ?>

                                <p>Belum ada gambar portofolio.</p>

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
                                Rp<?= number_format((float) $jasa['harga_jasa'], 0, ',', '.') ?>
                            </p>

                            <p>
                                <strong>Tanggal Upload:</strong><br>
                                <?= e($jasa['tanggal_upload']) ?>
                            </p>

                        </div>

                        <div class="service-actions">

                            <a
                                href="edit.php?id=<?= (int) $jasa['id_detail_jasa'] ?>&id_kategori_jasa=<?= $id_kategori_jasa ?>"
                                class="edit-btn"
                            >
                                Edit
                            </a>

                            <form
                                action="hapus.php"
                                method="POST"
                                onsubmit="return confirm('Yakin ingin menghapus portofolio ini?')"
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
                alt="Preview gambar"
                id="gambar-besar"
            >

        </div>

    </div>

    <script>
        const pesanBerhasil = document.getElementById('pesan');

        if (pesanBerhasil) {
            setTimeout(() => {
                pesanBerhasil.remove();
            }, 3000);
        }

        const lightbox = document.getElementById('lightbox');
        const gambarBesar = document.getElementById('gambar-besar');

        function bukaGambar(sumber) {
            gambarBesar.src = sumber;
            lightbox.style.display = 'flex';
        }

        function tutupGambar() {
            lightbox.style.display = 'none';
            gambarBesar.src = '';
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                tutupGambar();
            }
        });
    </script>

</body>

</html>