<?php
session_start();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Editor</title>
    <link rel="stylesheet" href="../tampilan/login.css">
</head>

<body>
    <div class="login-container">
        <div class="login-wrapper">
            <div class="login-card">
                <h1>Login Editor</h1>

                <p class="subtitle">
                    Login untuk mengelola jasa.
                </p>

                <form id="form-login" action="proses_login.php" method="POST">
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

                    <button type="submit" class="login-btn" id="tombol-login">
                        Login
                    </button>
                </form>

                <div class="error-message" id="pesan-error" style="display: none;"></div>
            </div>
        </div>
    </div>

    <script>
        const formLogin = document.getElementById('form-login');
        const pesanError = document.getElementById('pesan-error');
        const tombolLogin = document.getElementById('tombol-login');

        formLogin.addEventListener('submit', async function(event) {
            event.preventDefault();

            pesanError.style.display = 'none';
            tombolLogin.disabled = true;
            tombolLogin.textContent = 'Memproses...';

            try {
                const response = await fetch('proses_login.php', {
                    method: 'POST',
                    body: new FormData(formLogin)
                });

                const hasil = await response.json();

                if (hasil.status === 'berhasil') {
                    window.location.href = hasil.redirect;
                    return;
                }

                pesanError.textContent = hasil.pesan;
                pesanError.style.display = 'block';

            } catch (error) {
                pesanError.textContent = 'Terjadi kesalahan. Silakan coba lagi.';
                pesanError.style.display = 'block';
            }

            tombolLogin.disabled = false;
            tombolLogin.textContent = 'Login';
        });
    </script>
</body>

</html>