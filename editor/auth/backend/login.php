<?php
// memulai session
session_start();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <!-- pengaturan dasar halaman -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login Editor</title>

    <!-- menghubungkan file css -->
    <link rel="stylesheet" href="../frontend/login.css">
</head>

<body>

    <!-- container utama login -->
    <div class="login-container">

        <!-- pembungkus kartu login -->
        <div class="login-wrapper">

            <!-- kartu login -->
            <div class="login-card">

                <h1>Login Editor</h1>

                <!-- deskripsi halaman -->
                <p class="subtitle">
                    Login untuk mengelola jasa.
                </p>

                <!-- form login editor -->
                <form id="form-login" action="proses_login.php" method="POST">

                    <!-- input username -->
                    <div class="form-group">
                        <input
                            type="text"
                            id="username"
                            name="username"
                            placeholder="Masukkan username"
                            autocomplete="username"
                            required
                        >
                    </div>

                    <!-- input password -->
                    <div class="form-group">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Masukkan password"
                            autocomplete="current-password"
                            required
                        >
                    </div>

                    <!-- tombol login -->
                    <button type="submit" class="login-btn" id="tombol-login">
                        Login
                    </button>

                </form>

                <!-- pesan kesalahan login -->
                <div class="error-message" id="pesan-error" style="display: none;"></div>

            </div>

        </div>

    </div>

    <!-- proses login menggunakan javascript -->
    <script>
        const formLogin = document.getElementById('form-login');
        const pesanError = document.getElementById('pesan-error');
        const tombolLogin = document.getElementById('tombol-login');

        // menangani pengiriman form login
        formLogin.addEventListener('submit', async function(event) {
            event.preventDefault();

            pesanError.style.display = 'none';
            tombolLogin.disabled = true;
            tombolLogin.textContent = 'Memproses...';

            try {
                // mengirim data login ke server
                const response = await fetch('proses_login.php', {
                    method: 'POST',
                    body: new FormData(formLogin)
                });

                const hasil = await response.json();

                // mengarahkan editor jika login berhasil
                if (hasil.status === 'berhasil') {
                    window.location.href = hasil.redirect;
                    return;
                }

                // menampilkan pesan jika login gagal
                pesanError.textContent = hasil.pesan;
                pesanError.style.display = 'block';

            } catch (error) {
                // menampilkan pesan jika terjadi kesalahan
                pesanError.textContent = 'Terjadi kesalahan. Silakan coba lagi.';
                pesanError.style.display = 'block';
            }

            // mengaktifkan kembali tombol login
            tombolLogin.disabled = false;
            tombolLogin.textContent = 'Login';
        });
    </script>

</body>

</html>