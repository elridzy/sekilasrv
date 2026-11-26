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

// mengembalikan editor ke halaman edit jika terjadi kesalahan
function kembaliDenganError($id_detail_jasa, $id_kategori_jasa)
{
    header(
        'Location: edit.php?id=' . $id_detail_jasa .
        '&id_kategori_jasa=' . $id_kategori_jasa
    );

    exit;
}

// memeriksa metode request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Akses tidak diizinkan.');
}

// mengambil identitas editor
$id_editor = $_SESSION['id_editor'];

// mengambil id jasa dan kategori
$id_detail_jasa = (int) ($_POST['id_detail_jasa'] ?? 0);
$id_kategori_jasa = (int) ($_POST['id_kategori_jasa'] ?? 0);

// mengambil data input jasa
$nama_jasa = trim($_POST['nama_jasa'] ?? '');
$deskripsi = trim($_POST['deskripsi'] ?? '');

$harga_input = $_POST['harga'] ?? '';
$harga = is_string($harga_input) ? trim($harga_input) : '';

// memvalidasi token csrf
if (
    !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
    !is_string($_POST['csrf_token']) ||
    !is_string($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    kembaliDenganError($id_detail_jasa, $id_kategori_jasa);
}

// memeriksa kelengkapan data
if (
    $id_detail_jasa <= 0 ||
    $id_kategori_jasa <= 0 ||
    $nama_jasa === ''
) {
    kembaliDenganError($id_detail_jasa, $id_kategori_jasa);
}

// memeriksa format harga
if (preg_match('/^-\d+$/D', $harga)) {
    kembaliDenganError($id_detail_jasa, $id_kategori_jasa);
}

if (!preg_match('/^\d+$/D', $harga)) {
    kembaliDenganError($id_detail_jasa, $id_kategori_jasa);
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
    kembaliDenganError($id_detail_jasa, $id_kategori_jasa);
}

// mengubah harga menjadi integer
$harga_db = (int) $harga_normal;

// mengambil data jasa berdasarkan kepemilikan editor
$stmt = $pdo->prepare("
    SELECT id_detail_jasa, gambar_portofolio
    FROM detail_jasa
    WHERE id_detail_jasa = ?
    AND id_editor = ?
    AND id_kategori_jasa = ?
");

$stmt->execute([
    $id_detail_jasa,
    $id_editor,
    $id_kategori_jasa
]);

$jasa = $stmt->fetch(PDO::FETCH_ASSOC);

// memeriksa keberadaan data jasa
if (!$jasa) {
    die('Data jasa tidak ditemukan.');
}

// menyimpan path gambar lama
$gambar_lama = $jasa['gambar_portofolio'];

// memeriksa apakah editor mengunggah gambar baru
$ada_gambar_baru = (
    isset($_FILES['gambar']) &&
    $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE
);

// menentukan lokasi folder upload dan path database
$folder_upload = __DIR__ . '/../../gambar_portofolio/';
$folder_database = 'editor/gambar_portofolio/';

// menyiapkan variabel gambar
$tujuan = null;
$path_database = $gambar_lama;
$gambar_berhasil = false;

// memvalidasi gambar baru jika tersedia
if ($ada_gambar_baru) {

    // memeriksa batas ukuran upload dari konfigurasi server
    if (
        $_FILES['gambar']['error'] === UPLOAD_ERR_INI_SIZE ||
        $_FILES['gambar']['error'] === UPLOAD_ERR_FORM_SIZE
    ) {
        kembaliDenganError($id_detail_jasa, $id_kategori_jasa);
    }

    // memeriksa status upload
    if ($_FILES['gambar']['error'] !== UPLOAD_ERR_OK) {
        kembaliDenganError($id_detail_jasa, $id_kategori_jasa);
    }

    // membatasi ukuran gambar maksimal 5 mb
    if ($_FILES['gambar']['size'] > 5 * 1024 * 1024) {
        kembaliDenganError($id_detail_jasa, $id_kategori_jasa);
    }

    // mengambil file sementara
    $tmp_file = $_FILES['gambar']['tmp_name'];

    // memastikan file benar-benar berasal dari proses upload
    if (!is_uploaded_file($tmp_file)) {
        kembaliDenganError($id_detail_jasa, $id_kategori_jasa);
    }

    // memeriksa tipe mime gambar
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp_file);

    // menentukan format gambar yang diperbolehkan
    $format = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png'
    ];

    // menolak format gambar yang tidak didukung
    if (!isset($format[$mime])) {
        kembaliDenganError($id_detail_jasa, $id_kategori_jasa);
    }

    // membuat folder upload jika belum tersedia
    if (!is_dir($folder_upload)) {
        if (!mkdir($folder_upload, 0755, true) && !is_dir($folder_upload)) {
            kembaliDenganError($id_detail_jasa, $id_kategori_jasa);
        }
    }

    // membuat nama file gambar secara acak
    $ekstensi = $format[$mime];
    $nama_baru = bin2hex(random_bytes(16)) . '.' . $ekstensi;

    // menentukan lokasi penyimpanan gambar
    $tujuan = $folder_upload . $nama_baru;
    $path_database = $folder_database . $nama_baru;
}

// memproses perubahan data jasa
try {

    // memulai transaksi database
    $pdo->beginTransaction();

    // memindahkan gambar baru ke folder upload
    if ($ada_gambar_baru) {
        if (!move_uploaded_file($tmp_file, $tujuan)) {
            throw new Exception('Gagal menyimpan gambar baru.');
        }

        $gambar_berhasil = true;
    }

    // memperbarui data jasa di database
    $stmt = $pdo->prepare("
        UPDATE detail_jasa
        SET nama_jasa = ?,
            deskripsi_jasa = ?,
            harga_jasa = ?,
            gambar_portofolio = ?
        WHERE id_detail_jasa = ?
        AND id_editor = ?
        AND id_kategori_jasa = ?
    ");

    $stmt->execute([
        $nama_jasa,
        $deskripsi,
        $harga_db,
        $path_database,
        $id_detail_jasa,
        $id_editor,
        $id_kategori_jasa
    ]);

    // menyimpan perubahan database
    $pdo->commit();

    // menghapus gambar lama setelah perubahan berhasil
    if ($ada_gambar_baru && !empty($gambar_lama)) {
        $lokasi_gambar_lama = __DIR__ . '/../../' . $gambar_lama;

        if (is_file($lokasi_gambar_lama)) {
            unlink($lokasi_gambar_lama);
        }
    }

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

    // menghapus gambar baru jika proses penyimpanan gagal
    if ($gambar_berhasil && $tujuan && file_exists($tujuan)) {
        unlink($tujuan);
    }

    // mencatat kesalahan ke log server
    error_log($e->getMessage());

    // mengembalikan editor ke halaman edit
    kembaliDenganError($id_detail_jasa, $id_kategori_jasa);
}

?>