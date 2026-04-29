<?php

ob_start();
require_once __DIR__ . '/config.php';
ob_end_clean();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonErr('Method not allowed', 405);
}
if (empty($_FILES['files'])) {
    jsonErr('Tidak ada file dikirim. Gunakan field name "files[]".');
}


$raw   = $_FILES['files'];
$count = is_array($raw['name']) ? count($raw['name']) : 1;
$files = [];
if (is_array($raw['name'])) {
    for ($i = 0; $i < $count; $i++) {
        $files[] = [
            'name'     => $raw['name'][$i],
            'type'     => $raw['type'][$i],
            'tmp_name' => $raw['tmp_name'][$i],
            'error'    => $raw['error'][$i],
            'size'     => $raw['size'][$i],
        ];
    }
} else {
    $files[] = $raw;
}


$judulArr  = isset($_POST['judul'])     ? (array)$_POST['judul']     : [];
$katArr    = isset($_POST['kategori'])  ? (array)$_POST['kategori']  : ['umum'];
$deskArr   = isset($_POST['deskripsi']) ? (array)$_POST['deskripsi'] : [];
$tampilAll = isset($_POST['tampil'])    ? (int)(bool)$_POST['tampil'] : 1;

$validKat  = ['pemancingan','renang','fasilitas','aktivitas','umum'];

$uploaded = [];
$errors   = [];

foreach ($files as $idx => $file) {
    $origName = $file['name'];

    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = ['file' => $origName, 'msg' => phpUploadError($file['error'])];
        continue;
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        $mb = round(MAX_FILE_SIZE / 1048576);
        $errors[] = ['file' => $origName, 'msg' => "File terlalu besar. Maksimum {$mb} MB."];
        continue;
    }
    
    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeReal = $finfo->file($file['tmp_name']);
    if (!in_array($mimeReal, ALLOWED_MIME, true)) {
        $errors[] = ['file' => $origName, 'msg' => "Format tidak didukung ({$mimeReal})."];
        continue;
    }
    
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXT, true)) {
        $errors[] = ['file' => $origName, 'msg' => "Ekstensi .{$ext} tidak diizinkan."];
        continue;
    }
    
    $subDir  = date('Y/m') . '/';
    $fullDir = UPLOAD_DIR . $subDir;
    if (!is_dir($fullDir) && !@mkdir($fullDir, 0755, true)) {
        $errors[] = ['file' => $origName, 'msg' => 'Gagal membuat direktori. Cek permission folder uploads/.'];
        continue;
    }
    
    $safeName = bin2hex(random_bytes(8)) . '_' . preg_replace('/[^a-z0-9._-]/', '_', strtolower($origName));
    $safeName = substr($safeName, 0, 160) . '.' . $ext;
    $destPath = $fullDir . $safeName;

    
    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        $errors[] = ['file' => $origName, 'msg' => 'Gagal simpan file. Cek permission folder uploads/.'];
        continue;
    }
    
    $lebar = $tinggi = null;
    $imgInfo = @getimagesize($destPath);
    if ($imgInfo) { $lebar = $imgInfo[0]; $tinggi = $imgInfo[1]; }

    
    $judul     = trim($judulArr[$idx] ?? '') ?: pathinfo($origName, PATHINFO_FILENAME);
    $judul     = mb_substr($judul, 0, 255);
    
    $kategori  = trim($katArr[$idx] ?? $katArr[0] ?? 'umum');
    if (!in_array($kategori, $validKat, true)) $kategori = 'umum';
    $deskripsi = trim($deskArr[$idx] ?? '');
    $pathRel   = $subDir . $safeName;
    $urlPub    = UPLOAD_URL . $pathRel;

    
    try {
        $stmt = db()->prepare(
            'INSERT INTO galeri (judul, deskripsi, kategori, nama_file, path_file, ukuran, tipe_mime, lebar, tinggi, tampil)
             VALUES (:judul, :deskripsi, :kategori, :nama_file, :path_file, :ukuran, :tipe_mime, :lebar, :tinggi, :tampil)'
        );
        $stmt->execute([
            ':judul'      => $judul,
            ':deskripsi'  => $deskripsi,
            ':kategori'   => $kategori,
            ':nama_file'  => $safeName,
            ':path_file'  => $pathRel,
            ':ukuran'     => (int)$file['size'],
            ':tipe_mime'  => $mimeReal,
            ':lebar'      => $lebar,
            ':tinggi'     => $tinggi,
            ':tampil'     => $tampilAll,
        ]);
        $newId = (int)db()->lastInsertId();

        $uploaded[] = [
            'id'         => $newId,
            'judul'      => $judul,
            'kategori'   => $kategori,
            'deskripsi'  => $deskripsi,
            'url'        => $urlPub,
            'path_file'  => $pathRel,
            'ukuran'     => (int)$file['size'],
            'ukuran_fmt' => formatBytes((int)$file['size']),
            'lebar'      => $lebar,
            'tinggi'     => $tinggi,
            'tampil'     => (bool)$tampilAll,
        ];
    } catch (PDOException $e) {
        @unlink($destPath);
        $errors[] = ['file' => $origName, 'msg' => 'Gagal simpan DB: ' . $e->getMessage()];
    }
}

jsonOut([
    'ok'       => count($uploaded) > 0,
    'uploaded' => $uploaded,
    'errors'   => $errors,
    'total'    => count($uploaded),
]);

function phpUploadError(int $code): string {
    return match($code) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File terlalu besar (php.ini).',
        UPLOAD_ERR_PARTIAL    => 'Upload tidak lengkap.',
        UPLOAD_ERR_NO_FILE    => 'Tidak ada file.',
        UPLOAD_ERR_NO_TMP_DIR => 'Folder tmp PHP tidak tersedia.',
        UPLOAD_ERR_CANT_WRITE => 'Gagal tulis ke disk.',
        default               => "Error kode {$code}.",
    };
}
