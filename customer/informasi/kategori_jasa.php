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
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori Jasa | Customer</title>
    <link rel="stylesheet" href="../tampilan/kategori_jasa.css">
</head>

<body>

    <main class="page-container">

        <section class="content-container">

            <div class="category-card">

                <div class="card-header">
                    <h1 class="title">Kategori Jasa</h1>
                    <p class="subtitle">
                        Pilih kategori untuk melihat informasi jasa.
                    </p>
                </div>

                <?php if (isset($error)): ?>

                    <div class="message error-message">
                        Gagal mengambil data kategori jasa.
                    </div>

                <?php elseif (empty($kategori_jasa)): ?>

                    <div class="message empty-message">
                        Belum ada kategori jasa yang tersedia.
                    </div>

                <?php else: ?>

                    <div class="category-list">

                        <?php foreach ($kategori_jasa as $kategori): ?>

                            <div class="category-item">

                                <div class="category-info">
                                    <h2 class="category-name">
                                        <?= htmlspecialchars(
                                            $kategori['nama_kategori'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </h2>
                                </div>

                                <a
                                    href="../jasa/index.php?id_kategori_jasa=<?= (int) $kategori['id_kategori_jasa'] ?>"
                                    class="detail-btn"
                                >
                                    Lihat Jasa
                                </a>

                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>

            </div>

        </section>

    </main>

</body>

</html>