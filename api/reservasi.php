<?php
/**
 * api/reservasi.php — Backend API Sistem Reservasi
 * Sensasion
 *
 * Logika Gazebo:
 *  - Kapasitas harian = 26 gazebo
 *  - Slot dihitung dari SUM(jumlah_pengunjung) reservasi aktif per tanggal
 *  - Reset otomatis per hari (tanggal baru = 26 slot penuh)
 *
 * Endpoints:
 *  GET  ?action=cek_tanggal&tanggal=YYYY-MM-DD  → cek ketersediaan + sisa slot
 *  GET  ?action=list                              → semua reservasi (admin)
 *  GET  ?action=detail&kode=XXX                  → detail by kode
 *  GET  ?action=by_phone&telepon=XXX             → reservasi by nomor HP
 *  GET  ?action=tanggal_blokir                   → daftar tanggal penuh
 *  POST ?action=buat                             → buat reservasi baru
 *  POST ?action=update_status                    → update status (admin)
 *  GET  ?action=scan&kode=XXX                    → scan QR (admin)
 */

require_once __DIR__ . '/config.php';


define('KAPASITAS_GAZEBO', 26);


function ensureTable(): void {
    $sql = "CREATE TABLE IF NOT EXISTS reservasi (
        id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        kode_reservasi    VARCHAR(20)  NOT NULL UNIQUE,
        nama              VARCHAR(150) NOT NULL,
        telepon           VARCHAR(20)  NOT NULL,
        tanggal_kunjungan DATE         NOT NULL,
        jumlah_pengunjung INT UNSIGNED NOT NULL DEFAULT 1,
        jenis_kunjungan   VARCHAR(100) NOT NULL DEFAULT 'Umum',
        catatan           TEXT         DEFAULT NULL,
        qr_path           VARCHAR(512) DEFAULT NULL,
        status            ENUM('menunggu','disetujui','selesai','dibatalkan') NOT NULL DEFAULT 'menunggu',
        dibuat_pada       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        diubah_pada       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_tanggal (tanggal_kunjungan),
        INDEX idx_kode    (kode_reservasi),
        INDEX idx_status  (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    db()->exec($sql);
}
ensureTable();

function hitungSlotTerpakai(string $tanggal): int {
    $stmt = db()->prepare(
        "SELECT COALESCE(SUM(jumlah_pengunjung), 0)
         FROM reservasi
         WHERE tanggal_kunjungan = ? AND status NOT IN ('dibatalkan')"
    );
    $stmt->execute([$tanggal]);
    return (int)$stmt->fetchColumn();
}


function generateKode(): string {
    $prefix = 'SNS';
    $date   = date('ymd');
    $rand   = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 5));
    return $prefix . $date . $rand;
}


function generateQR(string $kode, string $projectRoot): ?string {
    $qrDir = $projectRoot . '/public/qrcodes/';
    if (!is_dir($qrDir)) @mkdir($qrDir, 0755, true);

    $filename = 'qr_' . $kode . '.png';
    $filepath = $qrDir . $filename;

    if (file_exists($filepath)) return 'public/qrcodes/' . $filename;

    $data  = urlencode('SNS-RESERVASI:' . $kode);
    $url   = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={$data}&format=png&ecc=M&margin=10";

    $ctx = stream_context_create(['http' => ['timeout' => 8]]);
    $img = @file_get_contents($url, false, $ctx);

    if ($img && strlen($img) > 100) {
        file_put_contents($filepath, $img);
        return 'public/qrcodes/' . $filename;
    }
    return null;
}


