<?php
session_start();

$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
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

                <?php if ($error !== ''): ?>
                    <div class="error-message">
                        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <form action="proses_login.php" method="POST">
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

                    <button type="submit" class="login-btn">
                        Login
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>