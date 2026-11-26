<?php
// memulai session
session_start();

// mengatur format respons menjadi json
header('Content-Type: application/json');

// memeriksa metode permintaan
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'status' => 'gagal',
        'pesan' => 'Metode permintaan tidak diizinkan.'
    ]);

    exit;
}

// menghubungkan halaman dengan database
require_once '../../../database/koneksi.php';

// mengambil data username dan password
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// mencari data editor berdasarkan username
$query = $pdo->prepare(
    'SELECT id_editor, username, password
     FROM editor
     WHERE username = :username'
);

$query->execute([
    'username' => $username
]);

$editor = $query->fetch(PDO::FETCH_ASSOC);

// memeriksa username dan password
if (
    $editor &&
    $password === $editor['password'] &&
    $password === '999'
) {
    // memperbarui session id setelah login berhasil
    session_regenerate_id(true);

    // menyimpan informasi editor ke dalam session
    $_SESSION['editor'] = $editor['username'];
    $_SESSION['id_editor'] = $editor['id_editor'];

    // mengirim respons berhasil dan mengarahkan ke dashboard kategori jasa
    echo json_encode([
        'status' => 'berhasil',
        'redirect' => '../../kategori_jasa/backend/dashboard.php'
    ]);

    exit;
}

// mengirim respons jika login gagal
echo json_encode([
    'status' => 'gagal',
    'pesan' => 'username atau password salah.'
]);

exit;
?>