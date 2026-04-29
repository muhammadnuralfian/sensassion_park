<?php

ob_start();
require_once __DIR__ . '/config.php';
ob_end_clean();

$method = $_SERVER['REQUEST_METHOD'];


if ($method === 'GET') {

    
    if (!empty($_GET['id'])) {
        $stmt = db()->prepare('SELECT * FROM galeri WHERE id = :id');
        $stmt->execute([':id' => (int)$_GET['id']]);
        $row = $stmt->fetch();
        if (!$row) jsonErr('Foto tidak ditemukan', 404);
        $row['url']        = UPLOAD_URL . $row['path_file'];
        $row['ukuran_fmt'] = formatBytes((int)$row['ukuran']);
        $row['tampil']     = (bool)$row['tampil'];
        jsonOut(['ok' => true, 'data' => $row]);
    }

    
    $sql    = 'SELECT id, judul, deskripsi, kategori, path_file, ukuran, tipe_mime, lebar, tinggi, tampil, urutan, dibuat_pada FROM galeri WHERE 1=1';
    $params = [];

    
    if (empty($_GET['all'])) {
        $sql .= ' AND tampil = 1';
    }

    
    if (!empty($_GET['kategori']) && $_GET['kategori'] !== 'all') {
        $valid = ['pemancingan','renang','fasilitas','aktivitas','umum'];
        if (in_array($_GET['kategori'], $valid, true)) {
            $sql .= ' AND kategori = :kategori';
            $params[':kategori'] = $_GET['kategori'];
        }
    }

    $sql .= ' ORDER BY urutan ASC, dibuat_pada DESC';

    
    if (!empty($_GET['limit'])) {
        $limit  = max(1, min(500, (int)$_GET['limit']));
        $offset = max(0, (int)($_GET['offset'] ?? 0));
        $sql .= " LIMIT {$limit} OFFSET {$offset}";
    }

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    foreach ($rows as &$row) {
        $row['url']        = UPLOAD_URL . $row['path_file'];
        $row['ukuran_fmt'] = formatBytes((int)$row['ukuran']);
        $row['tampil']     = (bool)$row['tampil'];
    }
    unset($row);

    jsonOut(['ok' => true, 'total' => count($rows), 'data' => $rows]);
}


$body = [];
if (in_array($method, ['PUT','DELETE','PATCH'], true)) {
    $rawBody = file_get_contents('php://input');
    $body    = json_decode($rawBody, true) ?? [];
}


if ($method === 'PUT') {
    $id = (int)($body['id'] ?? 0);
    if (!$id) jsonErr('ID wajib diisi');

    $allowed = ['judul','deskripsi','kategori','tampil','urutan'];
    $sets    = [];
    $params  = [':id' => $id];

    foreach ($allowed as $f) {
        if (!array_key_exists($f, $body)) continue;
        $val = $body[$f];
        if ($f === 'kategori') {
            $validKat = ['pemancingan','renang','fasilitas','aktivitas','umum'];
            if (!in_array($val, $validKat, true)) continue;
        }
        if ($f === 'tampil')  $val = (int)(bool)$val;
        if ($f === 'urutan')  $val = (int)$val;
        if ($f === 'judul')   $val = mb_substr(trim($val), 0, 255);
        $sets[]           = "`{$f}` = :{$f}";
        $params[":{$f}"]  = $val;
    }

    if (empty($sets)) jsonErr('Tidak ada field valid untuk diupdate');

    $stmt = db()->prepare('UPDATE galeri SET ' . implode(', ', $sets) . ' WHERE id = :id');
    $stmt->execute($params);

    jsonOut(['ok' => true, 'msg' => 'Berhasil diperbarui']);
}


if ($method === 'PATCH') {
    $id = (int)($body['id'] ?? 0);
    if (!$id) jsonErr('ID wajib diisi');

    $stmt = db()->prepare('UPDATE galeri SET tampil = NOT tampil WHERE id = :id');
    $stmt->execute([':id' => $id]);
    if ($stmt->rowCount() === 0) jsonErr('Foto tidak ditemukan', 404);

    $row    = db()->query("SELECT tampil FROM galeri WHERE id = {$id}")->fetch();
    $tampil = (bool)$row['tampil'];
    jsonOut(['ok' => true, 'tampil' => $tampil, 'msg' => $tampil ? 'Foto ditampilkan' : 'Foto disembunyikan']);
}


if ($method === 'DELETE') {
    $id = (int)($body['id'] ?? 0);
    if (!$id) jsonErr('ID wajib diisi');

    $stmt = db()->prepare('SELECT path_file FROM galeri WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row  = $stmt->fetch();
    if (!$row) jsonErr('Foto tidak ditemukan', 404);

    db()->prepare('DELETE FROM galeri WHERE id = :id')->execute([':id' => $id]);

    $filePath = UPLOAD_DIR . $row['path_file'];
    if (file_exists($filePath)) @unlink($filePath);

    jsonOut(['ok' => true, 'msg' => 'Foto berhasil dihapus']);
}

jsonErr('Method tidak didukung', 405);
