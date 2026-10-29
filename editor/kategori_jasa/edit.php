<?php
session_start();

if (!isset($_SESSION['editor'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../../database/koneksi.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header('Location: tambah.php');
    exit;
}

$query = $pdo->prepare(
    "SELECT id_kategori_jasa, nama_kategori
     FROM kategori_jasa
     WHERE id_kategori_jasa = :id"
);

$query->execute([
    'id' => $id
]);

$kategori = $query->fetch(PDO::FETCH_ASSOC);

if (!$kategori) {
    header('Location: tambah.php?pesan=gagal');
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Kategori Jasa</title>

    <link rel="stylesheet" href="../tampilan/kategori_jasa.css">
</head>

<body>
    <div class="page-container">
        <div class="form-container">
            <a href="tambah.php" class="back-btn">
                Kembali
            </a>

            <div class="category-card">
                <div class="card-header">
                    <h1 class="title">Edit Kategori Jasa</h1>
                    <p class="subtitle">Mengubah nama kategori.</p>
                </div>

                <form action="proses_edit.php" method="POST">
                    <input
                        type="hidden"
                        name="id_kategori_jasa"
                        value="<?= (int) $kategori['id_kategori_jasa'] ?>"
                    >

                    <div class="form-group">
                        <label for="nama_kategori">Nama Kategori</label>

                        <input
                            type="text"
                            id="nama_kategori"
                            name="nama_kategori"
                            value="<?= htmlspecialchars(
                                $kategori['nama_kategori'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            maxlength="100"
                            required
                        >
                    </div>

                    <button type="submit" class="submit-btn">
                        Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>