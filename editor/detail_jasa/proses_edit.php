<?php
session_start();

if (!isset($_SESSION['editor']) || !isset($_SESSION['id_editor'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../../database/koneksi.php';

function kembaliDenganError($pesan, $id_detail_jasa, $id_kategori_jasa)
{
    $_SESSION['error_jasa'] = $pesan;

    header(
        'Location: edit.php?id=' . $id_detail_jasa .
        '&id_kategori_jasa=' . $id_kategori_jasa
    );

    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Akses tidak diizinkan.');
}

$id_editor = $_SESSION['id_editor'];

$id_detail_jasa = (int) ($_POST['id_detail_jasa'] ?? 0);
$id_kategori_jasa = (int) ($_POST['id_kategori_jasa'] ?? 0);

$nama_jasa = trim($_POST['nama_jasa'] ?? '');
$deskripsi = trim($_POST['deskripsi'] ?? '');

$harga_input = $_POST['harga'] ?? '';
$harga = is_string($harga_input) ? trim($harga_input) : '';

if (
    !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
    !is_string($_POST['csrf_token']) ||
    !is_string($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    kembaliDenganError(
        'Permintaan tidak valid.',
        $id_detail_jasa,
        $id_kategori_jasa
    );
}

if (
    $id_detail_jasa <= 0 ||
    $id_kategori_jasa <= 0 ||
    $nama_jasa === ''
) {
    kembaliDenganError(
        'Data jasa tidak valid.',
        $id_detail_jasa,
        $id_kategori_jasa
    );
}

if (preg_match('/^-\d+$/D', $harga)) {
    kembaliDenganError(
        'Harga tidak boleh kurang dari 0.',
        $id_detail_jasa,
        $id_kategori_jasa
    );
}

if (!preg_match('/^\d+$/D', $harga)) {
    kembaliDenganError(
        'Harga harus berupa bilangan bulat tanpa titik atau koma.',
        $id_detail_jasa,
        $id_kategori_jasa
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
        $id_detail_jasa,
        $id_kategori_jasa
    );
}

$harga_db = (int) $harga_normal;

$stmt = $pdo->prepare("
    SELECT id_detail_jasa, gambar
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

if (!$jasa) {
    die('Data jasa tidak ditemukan.');
}

$gambar_lama = $jasa['gambar'];

$ada_gambar_baru = (
    isset($_FILES['gambar']) &&
    $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE
);

$folder_upload = __DIR__ . '/../gambar_portofolio/';
$folder_database = 'editor/gambar_portofolio/';

$tujuan = null;
$path_database = $gambar_lama;
$gambar_berhasil = false;

if ($ada_gambar_baru) {

    if (
        $_FILES['gambar']['error'] === UPLOAD_ERR_INI_SIZE ||
        $_FILES['gambar']['error'] === UPLOAD_ERR_FORM_SIZE
    ) {
        kembaliDenganError(
            'Ukuran gambar maksimal 5 MB.',
            $id_detail_jasa,
            $id_kategori_jasa
        );
    }

    if ($_FILES['gambar']['error'] !== UPLOAD_ERR_OK) {
        kembaliDenganError(
            'Terjadi kesalahan saat mengunggah gambar.',
            $id_detail_jasa,
            $id_kategori_jasa
        );
    }

    if ($_FILES['gambar']['size'] > 5 * 1024 * 1024) {
        kembaliDenganError(
            'Ukuran gambar maksimal 5 MB.',
            $id_detail_jasa,
            $id_kategori_jasa
        );
    }

    $tmp_file = $_FILES['gambar']['tmp_name'];

    if (!is_uploaded_file($tmp_file)) {
        kembaliDenganError(
            'File gambar tidak valid.',
            $id_detail_jasa,
            $id_kategori_jasa
        );
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp_file);

    $format = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png'
    ];

    if (!isset($format[$mime])) {
        kembaliDenganError(
            'Format gambar hanya boleh JPG, JPEG, atau PNG.',
            $id_detail_jasa,
            $id_kategori_jasa
        );
    }

    if (!is_dir($folder_upload)) {
        if (!mkdir($folder_upload, 0755, true) && !is_dir($folder_upload)) {
            kembaliDenganError(
                'Folder penyimpanan gambar tidak tersedia.',
                $id_detail_jasa,
                $id_kategori_jasa
            );
        }
    }

    $ekstensi = $format[$mime];
    $nama_baru = bin2hex(random_bytes(16)) . '.' . $ekstensi;

    $tujuan = $folder_upload . $nama_baru;
    $path_database = $folder_database . $nama_baru;
}

$gambar_berhasil = false;

try {

    $pdo->beginTransaction();

    if ($ada_gambar_baru) {
        if (!move_uploaded_file($tmp_file, $tujuan)) {
            throw new Exception('Gagal menyimpan gambar baru.');
        }

        $gambar_berhasil = true;
    }

    $stmt = $pdo->prepare("
        UPDATE detail_jasa
        SET nama_jasa = ?,
            deskripsi = ?,
            harga = ?,
            gambar = ?
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

    $pdo->commit();

    if ($ada_gambar_baru && !empty($gambar_lama)) {
        $lokasi_gambar_lama = __DIR__ . '/../../' . $gambar_lama;

        if (is_file($lokasi_gambar_lama)) {
            unlink($lokasi_gambar_lama);
        }
    }

    header(
        'Location: portofolio.php?id_kategori_jasa=' .
        $id_kategori_jasa . '&pesan=diubah'
    );

    exit;

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if ($gambar_berhasil && $tujuan && file_exists($tujuan)) {
        unlink($tujuan);
    }

    error_log($e->getMessage());

    kembaliDenganError(
        'Gagal mengubah data jasa.',
        $id_detail_jasa,
        $id_kategori_jasa
    );
}