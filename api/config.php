<?php




$projectRoot = realpath(__DIR__ . '/..');          
$projectName = basename($projectRoot);             


define('DB_HOST',    'localhost');
define('DB_NAME', 'sensasion'); 
define('DB_USER',    'root');            
define('DB_PASS',    '');               
define('DB_CHARSET', 'utf8mb4');




define('UPLOAD_DIR', $projectRoot . '/uploads/');


define('UPLOAD_URL', '/' . $projectName . '/uploads/');

define('MAX_FILE_SIZE', 10 * 1024 * 1024);  // 10 MB
define('ALLOWED_MIME',  ['image/jpeg','image/jpg','image/png','image/webp']);
define('ALLOWED_EXT',   ['jpg','jpeg','png','webp']);



ini_set('display_errors', 0);
error_reporting(E_ALL);
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) return false;
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode([
        'ok'    => false,
        'error' => "PHP Error [{$errno}]: {$errstr} in {$errfile}:{$errline}"
    ]);
    exit;
});
set_exception_handler(function($e) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode([
        'ok'    => false,
        'error' => 'Exception: ' . $e->getMessage()
    ]);
    exit;
});


header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }


function jsonOut($data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
function jsonErr(string $msg, int $code = 400): void {
    jsonOut(['ok' => false, 'error' => $msg], $code);
}


function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        jsonErr(
            'Koneksi database gagal: ' . $e->getMessage() .
            '. Periksa DB_USER, DB_PASS, DB_NAME di api/config.php dan pastikan MySQL menyala.',
            500
        );
    }
    return $pdo;
}


function formatBytes(int $bytes): string {
    if ($bytes < 1024)    return "{$bytes} B";
    if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
    return round($bytes / 1048576, 2) . ' MB';
}


if (!is_dir(UPLOAD_DIR)) {
    @mkdir(UPLOAD_DIR, 0755, true);
}
