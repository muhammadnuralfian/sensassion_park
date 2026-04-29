<?php


ob_start();
require_once __DIR__ . '/config.php';
ob_end_clean();

$method = $_SERVER['REQUEST_METHOD'];


function saveFasilitasImage(array $file): array {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'msg' => 'Upload gambar gagal.'];
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        return ['ok' => false, 'msg' => 'Ukuran gambar terlalu besar.'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    if (!in_array($mime, ALLOWED_MIME, true)) {
        return ['ok' => false, 'msg' => 'Format gambar tidak didukung.'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXT, true)) {
        return ['ok' => false, 'msg' => 'Ekstensi file tidak diizinkan.'];
    }

    $subDir  = 'fasilitas/';
    $fullDir = UPLOAD_DIR . $subDir;
    if (!is_dir($fullDir) && !@mkdir($fullDir, 0755, true)) {
        return ['ok' => false, 'msg' => 'Folder upload tidak bisa dibuat.'];
    }

    $safeName = bin2hex(random_bytes(8)) . '_' . preg_replace('/[^a-z0-9._-]/', '_', strtolower($file['name']));
    $destPath = $fullDir . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        return ['ok' => false, 'msg' => 'Gagal menyimpan gambar.'];
    }

    return [
        'ok'   => true,
        'path' => $subDir . $safeName
    ];
}

/* ===== Helper baca body JSON ===== */
function getJsonBody(): array {
    $raw = file_get_contents('php://input');
    return json_decode($raw, true) ?? [];
}

/* ===== GET ===== */
if ($method === 'GET') {
    if (!empty($_GET['id'])) {
        $stmt = db()->prepare('SELECT * FROM fasilitas WHERE id = :id');
        $stmt->execute([':id' => (int)$_GET['id']]);
        $row = $stmt->fetch();

        if (!$row) jsonErr('Fasilitas tidak ditemukan', 404);

        if (!empty($row['gambar'])) {
            $row['gambar_url'] = UPLOAD_URL . $row['gambar'];
        }

        jsonOut(['ok' => true, 'data' => $row]);
    }

    $stmt = db()->query('SELECT * FROM fasilitas ORDER BY urutan ASC, id ASC');
    $rows = $stmt->fetchAll();

    foreach ($rows as &$row) {
        if (!empty($row['gambar'])) {
            $row['gambar_url'] = UPLOAD_URL . $row['gambar'];
        }
    }
    unset($row);

    jsonOut(['ok' => true, 'total' => count($rows), 'data' => $rows]);
}

/* ===== POST ===== */
if ($method === 'POST') {
    $nama   = trim($_POST['nama_fasilitas'] ?? '');
    $desk   = trim($_POST['deskripsi'] ?? '');
    $urutan = (int)($_POST['urutan'] ?? 0);
    $gambar = '';

    if ($nama === '') jsonErr('Nama fasilitas wajib diisi');

    if (!empty($_FILES['gambar']) && $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE) {
        $up = saveFasilitasImage($_FILES['gambar']);
        if (!$up['ok']) jsonErr($up['msg']);
        $gambar = $up['path'];
    }

    $stmt = db()->prepare(
        'INSERT INTO fasilitas (nama_fasilitas, deskripsi, gambar, urutan)
         VALUES (:nama, :deskripsi, :gambar, :urutan)'
    );
    $stmt->execute([
        ':nama'      => $nama,
        ':deskripsi' => $desk,
        ':gambar'    => $gambar,
        ':urutan'    => $urutan
    ]);

    jsonOut([
        'ok'  => true,
        'msg' => 'Fasilitas berhasil ditambahkan',
        'id'  => (int)db()->lastInsertId()
    ], 201);
}

/* ===== PUT ===== */
if ($method === 'PUT') {
    $body   = getJsonBody();
    $id     = (int)($body['id'] ?? 0);
    $nama   = trim($body['nama_fasilitas'] ?? '');
    $desk   = trim($body['deskripsi'] ?? '');
    $gambar = trim($body['gambar'] ?? '');
    $urutan = (int)($body['urutan'] ?? 0);

    if (!$id) jsonErr('ID wajib diisi');
    if ($nama === '') jsonErr('Nama fasilitas wajib diisi');

    $stmt = db()->prepare(
        'UPDATE fasilitas
         SET nama_fasilitas = :nama,
             deskripsi = :deskripsi,
             gambar = :gambar,
             urutan = :urutan,
             diubah_pada = NOW()
         WHERE id = :id'
    );
    $stmt->execute([
        ':id'        => $id,
        ':nama'      => $nama,
        ':deskripsi' => $desk,
        ':gambar'    => $gambar,
        ':urutan'    => $urutan
    ]);

    jsonOut(['ok' => true, 'msg' => 'Fasilitas berhasil diperbarui']);
}

/* ===== DELETE ===== */
if ($method === 'DELETE') {
    $body = getJsonBody();
    $id   = (int)($body['id'] ?? ($_GET['id'] ?? 0));

    if (!$id) jsonErr('ID wajib diisi');

    $stmt = db()->prepare('SELECT gambar FROM fasilitas WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();

    if (!$row) jsonErr('Fasilitas tidak ditemukan', 404);

    db()->prepare('DELETE FROM fasilitas WHERE id = :id')->execute([':id' => $id]);

    if (!empty($row['gambar'])) {
        $filePath = UPLOAD_DIR . $row['gambar'];
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
    }

    jsonOut(['ok' => true, 'msg' => 'Fasilitas berhasil dihapus']);
}

jsonErr('Method tidak didukung', 405);