
CREATE DATABASE IF NOT EXISTS benkonek CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE benkonek;

CREATE TABLE IF NOT EXISTS users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    full_name     VARCHAR(150) NULL,
    email         VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('musician','band','admin') DEFAULT 'musician',
    is_active     BOOLEAN DEFAULT TRUE,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

SET @add_users_full_name = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE users ADD COLUMN full_name VARCHAR(150) NULL AFTER id',
        'SELECT 1'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'users'
      AND COLUMN_NAME = 'full_name'
);
PREPARE stmt FROM @add_users_full_name;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS musicians (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    user_id            INT UNIQUE NOT NULL,
    full_name          VARCHAR(150),
    bio                TEXT,
    location_city      VARCHAR(100),
    primary_instrument VARCHAR(100),
    experience_level   ENUM('Beginner','Intermediate','Advanced','Professional'),
    portfolio_url      VARCHAR(255),
    whatsapp_number    VARCHAR(20),
    photo_profile      VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS bands (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    user_id            INT UNIQUE NOT NULL,
    band_name          VARCHAR(150),
    formation_year     INT,
    main_genre         VARCHAR(100),
    basecamp_location  VARCHAR(150),
    bio                TEXT,
    whatsapp_number    VARCHAR(20),
    photo_profile      VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

SET @add_bands_bio = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE bands ADD COLUMN bio TEXT AFTER basecamp_location',
        'SELECT 1'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'bands'
      AND COLUMN_NAME = 'bio'
);
PREPARE stmt FROM @add_bands_bio;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS vacancies (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    band_id           INT NOT NULL,
    title             VARCHAR(200) NOT NULL,
    description       TEXT,
    needed_instrument VARCHAR(100),
    project_type      ENUM('Permanent','Session','Recording','Gig'),
    status            ENUM('Open','Closed') DEFAULT 'Open',
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (band_id) REFERENCES bands(id) ON DELETE CASCADE
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS app_modules (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    module_key  VARCHAR(60) UNIQUE NOT NULL,
    module_name VARCHAR(100) NOT NULL,
    description VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS roles (
    code        VARCHAR(30) PRIMARY KEY,
    label       VARCHAR(60) NOT NULL,
    description VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS permissions (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    permission_key VARCHAR(80) UNIQUE NOT NULL,
    module_key     VARCHAR(60) NOT NULL,
    action          ENUM('create','read','update','delete','manage') NOT NULL,
    description     VARCHAR(255) NOT NULL,
    FOREIGN KEY (module_key) REFERENCES app_modules(module_key) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS role_permissions (
    role_code     VARCHAR(30) NOT NULL,
    permission_id INT NOT NULL,
    PRIMARY KEY (role_code, permission_id),
    FOREIGN KEY (role_code) REFERENCES roles(code) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS crud_security_matrix (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    module_key  VARCHAR(60) NOT NULL,
    role_code   VARCHAR(30) NOT NULL,
    can_create  BOOLEAN DEFAULT FALSE,
    can_read    BOOLEAN DEFAULT FALSE,
    can_update  BOOLEAN DEFAULT FALSE,
    can_delete  BOOLEAN DEFAULT FALSE,
    scope_note  VARCHAR(255) NOT NULL,
    UNIQUE KEY uniq_matrix (module_key, role_code),
    FOREIGN KEY (module_key) REFERENCES app_modules(module_key) ON DELETE CASCADE,
    FOREIGN KEY (role_code) REFERENCES roles(code) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO app_modules (module_key, module_name, description) VALUES
('auth', 'Login dan Logout', 'Autentikasi multi user memakai email, password hash, session, dan status aktif.'),
('dashboard', 'Dashboard Role', 'Halaman utama setelah login yang berubah sesuai role.'),
('musician_profiles', 'Profil Musisi', 'Data profil musisi, instrumen, pengalaman, portfolio, dan WhatsApp.'),
('band_profiles', 'Profil Band', 'Data profil band, genre, basecamp, tahun terbentuk, dan WhatsApp.'),
('vacancies', 'Lowongan Band', 'CRUD lowongan personel band di GigBoard.'),
('public_catalog', 'Direktori Publik', 'Halaman GigBoard, detail lowongan, dan direktori musisi.'),
('user_management', 'Manajemen User', 'Admin membuat, membaca, mengubah, menghapus, dan menonaktifkan akun.'),
('rbac_docs', 'Dokumentasi RBAC', 'Identifikasi modul, role, RBAC, dan matriks keamanan CRUD.')
ON DUPLICATE KEY UPDATE
module_name = VALUES(module_name),
description = VALUES(description);

INSERT INTO roles (code, label, description) VALUES
('guest', 'Guest', 'Pengunjung belum login. Hanya boleh membaca katalog publik.'),
('musician', 'Musisi', 'User musisi. Mengelola profil sendiri dan membaca lowongan band.'),
('band', 'Band', 'User band. Mengelola profil band dan CRUD lowongan milik band sendiri.'),
('admin', 'Admin', 'Pengelola sistem. Mengelola semua user, status akun, dan membaca RBAC.')
ON DUPLICATE KEY UPDATE
label = VALUES(label),
description = VALUES(description);

INSERT INTO permissions (permission_key, module_key, action, description) VALUES
('auth.login', 'auth', 'read', 'Masuk ke aplikasi dengan akun aktif.'),
('dashboard.read', 'dashboard', 'read', 'Melihat dashboard sesuai role.'),
('catalog.read', 'public_catalog', 'read', 'Membaca GigBoard, detail lowongan, dan direktori musisi.'),
('musician_profile.create', 'musician_profiles', 'create', 'Membuat profil musisi.'),
('musician_profile.read', 'musician_profiles', 'read', 'Membaca profil musisi.'),
('musician_profile.update', 'musician_profiles', 'update', 'Mengubah profil musisi milik sendiri.'),
('band_profile.create', 'band_profiles', 'create', 'Membuat profil band.'),
('band_profile.read', 'band_profiles', 'read', 'Membaca profil band.'),
('band_profile.update', 'band_profiles', 'update', 'Mengubah profil band milik sendiri.'),
('vacancy.create', 'vacancies', 'create', 'Band membuat lowongan.'),
('vacancy.read', 'vacancies', 'read', 'Membaca lowongan.'),
('vacancy.update', 'vacancies', 'update', 'Band mengubah lowongan milik sendiri.'),
('vacancy.delete', 'vacancies', 'delete', 'Band menghapus lowongan milik sendiri.'),
('vacancy.manage_status', 'vacancies', 'manage', 'Band membuka atau menutup lowongan milik sendiri.'),
('user.manage', 'user_management', 'manage', 'Admin mengelola akun user.'),
('rbac.read', 'rbac_docs', 'read', 'Admin membaca tabel RBAC dan matriks CRUD.')
ON DUPLICATE KEY UPDATE
module_key = VALUES(module_key),
action = VALUES(action),
description = VALUES(description);

INSERT IGNORE INTO role_permissions (role_code, permission_id)
SELECT 'guest', id FROM permissions WHERE permission_key IN ('catalog.read');

INSERT IGNORE INTO role_permissions (role_code, permission_id)
SELECT 'musician', id FROM permissions WHERE permission_key IN (
    'auth.login','dashboard.read','catalog.read',
    'musician_profile.create','musician_profile.read','musician_profile.update',
    'band_profile.read','vacancy.read'
);

INSERT IGNORE INTO role_permissions (role_code, permission_id)
SELECT 'band', id FROM permissions WHERE permission_key IN (
    'auth.login','dashboard.read','catalog.read',
    'band_profile.create','band_profile.read','band_profile.update',
    'musician_profile.read','vacancy.create','vacancy.read','vacancy.update',
    'vacancy.delete','vacancy.manage_status'
);

INSERT IGNORE INTO role_permissions (role_code, permission_id)
SELECT 'admin', id FROM permissions;

INSERT INTO crud_security_matrix
(module_key, role_code, can_create, can_read, can_update, can_delete, scope_note) VALUES
('public_catalog', 'guest', 0, 1, 0, 0, 'Guest bisa membaca GigBoard, detail lowongan, dan direktori musisi.'),
('public_catalog', 'musician', 0, 1, 0, 0, 'Musisi login bisa membaca katalog publik.'),
('public_catalog', 'band', 0, 1, 0, 0, 'Band login bisa membaca katalog publik.'),
('public_catalog', 'admin', 0, 1, 0, 0, 'Admin bisa membaca katalog publik.'),
('musician_profiles', 'guest', 0, 1, 0, 0, 'Guest hanya membaca profil publik di direktori.'),
('musician_profiles', 'musician', 1, 1, 1, 0, 'Musisi hanya membuat dan mengubah profil sendiri.'),
('musician_profiles', 'band', 0, 1, 0, 0, 'Band membaca data musisi untuk rekrutmen.'),
('musician_profiles', 'admin', 1, 1, 1, 1, 'Admin dapat mengelola akun dan data terkait melalui manajemen user/database.'),
('band_profiles', 'guest', 0, 1, 0, 0, 'Guest membaca info band melalui lowongan.'),
('band_profiles', 'musician', 0, 1, 0, 0, 'Musisi membaca profil band dari detail lowongan.'),
('band_profiles', 'band', 1, 1, 1, 0, 'Band hanya membuat dan mengubah profil sendiri.'),
('band_profiles', 'admin', 1, 1, 1, 1, 'Admin dapat mengelola akun dan data terkait melalui manajemen user/database.'),
('vacancies', 'guest', 0, 1, 0, 0, 'Guest hanya membaca lowongan terbuka.'),
('vacancies', 'musician', 0, 1, 0, 0, 'Musisi membaca lowongan dan menghubungi band.'),
('vacancies', 'band', 1, 1, 1, 1, 'Band CRUD hanya untuk lowongan milik band sendiri.'),
('vacancies', 'admin', 1, 1, 1, 1, 'Admin dapat mengawasi data lowongan melalui database.'),
('user_management', 'guest', 0, 0, 0, 0, 'Guest tidak punya akses manajemen user.'),
('user_management', 'musician', 0, 0, 0, 0, 'Musisi tidak punya akses manajemen user.'),
('user_management', 'band', 0, 0, 0, 0, 'Band tidak punya akses manajemen user.'),
('user_management', 'admin', 1, 1, 1, 1, 'Admin bisa create, read, update, delete, reset password, dan ubah status user.'),
('rbac_docs', 'guest', 0, 0, 0, 0, 'Guest tidak melihat tabel RBAC.'),
('rbac_docs', 'musician', 0, 0, 0, 0, 'Musisi tidak melihat tabel RBAC.'),
('rbac_docs', 'band', 0, 0, 0, 0, 'Band tidak melihat tabel RBAC.'),
('rbac_docs', 'admin', 0, 1, 0, 0, 'Admin membaca identifikasi modul, role, RBAC, dan matriks CRUD.')
ON DUPLICATE KEY UPDATE
can_create = VALUES(can_create),
can_read = VALUES(can_read),
can_update = VALUES(can_update),
can_delete = VALUES(can_delete),
scope_note = VALUES(scope_note);

INSERT INTO users (id, full_name, email, password_hash, role, is_active)
VALUES (1, 'Administrator BenKonek', 'admin@benkonek.test', '$2y$10$bOVVQOPapmT14LyGezWeY.fjk92uFjQwh5kQwSP4DoHrBKweBCMqa', 'admin', 1)
ON DUPLICATE KEY UPDATE
full_name = VALUES(full_name),
password_hash = VALUES(password_hash),
role = VALUES(role),
is_active = VALUES(is_active);
