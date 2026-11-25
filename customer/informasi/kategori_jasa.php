<?php
require_once '../../database/koneksi.php';

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

function e($nilai)
{
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Informasi Kategori Jasa</title>

    <link rel="stylesheet" href="../tampilan/kategori_jasa.css">
</head>

<body>

    <main class="page-container">

        <section class="content-container">

            <div class="category-card">

                <div class="card-header">
                    <h1 class="title">Daftar Kategori Jasa</h1>
                </div>

                <?php if (!isset($error)): ?>

                    <?php if (empty($kategori_jasa)): ?>

                        <div class="message empty-message">
                            Belum ada kategori jasa yang tersedia.
                        </div>

                    <?php else: ?>

                        <div class="category-list">

                            <?php foreach ($kategori_jasa as $kategori): ?>

                                <div class="category-item">

                                    <div class="category-info">
                                        <h2 class="category-name">
                                            <?= e($kategori['nama_kategori']) ?>
                                        </h2>
                                    </div>

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