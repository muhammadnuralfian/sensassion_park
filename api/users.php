<?php
/**
 * api/users.php — API Manajemen User Admin
 * Sensasion
 *
 * Endpoints:
 *  GET  ?action=list                        → daftar semua user
 *  POST ?action=tambah                      → tambah user baru
 *  POST ?action=edit                        → edit user (id, nama, email, role)
 *  POST ?action=hapus                       → hapus user (id)
 *  POST ?action=reset_password              → reset password (id, password_baru)
 *  POST ?action=login                       → login admin
 *  POST ?action=logout                      → logout
 */

require_once __DIR__ . '/config.php';


function ensureUsersTable(): void {
    db()->exec("CREATE TABLE IF NOT EXISTS users (
        id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        nama        VARCHAR(150) NOT NULL,
        email       VARCHAR(200) NOT NULL UNIQUE,
        password    VARCHAR(255) NOT NULL,
        role        ENUM('admin') NOT NULL DEFAULT 'admin',
        status      ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
        last_login  DATETIME DEFAULT NULL,
        dibuat_pada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    
    $stmt = db()->query("SELECT COUNT(*) FROM users");
    if ((int)$stmt->fetchColumn() === 0) {
        $hash = password_hash('sensasion2024', PASSWORD_BCRYPT);
        db()->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)")
             ->execute(['Administrator', 'admin@sensasion.id', $hash, 'admin']);
    }
}
ensureUsersTable();

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

$body = [];
if ($method === 'POST') {
    $raw = file_get_contents('php://input');
    if ($raw) $body = json_decode($raw, true) ?? [];
    $body = array_merge($_POST, $body);
}

switch ($action) {

    
    case 'list':
        $stmt = db()->query("SELECT id, nama, email, role, status, last_login, dibuat_pada FROM users ORDER BY dibuat_pada DESC");
        jsonOut(['ok' => true, 'data' => $stmt->fetchAll()]);
        break;

    
    case 'tambah':
        $nama  = trim($body['nama']  ?? '');
        $email = trim($body['email'] ?? '');
        $pass  = trim($body['password'] ?? '');

        if (!$nama || !$email || !$pass) jsonErr('Nama, email, dan password wajib diisi');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) jsonErr('Format email tidak valid');
        if (strlen($pass) < 6) jsonErr('Password minimal 6 karakter');

        
        $chk = db()->prepare("SELECT id FROM users WHERE email = ?");
        $chk->execute([$email]);
        if ($chk->fetch()) jsonErr('Email sudah digunakan');

        $hash = password_hash($pass, PASSWORD_BCRYPT);
        db()->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, 'admin')")
             ->execute([$nama, $email, $hash]);

        jsonOut(['ok' => true, 'message' => 'User berhasil ditambahkan']);
        break;

    
    case 'edit':
        $id    = (int)($body['id'] ?? 0);
        $nama  = trim($body['nama']  ?? '');
        $email = trim($body['email'] ?? '');
        $status = trim($body['status'] ?? '');

        if (!$id || !$nama || !$email) jsonErr('ID, nama, dan email wajib diisi');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) jsonErr('Format email tidak valid');
        if (!in_array($status, ['aktif','nonaktif'])) jsonErr('Status tidak valid');

        
        $chk = db()->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $chk->execute([$email, $id]);
        if ($chk->fetch()) jsonErr('Email sudah digunakan user lain');

        $stmt = db()->prepare("UPDATE users SET nama=?, email=?, status=? WHERE id=?");
        $stmt->execute([$nama, $email, $status, $id]);

        if ($stmt->rowCount() === 0) jsonErr('User tidak ditemukan', 404);
        jsonOut(['ok' => true, 'message' => 'User berhasil diperbarui']);
        break;

    
    case 'hapus':
        $id = (int)($body['id'] ?? 0);
        if (!$id) jsonErr('ID wajib diisi');

        
        $cntAdmin = (int)db()->query("SELECT COUNT(*) FROM users WHERE status='aktif'")->fetchColumn();
        if ($cntAdmin <= 1) {
            $target = db()->prepare("SELECT id FROM users WHERE id=?");
            $target->execute([$id]);
            if ($target->fetch()) jsonErr('Tidak bisa menghapus satu-satunya admin aktif');
        }

        $check = db()->prepare("SELECT id FROM users WHERE id=?");
        $check->execute([$id]);
        if (!$check->fetch()) jsonErr('User tidak ditemukan', 404);

        db()->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
        jsonOut(['ok' => true, 'message' => 'User berhasil dihapus']);
        break;

    
    case 'login':
        $input = trim($body['email'] ?? $body['username'] ?? '');
        $pass  = $body['password'] ?? '';

        if (!$input || !$pass) jsonErr('Email/username dan password wajib diisi');

        
        $stmt = db()->prepare(
            "SELECT * FROM users WHERE (email = ? OR nama = ?) AND status = 'aktif' LIMIT 1"
        );
        $stmt->execute([$input, $input]);
        $user = $stmt->fetch();

        if (!$user) {
            jsonErr('Akun tidak ditemukan atau tidak aktif', 401);
        }

        if (!password_verify($pass, $user['password'])) {
            jsonErr('Password salah', 401);
        }

        
        db()->prepare("UPDATE users SET last_login=NOW() WHERE id=?")->execute([$user['id']]);

        unset($user['password']);
        jsonOut(['ok' => true, 'message' => 'Login berhasil', 'user' => $user]);
        break;


    case 'reset_admin':
        $hash = password_hash('sensasion2024', PASSWORD_BCRYPT);
        $affected = db()->prepare("UPDATE users SET password = ? WHERE role = 'admin'");
        $affected->execute([$hash]);
        $count = $affected->rowCount();
        if ($count === 0) {
            
            db()->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)")
                 ->execute(['Administrator', 'admin@sensasion.id', $hash, 'admin']);
            jsonOut(['ok' => true, 'message' => 'Admin baru dibuat. Login: admin@sensasion.id / sensasion2024']);
        }
        jsonOut(['ok' => true, 'message' => "Password $count akun admin direset ke: sensasion2024"]);
        break;

    default:
        jsonErr('Action tidak dikenali: ' . htmlspecialchars($action), 400);
}
