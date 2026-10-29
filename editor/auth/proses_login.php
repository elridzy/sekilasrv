<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

require_once '../../database/koneksi.php';

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

$query = $pdo->prepare(
    'SELECT id_editor, username, pasword
     FROM editor
     WHERE username = :username'
);

$query->execute([
    'username' => $username
]);

$editor = $query->fetch(PDO::FETCH_ASSOC);

if (
    $editor &&
    $password === $editor['pasword'] &&
    $password === '999'
) {
    session_regenerate_id(true);

    $_SESSION['editor'] = $editor['username'];
    $_SESSION['id_editor'] = $editor['id_editor'];

    header('Location: ../kategori_jasa/tambah.php');
    exit;
}

$_SESSION['error'] = 'username atau password salah!';

header('Location: login.php');
exit;