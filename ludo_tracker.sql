-- ============================================================
-- LUDO TRACKER - SQL DUMP
-- Bisa langsung di-import ke MySQL tanpa perlu jalankan migrate
-- Jalankan: mysql -u root -p ludo_tracker < ludo_tracker.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS `ludo_tracker`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `ludo_tracker`;

-- ---- Tabel admins ----
CREATE TABLE IF NOT EXISTS `admins` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username`   VARCHAR(255) NOT NULL UNIQUE,
  `password`   VARCHAR(255) NOT NULL,
  `nama`       VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---- Tabel players ----
CREATE TABLE IF NOT EXISTS `players` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_pemain`  VARCHAR(255) NOT NULL,
  `avatar_color` VARCHAR(10) NOT NULL DEFAULT '#4361ee',
  `created_at`   TIMESTAMP NULL DEFAULT NULL,
  `updated_at`   TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---- Tabel attendance ----
CREATE TABLE IF NOT EXISTS `attendance` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `player_id`    BIGINT UNSIGNED NOT NULL,
  `status_hadir` ENUM('hadir','tidak_hadir') NOT NULL DEFAULT 'tidak_hadir',
  `tanggal`      DATE NOT NULL,
  `created_at`   TIMESTAMP NULL DEFAULT NULL,
  `updated_at`   TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attendance_player_id_tanggal_unique` (`player_id`, `tanggal`),
  CONSTRAINT `attendance_player_id_fk` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---- Tabel matches ----
CREATE TABLE IF NOT EXISTS `matches` (
  `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tanggal_match`  DATE NOT NULL,
  `nomor_match`    INT NOT NULL DEFAULT 1,
  `status_match`   ENUM('berlangsung','selesai') NOT NULL DEFAULT 'berlangsung',
  `waktu_mulai`    TIMESTAMP NULL DEFAULT NULL,
  `waktu_selesai`  TIMESTAMP NULL DEFAULT NULL,
  `created_at`     TIMESTAMP NULL DEFAULT NULL,
  `updated_at`     TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `matches_tanggal_match_index` (`tanggal_match`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---- Tabel match_scores ----
CREATE TABLE IF NOT EXISTS `match_scores` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `match_id`     BIGINT UNSIGNED NOT NULL,
  `player_id`    BIGINT UNSIGNED NOT NULL,
  `skor_keinjek` INT NOT NULL DEFAULT 0,
  `total_skor`   INT NOT NULL DEFAULT 0,
  `posisi`       ENUM('juara','runner_up','ketiga','keempat','none') NOT NULL DEFAULT 'none',
  `created_at`   TIMESTAMP NULL DEFAULT NULL,
  `updated_at`   TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `match_scores_match_id_player_id_unique` (`match_id`, `player_id`),
  CONSTRAINT `match_scores_match_id_fk`  FOREIGN KEY (`match_id`)  REFERENCES `matches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `match_scores_player_id_fk` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---- Tabel daily_photos ----
CREATE TABLE IF NOT EXISTS `daily_photos` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tanggal`     DATE NOT NULL UNIQUE,
  `foto`        VARCHAR(255) NOT NULL,
  `deskripsi`   VARCHAR(255) DEFAULT NULL,
  `created_at`  TIMESTAMP NULL DEFAULT NULL,
  `updated_at`  TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---- Tabel sessions (untuk Laravel session driver) ----
CREATE TABLE IF NOT EXISTS `sessions` (
  `id`            VARCHAR(255) NOT NULL,
  `user_id`       BIGINT UNSIGNED DEFAULT NULL,
  `ip_address`    VARCHAR(45) DEFAULT NULL,
  `user_agent`    TEXT,
  `payload`       LONGTEXT NOT NULL,
  `last_activity` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---- Tabel cache ----
CREATE TABLE IF NOT EXISTS `cache` (
  `key`        VARCHAR(255) NOT NULL,
  `value`      MEDIUMTEXT NOT NULL,
  `expiration` INT NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---- Tabel jobs (queue, opsional) ----
CREATE TABLE IF NOT EXISTS `jobs` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue`        VARCHAR(255) NOT NULL,
  `payload`      LONGTEXT NOT NULL,
  `attempts`     TINYINT UNSIGNED NOT NULL,
  `reserved_at`  INT UNSIGNED DEFAULT NULL,
  `available_at` INT UNSIGNED NOT NULL,
  `created_at`   INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- DATA AWAL (SEEDER)
-- ============================================================

-- Admin: username=admin, password=admin123
INSERT INTO `admins` (`username`, `password`, `nama`, `created_at`, `updated_at`) VALUES
('admin', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', NOW(), NOW());

-- Pemain
INSERT INTO `players` (`nama_pemain`, `avatar_color`, `created_at`, `updated_at`) VALUES
('Budi',  '#ef4444', NOW(), NOW()),
('Sari',  '#3b82f6', NOW(), NOW()),
('Joko',  '#10b981', NOW(), NOW()),
('Dewi',  '#f59e0b', NOW(), NOW()),
('Agus',  '#8b5cf6', NOW(), NOW()),
('Rina',  '#ec4899', NOW(), NOW());

-- Absensi hari ini (semua hadir)
INSERT INTO `attendance` (`player_id`, `status_hadir`, `tanggal`, `created_at`, `updated_at`)
SELECT `id`, 'hadir', CURDATE(), NOW(), NOW() FROM `players`;

-- Contoh pertandingan selesai
INSERT INTO `matches` (`tanggal_match`, `nomor_match`, `status_match`, `waktu_mulai`, `waktu_selesai`, `created_at`, `updated_at`) VALUES
(CURDATE(), 1, 'selesai', DATE_SUB(NOW(), INTERVAL 2 HOUR), DATE_SUB(NOW(), INTERVAL 1 HOUR), NOW(), NOW());

-- Skor pertandingan 1
INSERT INTO `match_scores` (`match_id`, `player_id`, `skor_keinjek`, `total_skor`, `posisi`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 15, 'juara',     NOW(), NOW()),
(1, 2, 3, 12, 'runner_up', NOW(), NOW()),
(1, 3, 5, 8,  'ketiga',    NOW(), NOW()),
(1, 4, 7, 4,  'keempat',   NOW(), NOW());

-- Pertandingan sedang berlangsung
INSERT INTO `matches` (`tanggal_match`, `nomor_match`, `status_match`, `waktu_mulai`, `waktu_selesai`, `created_at`, `updated_at`) VALUES
(CURDATE(), 2, 'berlangsung', DATE_SUB(NOW(), INTERVAL 30 MINUTE), NULL, NOW(), NOW());

-- Skor pertandingan 2 (sedang berlangsung)
INSERT INTO `match_scores` (`match_id`, `player_id`, `skor_keinjek`, `total_skor`, `posisi`, `created_at`, `updated_at`) VALUES
(2, 3, 1, 5, 'none', NOW(), NOW()),
(2, 4, 2, 3, 'none', NOW(), NOW()),
(2, 5, 0, 7, 'none', NOW(), NOW()),
(2, 6, 3, 2, 'none', NOW(), NOW());
