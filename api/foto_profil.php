<?php
/**
 * api/foto_profil.php — Upload & Simpan Foto Profil Admin ke Database
 *
 * POST multipart/form-data:
 *   foto   : file gambar (jpg/png/webp, max 5MB)
 *   user_id: id user yang akan diupdate fotonya
 *
 * GET ?action=get&user_id=1 → ambil URL foto profil user
 */

require_once __DIR__ . '/config.php';


function ensureFotoColumn(): void {
    try {
        db()->query("SELECT foto_profil FROM users LIMIT 1");
    } catch (PDOException $e) {
        db()->exec("ALTER TABLE users ADD COLUMN foto_profil VARCHAR(500) DEFAULT NULL");
    }
}
ensureFotoColumn();

$action = $_GET['action'] ?? $_POST['action'] ?? 'upload';
$method = $_SERVER['REQUEST_METHOD'];


if ($method === 'GET' && $action === 'get') {
    $userId = (int)($_GET['user_id'] ?? 0);
    if (!$userId) jsonErr('user_id wajib diisi');

    $stmt = db()->prepare("SELECT foto_profil FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $row = $stmt->fetch();
    if (!$row) jsonErr('User tidak ditemukan', 404);

    jsonOut(['ok' => true, 'foto_url' => $row['foto_profil']]);
}


if ($method !== 'POST') jsonErr('Method not allowed', 405);

$userId = (int)($_POST['user_id'] ?? 0);
if (!$userId) jsonErr('user_id wajib diisi');


$chk = db()->prepare("SELECT id, foto_profil FROM users WHERE id = ?");
$chk->execute([$userId]);
$user = $chk->fetch();
if (!$user) jsonErr('User tidak ditemukan', 404);


if (empty($_FILES['foto']) || $_FILES['foto']['error'] === UPLOAD_ERR_NO_FILE) {
    jsonErr('File foto wajib dikirim');
}

$file = $_FILES['foto'];


if ($file['error'] !== UPLOAD_ERR_OK) {
    jsonErr('Upload gagal, kode: ' . $file['error']);
}


$maxSize = 5 * 1024 * 1024;
if ($file['size'] > $maxSize) {
    jsonErr('Ukuran foto maksimal 5 MB');
}


$finfo    = new finfo(FILEINFO_MIME_TYPE);
$mimeReal = $finfo->file($file['tmp_name']);
if (!in_array($mimeReal, ['image/jpeg','image/jpg','image/png','image/webp'], true)) {
    jsonErr('Format tidak didukung. Gunakan JPG, PNG, atau WebP');
}


$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, ['jpg','jpeg','png','webp'], true)) {
    jsonErr("Ekstensi .{$ext} tidak diizinkan");
}


$subDir  = 'profil/';
$fullDir = UPLOAD_DIR . $subDir;
if (!is_dir($fullDir) && !@mkdir($fullDir, 0755, true)) {
    jsonErr('Gagal membuat direktori. Cek permission folder uploads/');
}


if (!empty($user['foto_profil'])) {
    $oldFile = UPLOAD_DIR . str_replace(UPLOAD_URL, '', $user['foto_profil']);
    if (file_exists($oldFile)) @unlink($oldFile);
}


$safeName = 'profil_' . $userId . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
$destPath = $fullDir . $safeName;

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    jsonErr('Gagal menyimpan file. Cek permission folder uploads/');
}


$urlPub = UPLOAD_URL . $subDir . $safeName;


$stmt = db()->prepare("UPDATE users SET foto_profil = ? WHERE id = ?");
$stmt->execute([$urlPub, $userId]);

jsonOut([
    'ok'       => true,
    'message'  => 'Foto profil berhasil diperbarui',
    'foto_url' => $urlPub,
]);
