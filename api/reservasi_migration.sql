-- ============================================================
-- reservasi_migration.sql
-- Sensasion — Sistem Reservasi Gazebo
--
-- Logika slot:
--   - Kapasitas harian = 26 gazebo (KAPASITAS_GAZEBO di reservasi.php)
--   - Slot dihitung dari SUM(jumlah_pengunjung) per tanggal_kunjungan
--   - Reset otomatis setiap hari (karena berbasis tanggal, bukan counter)
--   - Jika user A pesan 3 gazebo, user B hanya bisa pesan maks 23
-- ============================================================

USE sensasion;

-- ===== TABEL RESERVASI =====
CREATE TABLE IF NOT EXISTS reservasi (
  id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kode_reservasi    VARCHAR(20)  NOT NULL UNIQUE,
  nama              VARCHAR(150) NOT NULL,
  telepon           VARCHAR(20)  NOT NULL,
  tanggal_kunjungan DATE         NOT NULL,
  -- jumlah_pengunjung = jumlah GAZEBO yang dipesan (1–26)
  jumlah_pengunjung INT UNSIGNED NOT NULL DEFAULT 1,
  jenis_kunjungan   VARCHAR(100) NOT NULL DEFAULT 'Umum',
  catatan           TEXT         DEFAULT NULL,
  qr_path           VARCHAR(512) DEFAULT NULL,
  status            ENUM('menunggu','disetujui','selesai','dibatalkan') NOT NULL DEFAULT 'menunggu',
  dibuat_pada       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  diubah_pada       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_tanggal  (tanggal_kunjungan),
  INDEX idx_kode     (kode_reservasi),
  INDEX idx_status   (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===== VIEW: sisa slot per tanggal (opsional, untuk monitoring) =====
-- Berguna untuk admin melihat kapasitas harian di satu query
CREATE OR REPLACE VIEW v_sisa_slot AS
SELECT
  tanggal_kunjungan,
  26 AS kapasitas,
  COALESCE(SUM(jumlah_pengunjung), 0) AS terpakai,
  26 - COALESCE(SUM(jumlah_pengunjung), 0) AS sisa
FROM reservasi
WHERE status NOT IN ('dibatalkan')
  AND tanggal_kunjungan >= CURDATE()
GROUP BY tanggal_kunjungan;
