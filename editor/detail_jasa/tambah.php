<?php
session_start();

if (!isset($_SESSION['editor'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../../database/koneksi.php';

$id_kategori_jasa = filter_input(
    INPUT_GET,
    'id_kategori_jasa',
    FILTER_VALIDATE_INT
);

if (!$id_kategori_jasa) {
    header('Location: ../kategori_jasa/tambah.php');
    exit;
}

$query = $pdo->prepare(
    'SELECT id_kategori_jasa, nama_kategori
     FROM kategori_jasa
     WHERE id_kategori_jasa = :id'
);

$query->execute([
    'id' => $id_kategori_jasa
]);

$kategori = $query->fetch(PDO::FETCH_ASSOC);

if (!$kategori) {
    header('Location: ../kategori_jasa/tambah.php');
    exit;
}

$pesan = $_GET['pesan'] ?? '';

function e($nilai)
{
    return htmlspecialchars(
        (string) $nilai,
        ENT_QUOTES,
        'UTF-8'
    );
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Detail Jasa | Editor</title>

    <link rel="stylesheet" href="../tampilan/detail_jasa.css">
</head>

<body>
    <main class="container">
        <a
            href="../kategori_jasa/tambah.php"
            class="back-btn"
        >
            Kembali
        </a>

        <section class="form-card">
            <header class="form-header">
                <h1>Tambah Jasa</h1>

                <p>
                    Kategori: <?= e($kategori['nama_kategori']) ?>
                </p>
            </header>

            <?php if ($pesan === 'gagal'): ?>
                <div class="message error-message">
                    <?= e(
                        $_SESSION['error_jasa']
                        ?? 'Jasa gagal disimpan. Periksa data yang dimasukkan.'
                    ) ?>
                </div>

                <?php unset($_SESSION['error_jasa']); ?>
            <?php endif; ?>

            <form
                action="proses_tambah.php"
                method="POST"
                enctype="multipart/form-data"
            >
                <input
                    type="hidden"
                    name="id_kategori_jasa"
                    value="<?= (int) $kategori['id_kategori_jasa'] ?>"
                >

                <div class="form-group">
                    <label for="nama_jasa">Nama Jasa</label>

                    <input
                        type="text"
                        id="nama_jasa"
                        name="nama_jasa"
                        maxlength="100"
                        placeholder="Masukkan nama jasa"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="deskripsi">Deskripsi</label>

                    <textarea
                        id="deskripsi"
                        name="deskripsi"
                        rows="5"
                        placeholder="Tambahkan deskripsi jasa"
                    ></textarea>
                </div>

                <div class="form-group">
                    <label for="harga">Harga</label>

                    <input
                        type="number"
                        id="harga"
                        name="harga"
                        min="1"
                        step="1"
                        placeholder="Masukkan harga jasa"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="portofolio">Portofolio</label>

                    <input
                        type="file"
                        id="portofolio"
                        name="portofolio[]"
                        accept=".jpg,.jpeg,.png"
                        multiple
                        required
                    >

                    <small>
                        Format JPG, JPEG, PNG. Maksimal 5 MB per gambar.
                    </small>
                </div>

                <div class="button-group">
                    <button type="submit" class="submit-btn">
                        Simpan Data Jasa
                    </button>

                    <a
                        href="data_jasa.php?id_kategori_jasa=<?= (int) $kategori['id_kategori_jasa'] ?>"
                        class="view-btn"
                    >
                        Lihat Data Jasa
                    </a>
                </div>
            </form>
        </section>
    </main>
</body>

</html>