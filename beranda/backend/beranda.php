<!DOCTYPE html>
<html lang="id">

<head>
    <!-- pengaturan halaman -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Peran</title>

    <!-- menghubungkan file css -->
    <link rel="stylesheet" href="../frontend/beranda.css">
</head>

<body>

    <!-- container utama halaman -->
    <main class="main-container">

        <!-- bagian sambutan -->
        <section class="left-section">
            <div class="welcome-content">
                <h1 class="title">
                    Welcome To <span>sekilasrv</span>
                </h1>

                <p class="description">
                    Website promosi jasa editing yang menyediakan informasi jasa untuk kebutuhan desain anda.
                </p>
            </div>
        </section>

        <!-- bagian pemilihan peran -->
        <section class="right-section">
            <div class="role-card">

                <!-- judul pemilihan peran -->
                <h2 class="heading">Pilih Peran</h2>

                <!-- daftar tombol peran -->
                <div class="button-group">

                    <!-- tombol masuk sebagai customer -->
                    <a href="../../customer/backend/kategori_jasa.php" class="role-btn">
                        <div class="icon-wrapper">🔍</div>

                        <div class="btn-text">
                            <span class="btn-label">Customer</span>
                            <span class="btn-sub">Lihat Jasa</span>
                        </div>
                    </a>

                    <!-- tombol masuk sebagai editor -->
                    <a href="../../editor/auth/backend/login.php" class="role-btn">
                        <div class="icon-wrapper">💻</div>

                        <div class="btn-text">
                            <span class="btn-label">Editor</span>
                            <span class="btn-sub">Kelola Jasa</span>
                        </div>
                    </a>

                </div>
            </div>
        </section>

    </main>

</body>

</html>