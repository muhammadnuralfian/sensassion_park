<?php

ob_start();
require_once __DIR__ . '/config.php';
ob_end_clean();

$method = $_SERVER['REQUEST_METHOD'];


if ($method === 'POST') {
    $body   = json_decode(file_get_contents('php://input'), true) ?? [];
    $hal    = mb_substr(trim($body['halaman'] ?? 'beranda'), 0, 100);
    db()->prepare('INSERT INTO kunjungan (halaman) VALUES (:h)')->execute([':h' => $hal]);
    jsonOut(['ok' => true]);
}


if ($method === 'GET') {

    
    if (!empty($_GET['mode']) && $_GET['mode'] === 'rekomen') {
        $rows = db()->query(
            "SELECT HOUR(waktu_kunjungan) AS jam, COUNT(*) AS total
             FROM kunjungan
             WHERE waktu_kunjungan >= NOW() - INTERVAL 7 DAY
             GROUP BY jam ORDER BY jam"
        )->fetchAll();

        $perJam = array_fill(0, 24, 0);
        foreach ($rows as $r) $perJam[(int)$r['jam']] = (int)$r['total'];
        $maxVal = max($perJam) ?: 1;

        
        $padat = array_map(fn($v) => round($v / $maxVal * 100), $perJam);


        $bestRenang  = getBestWindow($padat, 7, 17, 2);
        
        $bestMancing = getBestWindow($padat, 6, 18, 2, [11,12,13]); // hindari jam 11-13 (terik)

        jsonOut([
            'ok'         => true,
            'padat'      => $padat,
            'renang'     => $bestRenang,
            'mancing'    => $bestMancing,
        ]);
    }

    
    $rows = db()->query(
        "SELECT HOUR(waktu_kunjungan) AS jam, COUNT(*) AS total
         FROM kunjungan
         WHERE waktu_kunjungan >= NOW() - INTERVAL 7 DAY
         GROUP BY jam ORDER BY jam"
    )->fetchAll();

    $data = array_fill(0, 24, 0);
    foreach ($rows as $r) $data[(int)$r['jam']] = (int)$r['total'];

    
    $hariIni = (int)db()->query(
        "SELECT COUNT(*) FROM kunjungan WHERE DATE(waktu_kunjungan) = CURDATE()"
    )->fetchColumn();

    
    $jamSibuk = db()->query(
        "SELECT HOUR(waktu_kunjungan) AS jam, COUNT(*) AS total
         FROM kunjungan WHERE DATE(waktu_kunjungan) = CURDATE()
         GROUP BY jam ORDER BY total DESC LIMIT 1"
    )->fetch();

    jsonOut([
        'ok'        => true,
        'per_jam'   => $data,
        'hari_ini'  => $hariIni,
        'jam_sibuk' => $jamSibuk ? (int)$jamSibuk['jam'] : null,
        'labels'    => array_map(fn($h) => sprintf('%02d:00', $h), range(0, 23)),
    ]);
}

jsonErr('Method tidak didukung', 405);

function getBestWindow(array $padat, int $start, int $end, int $window, array $hindari = []): array {
    $best = null;
    $bestScore = PHP_INT_MAX;
    for ($h = $start; $h <= $end - $window; $h++) {
        
        $skip = false;
        foreach ($hindari as $hj) {
            if ($hj >= $h && $hj < $h + $window) { $skip = true; break; }
        }
        if ($skip) continue;

        $score = 0;
        for ($i = 0; $i < $window; $i++) $score += $padat[$h + $i];
        if ($score < $bestScore) {
            $bestScore = $score;
            $best = ['jam_mulai' => $h, 'jam_selesai' => $h + $window, 'skor_padat' => $score];
        }
    }
    if (!$best) $best = ['jam_mulai' => $start, 'jam_selesai' => $start + $window, 'skor_padat' => 0];
    $best['label'] = sprintf('%02d:00–%02d:00', $best['jam_mulai'], $best['jam_selesai']);
    return $best;
}
