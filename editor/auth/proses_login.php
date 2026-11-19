<?php
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'status' => 'gagal',
        'pesan' => 'Metode permintaan tidak diizinkan.'
    ]);

    exit;
}

require_once '../../database/koneksi.php';

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

$query = $pdo->prepare(
    'SELECT id_editor, username, password
     FROM editor
     WHERE username = :username'
);

$query->execute([
    'username' => $username
]);

$editor = $query->fetch(PDO::FETCH_ASSOC);

if (
    $editor &&
    $password === $editor['password'] &&
    $password === '999'
) {
    session_regenerate_id(true);

    $_SESSION['editor'] = $editor['username'];
    $_SESSION['id_editor'] = $editor['id_editor'];

    echo json_encode([
        'status' => 'berhasil',
        'redirect' => '../kategori_jasa/tambah_edit.php'
    ]);

    exit;
}

echo json_encode([
    'status' => 'gagal',
    'pesan' => 'username atau password salah.'
]);

exit;