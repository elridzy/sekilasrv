<?php

// memulai session
session_start();

// memeriksa session editor
if (!isset($_SESSION['editor']) || !isset($_SESSION['id_editor'])) {
    header('Location: ../../auth/backend/login.php');
    exit;
}

// menghubungkan halaman dengan database
require_once '../../../database/koneksi.php';

// mengembalikan editor ke halaman tambah jika terjadi kesalahan
function kembaliDenganError($id_kategori_jasa, $old = [])
{
    $_SESSION['old_jasa'] = $old;

    header('Location: tambah.php?id_kategori_jasa=' . $id_kategori_jasa);
    exit;
}

// memeriksa metode request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Akses tidak diizinkan.');
}

// mengambil identitas editor dan kategori
$id_editor = $_SESSION['id_editor'];
$id_kategori_jasa = (int) ($_POST['id_kategori_jasa'] ?? 0);

// mengambil data input jasa
$nama_jasa = trim($_POST['nama_jasa'] ?? '');
$deskripsi = trim($_POST['deskripsi'] ?? '');

$harga_input = $_POST['harga'] ?? '';
$harga = is_string($harga_input) ? trim($harga_input) : '';

// menyimpan input untuk ditampilkan kembali jika terjadi kesalahan
$old = [
    'nama_jasa' => $nama_jasa,
    'deskripsi' => $deskripsi,
    'harga' => $harga
];

// memvalidasi token csrf
if (
    !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
    !is_string($_POST['csrf_token']) ||
    !is_string($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    kembaliDenganError($id_kategori_jasa, $old);
}

// memeriksa kelengkapan data
if ($id_kategori_jasa <= 0 || $nama_jasa === '') {
    kembaliDenganError($id_kategori_jasa, $old);
}

// memeriksa format harga
if (preg_match('/^-\d+$/D', $harga)) {
    kembaliDenganError($id_kategori_jasa, $old);
}

if (!preg_match('/^\d+$/D', $harga)) {
    kembaliDenganError($id_kategori_jasa, $old);
}

// memeriksa batas maksimum harga
$harga_normal = ltrim($harga, '0');

if ($harga_normal === '') {
    $harga_normal = '0';
}

if (
    strlen($harga_normal) > 10 ||
    (
        strlen($harga_normal) === 10 &&
        strcmp($harga_normal, '2147483647') > 0
    )
) {
    kembaliDenganError($id_kategori_jasa, $old);
}

// mengubah harga menjadi integer
$harga_db = (int) $harga_normal;

// memeriksa keberadaan kategori jasa
$stmt = $pdo->prepare("
    SELECT id_kategori_jasa
    FROM kategori_jasa
    WHERE id_kategori_jasa = ?
");

$stmt->execute([$id_kategori_jasa]);

if (!$stmt->fetch()) {
    kembaliDenganError($id_kategori_jasa, $old);
}

// memeriksa keberadaan file gambar
if (!isset($_FILES['gambar'])) {
    kembaliDenganError($id_kategori_jasa, $old);
}

// memeriksa batas ukuran upload dari konfigurasi server
if (
    $_FILES['gambar']['error'] === UPLOAD_ERR_INI_SIZE ||
    $_FILES['gambar']['error'] === UPLOAD_ERR_FORM_SIZE
) {
    kembaliDenganError($id_kategori_jasa, $old);
}

// memeriksa apakah gambar sudah dipilih
if ($_FILES['gambar']['error'] === UPLOAD_ERR_NO_FILE) {
    kembaliDenganError($id_kategori_jasa, $old);
}

// memeriksa status upload
if ($_FILES['gambar']['error'] !== UPLOAD_ERR_OK) {
    kembaliDenganError($id_kategori_jasa, $old);
}

// membatasi ukuran gambar maksimal 5 mb
if ($_FILES['gambar']['size'] > 5 * 1024 * 1024) {
    kembaliDenganError($id_kategori_jasa, $old);
}

// menentukan lokasi folder upload dan path database
$folder_upload = __DIR__ . '/../../gambar_portofolio/';
$folder_database = 'editor/gambar_portofolio/';

// membuat folder upload jika belum tersedia
if (!is_dir($folder_upload)) {
    if (!mkdir($folder_upload, 0755, true) && !is_dir($folder_upload)) {
        kembaliDenganError($id_kategori_jasa, $old);
    }
}

// mengambil file sementara
$tmp_file = $_FILES['gambar']['tmp_name'];

// memastikan file benar-benar berasal dari proses upload
if (!is_uploaded_file($tmp_file)) {
    kembaliDenganError($id_kategori_jasa, $old);
}

// memeriksa ekstensi file gambar
$ekstensi_asli = strtolower(
    pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION)
);

if (!in_array($ekstensi_asli, ['jpg', 'jpeg', 'png'], true)) {
    kembaliDenganError($id_kategori_jasa, $old);
}

// memeriksa tipe mime gambar
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($tmp_file);

// menentukan format gambar yang diperbolehkan
$format = [
    'image/jpeg' => ['jpg', 'jpeg'],
    'image/png' => ['png']
];

// memastikan ekstensi sesuai dengan tipe mime
if (
    !isset($format[$mime]) ||
    !in_array($ekstensi_asli, $format[$mime], true)
) {
    kembaliDenganError($id_kategori_jasa, $old);
}

// membuat nama file gambar secara acak
$ekstensi = $ekstensi_asli;
$nama_baru = bin2hex(random_bytes(16)) . '.' . $ekstensi;

// menentukan lokasi penyimpanan gambar
$tujuan = $folder_upload . $nama_baru;
$path_database = $folder_database . $nama_baru;

// menyimpan data jasa ke database
try {

    // memulai transaksi database
    $pdo->beginTransaction();

    // memindahkan gambar ke folder upload
    if (!move_uploaded_file($tmp_file, $tujuan)) {
        throw new Exception('Gagal menyimpan gambar portofolio.');
    }

    // memasukkan data jasa baru
    $stmt = $pdo->prepare("
        INSERT INTO detail_jasa (
            id_kategori_jasa,
            id_editor,
            nama_jasa,
            deskripsi_jasa,
            harga_jasa,
            gambar_portofolio
        )
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $id_kategori_jasa,
        $id_editor,
        $nama_jasa,
        $deskripsi,
        $harga_db,
        $path_database
    ]);

    // menyimpan perubahan database
    $pdo->commit();

    // kembali ke dashboard detail jasa
    header(
        'Location: dashboard.php?id_kategori_jasa=' . $id_kategori_jasa
    );

    exit;

} catch (Throwable $e) {

    // membatalkan transaksi jika terjadi kesalahan
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // menghapus gambar jika penyimpanan data gagal
    if (file_exists($tujuan)) {
        unlink($tujuan);
    }

    // mencatat kesalahan ke log server
    error_log($e->getMessage());

    // mengembalikan editor ke halaman tambah
    kembaliDenganError($id_kategori_jasa, $old);
}

?>