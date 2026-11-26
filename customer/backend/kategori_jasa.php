<?php
// menghubungkan halaman dengan database
require_once '../../database/koneksi.php';

// mengambil data kategori jasa dari database
try {
    $query = $pdo->query("
        SELECT id_kategori_jasa, nama_kategori
        FROM kategori_jasa
        ORDER BY id_kategori_jasa ASC
    ");

    $kategori_jasa = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $kategori_jasa = [];
    $error = true;
}

// mengamankan data sebelum ditampilkan ke halaman
function e($nilai)
{
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <!-- pengaturan dasar halaman -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Informasi Kategori Jasa</title>

    <!-- menghubungkan file css -->
    <link rel="stylesheet" href="../frontend/kategori_jasa.css">
</head>

<body>

    <!-- container utama halaman -->
    <main class="page-container">

        <!-- container konten -->
        <section class="content-container">

            <!-- kartu daftar kategori jasa -->
            <div class="category-card">

                <!-- bagian judul kartu -->
                <div class="card-header">
                    <h1 class="title">Daftar Kategori Jasa</h1>
                </div>

                <!-- menampilkan daftar kategori jika tidak terjadi kesalahan -->
                <?php if (!isset($error)): ?>

                    <!-- menampilkan pesan jika kategori masih kosong -->
                    <?php if (empty($kategori_jasa)): ?>

                        <div class="message empty-message">
                            Belum ada kategori jasa yang tersedia.
                        </div>

                    <?php else: ?>

                        <!-- daftar kategori jasa -->
                        <div class="category-list">

                            <?php $nomor = 1; ?>

                            <!-- menampilkan setiap kategori jasa -->
                            <?php foreach ($kategori_jasa as $kategori): ?>

                                <div class="category-item">

                                    <!-- informasi kategori -->
                                    <div class="category-info">

                                        <span class="category-number">
                                            <?= $nomor++ ?>.
                                        </span>

                                        <h2 class="category-name">
                                            <?= e($kategori['nama_kategori']) ?>
                                        </h2>

                                    </div>

                                    <!-- tombol menuju halaman detail jasa -->
                                    <a
                                        href="detail_jasa.php?id_kategori_jasa=<?= (int) $kategori['id_kategori_jasa'] ?>"
                                        class="detail-btn"
                                    >
                                        Lihat Detail Jasa
                                    </a>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    <?php endif; ?>

                <!-- menampilkan pesan jika terjadi kesalahan database -->
                <?php else: ?>

                    <div class="message empty-message">
                        Terjadi kesalahan saat mengambil data kategori jasa.
                    </div>

                <?php endif; ?>

            </div>

        </section>

    </main>

</body>

</html>