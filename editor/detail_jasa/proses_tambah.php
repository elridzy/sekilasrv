<?php
session_start();

if (!isset($_SESSION['editor']) || !isset($_SESSION['id_editor'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../../database/koneksi.php';

function kembaliDenganError($pesan, $id_kategori_jasa, $old = [])
{
    $_SESSION['error_jasa'] = $pesan;
    $_SESSION['old_jasa'] = $old;

    header('Location: tambah.php?id_kategori_jasa=' . $id_kategori_jasa);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Akses tidak diizinkan.');
}

$id_editor = $_SESSION['id_editor'];
$id_kategori_jasa = (int) ($_POST['id_kategori_jasa'] ?? 0);

$nama_jasa = trim($_POST['nama_jasa'] ?? '');
$deskripsi = trim($_POST['deskripsi'] ?? '');

$harga_input = $_POST['harga'] ?? '';
$harga = is_string($harga_input) ? trim($harga_input) : '';

$old = [
    'nama_jasa' => $nama_jasa,
    'deskripsi' => $deskripsi,
    'harga' => $harga
];

if (
    !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
    !is_string($_POST['csrf_token']) ||
    !is_string($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    kembaliDenganError('Permintaan tidak valid.', $id_kategori_jasa, $old);
}

if ($id_kategori_jasa <= 0 || $nama_jasa === '') {
    kembaliDenganError('Data jasa tidak valid.', $id_kategori_jasa, $old);
}

if (preg_match('/^-\d+$/D', $harga)) {
    kembaliDenganError(
        'Harga tidak boleh kurang dari 0.',
        $id_kategori_jasa,
        $old
    );
}

if (!preg_match('/^\d+$/D', $harga)) {
    kembaliDenganError(
        'Harga harus berupa bilangan bulat tanpa titik atau koma.',
        $id_kategori_jasa,
        $old
    );
}

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
    kembaliDenganError(
        'Harga melebihi batas nilai INTEGER.',
        $id_kategori_jasa,
        $old
    );
}

$harga_db = (int) $harga_normal;

$stmt = $pdo->prepare("
    SELECT id_kategori_jasa
    FROM kategori_jasa
    WHERE id_kategori_jasa = ?
");

$stmt->execute([$id_kategori_jasa]);

if (!$stmt->fetch()) {
    kembaliDenganError(
        'Kategori jasa tidak ditemukan.',
        $id_kategori_jasa,
        $old
    );
}

if (!isset($_FILES['gambar'])) {
    kembaliDenganError(
        'Silakan pilih gambar portofolio.',
        $id_kategori_jasa,
        $old
    );
}

if (
    $_FILES['gambar']['error'] === UPLOAD_ERR_INI_SIZE ||
    $_FILES['gambar']['error'] === UPLOAD_ERR_FORM_SIZE
) {
    kembaliDenganError(
        'Ukuran gambar maksimal 5 MB.',
        $id_kategori_jasa,
        $old
    );
}

if ($_FILES['gambar']['error'] === UPLOAD_ERR_NO_FILE) {
    kembaliDenganError(
        'Silakan pilih gambar portofolio.',
        $id_kategori_jasa,
        $old
    );
}

if ($_FILES['gambar']['error'] !== UPLOAD_ERR_OK) {
    kembaliDenganError(
        'Terjadi kesalahan saat mengunggah gambar.',
        $id_kategori_jasa,
        $old
    );
}

if ($_FILES['gambar']['size'] > 5 * 1024 * 1024) {
    kembaliDenganError(
        'Ukuran gambar maksimal 5 MB.',
        $id_kategori_jasa,
        $old
    );
}

$folder_upload = __DIR__ . '/../gambar_portofolio/';
$folder_database = 'editor/gambar_portofolio/';

if (!is_dir($folder_upload)) {
    if (!mkdir($folder_upload, 0755, true) && !is_dir($folder_upload)) {
        kembaliDenganError(
            'Folder penyimpanan gambar tidak tersedia.',
            $id_kategori_jasa,
            $old
        );
    }
}

$tmp_file = $_FILES['gambar']['tmp_name'];

if (!is_uploaded_file($tmp_file)) {
    kembaliDenganError(
        'File gambar tidak valid.',
        $id_kategori_jasa,
        $old
    );
}

$ekstensi_asli = strtolower(
    pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION)
);

if (!in_array($ekstensi_asli, ['jpeg', 'png'], true)) {
    kembaliDenganError(
        'Format gambar hanya boleh JPEG dan PNG.',
        $id_kategori_jasa,
        $old
    );
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($tmp_file);

$format = [
    'image/jpeg' => 'jpeg',
    'image/png' => 'png'
];

if (
    !isset($format[$mime]) ||
    $format[$mime] !== $ekstensi_asli
) {
    kembaliDenganError(
        'Format gambar tidak sesuai. Gunakan JPEG atau PNG.',
        $id_kategori_jasa,
        $old
    );
}

$ekstensi = $format[$mime];
$nama_baru = bin2hex(random_bytes(16)) . '.' . $ekstensi;

$tujuan = $folder_upload . $nama_baru;
$path_database = $folder_database . $nama_baru;

try {
    $pdo->beginTransaction();

    if (!move_uploaded_file($tmp_file, $tujuan)) {
        throw new Exception('Gagal menyimpan gambar portofolio.');
    }

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

    $pdo->commit();

    header(
        'Location: portofolio.php?id_kategori_jasa=' .
        $id_kategori_jasa . '&pesan=berhasil'
    );

    exit;

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if (file_exists($tujuan)) {
        unlink($tujuan);
    }

    error_log($e->getMessage());

    kembaliDenganError(
        'Gagal menyimpan data jasa.',
        $id_kategori_jasa,
        $old
    );
}