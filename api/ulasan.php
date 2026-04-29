<?php


ob_start();
require_once __DIR__ . '/config.php';
ob_end_clean();

$method = $_SERVER['REQUEST_METHOD'];


if ($method === 'GET') {
    $sql    = 'SELECT id, nama, komentar, rating, tanggal, tampil FROM ulasan WHERE 1=1';
    $params = [];
    if (empty($_GET['all'])) {
        $sql .= ' AND tampil = 1';
    }
    $sql .= ' ORDER BY tanggal DESC';
    if (!empty($_GET['limit'])) {
        $sql .= ' LIMIT ' . max(1, min(100, (int)$_GET['limit']));
    }
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    foreach ($rows as &$r) {
        $r['rating'] = (int)$r['rating'];
        $r['tampil'] = (bool)$r['tampil'];
        $r['tanggal_fmt'] = date('d M Y', strtotime($r['tanggal']));
        $r['bintang'] = str_repeat('★', $r['rating']) . str_repeat('☆', 5 - $r['rating']);
    }
    unset($r);
    $avg = count($rows) ? round(array_sum(array_column($rows,'rating')) / count($rows), 1) : 0;
    jsonOut(['ok' => true, 'total' => count($rows), 'rata_rating' => $avg, 'data' => $rows]);
}

$body = [];
if (in_array($method, ['POST','PUT','DELETE','PATCH'], true)) {
    $raw  = file_get_contents('php://input');
    $body = json_decode($raw, true) ?? [];
}

/* ===== POST — Tambah ulasan baru ===== */
if ($method === 'POST') {
    $nama     = trim($body['nama']     ?? '');
    $komentar = trim($body['komentar'] ?? '');
    $rating   = (int)($body['rating'] ?? 5);

    if (!$nama)     jsonErr('Nama tidak boleh kosong');
    if (!$komentar) jsonErr('Komentar tidak boleh kosong');
    if ($rating < 1 || $rating > 5) jsonErr('Rating harus 1–5');

    $nama     = mb_substr($nama, 0, 100);
    $komentar = mb_substr($komentar, 0, 1000);

    $stmt = db()->prepare(
        'INSERT INTO ulasan (nama, komentar, rating) VALUES (:nama, :komentar, :rating)'
    );
    $stmt->execute([':nama' => $nama, ':komentar' => $komentar, ':rating' => $rating]);
    $id = (int)db()->lastInsertId();

    $row = db()->prepare('SELECT * FROM ulasan WHERE id = :id');
    $row->execute([':id' => $id]);
    $data = $row->fetch();
    $data['tanggal_fmt'] = date('d M Y', strtotime($data['tanggal']));
    $data['bintang']     = str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);

    jsonOut(['ok' => true, 'msg' => 'Ulasan berhasil dikirim!', 'data' => $data], 201);
}

/* ===== PUT — Toggle tampil / update ===== */
if ($method === 'PUT') {
    $id     = (int)($body['id'] ?? 0);
    $tampil = isset($body['tampil']) ? (int)(bool)$body['tampil'] : null;
    if (!$id) jsonErr('ID wajib diisi');
    if ($tampil !== null) {
        db()->prepare('UPDATE ulasan SET tampil = :t WHERE id = :id')
            ->execute([':t' => $tampil, ':id' => $id]);
    }
    jsonOut(['ok' => true, 'msg' => 'Ulasan diperbarui']);
}

/* ===== DELETE ===== */
if ($method === 'DELETE') {
    $id = (int)($body['id'] ?? 0);
    if (!$id) jsonErr('ID wajib diisi');
    db()->prepare('DELETE FROM ulasan WHERE id = :id')->execute([':id' => $id]);
    jsonOut(['ok' => true, 'msg' => 'Ulasan dihapus']);
}

jsonErr('Method tidak didukung', 405);