$action = $_GET['action'] ?? $_POST['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

$body = [];
if ($method === 'POST') {
    $raw = file_get_contents('php://input');
    if ($raw) $body = json_decode($raw, true) ?? [];
    $body = array_merge($_POST, $body);
}

switch ($action) {


    case 'cek_tanggal':
        $tanggal = $_GET['tanggal'] ?? '';
        if (!$tanggal || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
            jsonErr('Parameter tanggal tidak valid');
        }


        $terpakai  = hitungSlotTerpakai($tanggal);
        $kapasitas = KAPASITAS_GAZEBO;
        $sisa      = max(0, $kapasitas - $terpakai);

        jsonOut([
            'ok'        => true,
            'tersedia'  => $sisa > 0,
            'terisi'    => $terpakai,   
            'sisa'      => $sisa,       
            'kapasitas' => $kapasitas,  
            'tanggal'   => $tanggal,
        ]);
        break;

    
    case 'buat':
        if ($method !== 'POST') jsonErr('Metode harus POST', 405);

        $nama    = trim($body['nama']    ?? '');
        $telp    = trim($body['telepon'] ?? '');
        $tgl     = trim($body['tanggal_kunjungan'] ?? '');
        $jml     = (int)($body['jumlah_pengunjung'] ?? 1);
        $jenis   = trim($body['jenis_kunjungan']    ?? 'Umum');
        $catatan = trim($body['catatan']  ?? '');

        if (!$nama || !$telp || !$tgl) jsonErr('Nama, telepon, dan tanggal wajib diisi');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl)) jsonErr('Format tanggal tidak valid');
        if ($tgl < date('Y-m-d')) jsonErr('Tanggal tidak boleh di masa lalu');
        if ($jml < 1 || $jml > KAPASITAS_GAZEBO) jsonErr('Jumlah gazebo tidak valid (1–' . KAPASITAS_GAZEBO . ' unit)');


        $terpakai = hitungSlotTerpakai($tgl);
        $sisa     = KAPASITAS_GAZEBO - $terpakai;

        if ($sisa <= 0) {
            jsonErr('Semua gazebo sudah dipesan untuk tanggal ' . $tgl . '. Pilih tanggal lain.', 409);
        }
        if ($jml > $sisa) {
            jsonErr("Hanya tersisa $sisa gazebo untuk tanggal $tgl. Kurangi jumlah gazebo Anda.", 409);
        }


        $kode = '';
        for ($i = 0; $i < 5; $i++) {
            $kode = generateKode();
            $chk  = db()->prepare("SELECT id FROM reservasi WHERE kode_reservasi = ?");
            $chk->execute([$kode]);
            if (!$chk->fetch()) break;
        }


        $projectRoot = realpath(__DIR__ . '/..');
        $qrPath = generateQR($kode, $projectRoot);


        $ins = db()->prepare(
            "INSERT INTO reservasi (kode_reservasi, nama, telepon, tanggal_kunjungan, jumlah_pengunjung, jenis_kunjungan, catatan, qr_path)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $ins->execute([$kode, $nama, $telp, $tgl, $jml, $jenis, $catatan ?: null, $qrPath]);
        $id = db()->lastInsertId();


        $sel = db()->prepare("SELECT * FROM reservasi WHERE id = ?");
        $sel->execute([$id]);
        $row = $sel->fetch();

        $projectName  = basename($projectRoot);
        $row['qr_url'] = $qrPath
            ? '/' . $projectName . '/' . $qrPath
            : null;


        $sisaSetelah = max(0, KAPASITAS_GAZEBO - hitungSlotTerpakai($tgl));
        $row['sisa_slot'] = $sisaSetelah;

        jsonOut(['ok' => true, 'message' => 'Reservasi berhasil dibuat!', 'data' => $row]);
        break;


    case 'list':
        $status = $_GET['status'] ?? '';
        $tgl    = $_GET['tanggal'] ?? '';
        $q      = $_GET['q'] ?? '';

        $where  = ['1=1'];
        $params = [];

        if ($status) { $where[] = 'status = ?'; $params[] = $status; }
        if ($tgl)    { $where[] = 'tanggal_kunjungan = ?'; $params[] = $tgl; }
        if ($q) {
            $where[] = '(nama LIKE ? OR kode_reservasi LIKE ? OR telepon LIKE ?)';
            $like = '%' . $q . '%';
            $params = array_merge($params, [$like, $like, $like]);
        }

        $sql  = 'SELECT * FROM reservasi WHERE ' . implode(' AND ', $where) . ' ORDER BY dibuat_pada DESC';
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $projectName = basename(realpath(__DIR__ . '/..'));
        foreach ($rows as &$r) {
            $r['qr_url'] = $r['qr_path'] ? '/' . $projectName . '/' . $r['qr_path'] : null;
        }

        jsonOut(['ok' => true, 'data' => $rows, 'total' => count($rows)]);
        break;


    case 'detail':
    case 'scan':
        $kode = trim($_GET['kode'] ?? $_POST['kode'] ?? $body['kode'] ?? '');
        if (!$kode) jsonErr('Kode reservasi wajib diisi');

        $stmt = db()->prepare("SELECT * FROM reservasi WHERE kode_reservasi = ?");
        $stmt->execute([strtoupper($kode)]);
        $row = $stmt->fetch();

        if (!$row) jsonErr('Reservasi dengan kode "' . htmlspecialchars($kode) . '" tidak ditemukan', 404);

        $projectName  = basename(realpath(__DIR__ . '/..'));
        $row['qr_url'] = $row['qr_path'] ? '/' . $projectName . '/' . $row['qr_path'] : null;

        jsonOut(['ok' => true, 'data' => $row]);
        break;

    
    case 'update_status':
        if ($method !== 'POST') jsonErr('Metode harus POST', 405);

        $id_or_kode = $body['id'] ?? $body['kode'] ?? '';
        $newStatus  = $body['status'] ?? '';
        $allowed    = ['menunggu','disetujui','selesai','dibatalkan'];

        if (!$id_or_kode) jsonErr('ID atau kode reservasi wajib');
        if (!in_array($newStatus, $allowed)) jsonErr('Status tidak valid');

        if (is_numeric($id_or_kode)) {
            $stmt = db()->prepare("UPDATE reservasi SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, (int)$id_or_kode]);
        } else {
            $stmt = db()->prepare("UPDATE reservasi SET status = ? WHERE kode_reservasi = ?");
            $stmt->execute([$newStatus, strtoupper($id_or_kode)]);
        }

        if ($stmt->rowCount() === 0) jsonErr('Reservasi tidak ditemukan', 404);

        jsonOut(['ok' => true, 'message' => 'Status berhasil diperbarui']);
        break;

    
    case 'by_phone':
        $telepon = trim($_GET['telepon'] ?? '');
        if (!$telepon) jsonErr('Nomor telepon wajib diisi');

        $telepon = preg_replace('/\s+/', '', $telepon);
        $alt = preg_replace('/^0/', '62', $telepon);

        $stmt = db()->prepare(
            "SELECT * FROM reservasi WHERE telepon = ? OR telepon = ? ORDER BY dibuat_pada DESC LIMIT 20"
        );
        $stmt->execute([$telepon, $alt]);
        $rows = $stmt->fetchAll();

        if (empty($rows)) jsonErr('Tidak ada reservasi dengan nomor telepon tersebut', 404);

        $projectName = basename(realpath(__DIR__ . '/..'));
        foreach ($rows as &$r) {
            $r['qr_url'] = $r['qr_path'] ? '/' . $projectName . '/' . $r['qr_path'] : null;
        }

        jsonOut(['ok' => true, 'data' => $rows, 'total' => count($rows)]);
        break;


    case 'tanggal_blokir':
        $stmt = db()->query(
            "SELECT tanggal_kunjungan, SUM(jumlah_pengunjung) as total_gazebo
             FROM reservasi
             WHERE status NOT IN ('dibatalkan') AND tanggal_kunjungan >= CURDATE()
             GROUP BY tanggal_kunjungan
             HAVING total_gazebo >= " . KAPASITAS_GAZEBO
        );
        $rows  = $stmt->fetchAll();
        $dates = array_column($rows, 'tanggal_kunjungan');
        jsonOut(['ok' => true, 'tanggal_blokir' => $dates]);
        break;

    default:
        jsonErr('Action tidak dikenali: ' . htmlspecialchars($action), 400);
}
