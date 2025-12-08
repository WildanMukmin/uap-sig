-- Database untuk Web GIS Sarana Ibadah Bandar Lampung (Improved)

CREATE DATABASE IF NOT EXISTS webgis_sarana_ibadah;
USE webgis_sarana_ibadah;

-- Tabel Users untuk authentication
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    role ENUM('admin', 'guest') DEFAULT 'guest',
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Kecamatan
CREATE TABLE IF NOT EXISTS kecamatan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    luas_km DECIMAL(10, 3),
    geojson LONGTEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nama (nama)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Sarana Ibadah (Improved)
CREATE TABLE IF NOT EXISTS sarana_ibadah (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    jenis ENUM('Masjid', 'Gereja', 'Pura', 'Vihara', 'Klenteng') NOT NULL,
    alamat TEXT,
    kecamatan_id INT,
    kecamatan_name VARCHAR(100),
    kapasitas INT DEFAULT 0,
    tahun_berdiri YEAR,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    keterangan TEXT,
    foto VARCHAR(255),
    luas DECIMAL(10, 2) DEFAULT 0.0,
    fgsibd INT,
    namobj VARCHAR(255),
    remark VARCHAR(255),
    created_by INT,
    updated_by INT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (kecamatan_id) REFERENCES kecamatan(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_jenis (jenis),
    INDEX idx_kecamatan (kecamatan_id),
    INDEX idx_kecamatan_name (kecamatan_name),
    INDEX idx_koordinat (latitude, longitude),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Activity Log
CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action ENUM('create', 'read', 'update', 'delete', 'login', 'logout') NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, full_name, email, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@bandarlampung.go.id', 'admin');

-- Insert Kecamatan data
INSERT INTO kecamatan (nama, luas_km) VALUES
('Kemiling', 21.204),
('Teluk Betung Barat', 17.788),
('Teluk Betung Timur', 10.414),
('Teluk Betung Selatan', 3.549),
('Bumi Waras', 4.247),
('Panjang', 13.047),
('Sukabumi', 24.649),
('Sukarame', 10.514),
('Tanjung Senang', 9.366),
('Rajabasa', 12.882),
('Langkapura', 5.15),
('Labuhan Ratu', 5.994),
('Kedaton', 3.705),
('Way Halim', 6.242),
('Kedamaian', 8.273),
('Teluk Betung Utara', 4.095),
('Enggal', 2.748),
('Tanjung Karang Timur', 1.998),
('Tanjung Karang Pusat', 3.351),
('Tanjung Karang Barat', 11.435);

-- Views untuk reporting
CREATE VIEW v_sarana_ibadah_detail AS
SELECT 
    s.id,
    s.nama,
    s.jenis,
    s.alamat,
    s.kecamatan_name as kecamatan,
    k.luas_km as luas_kecamatan,
    s.kapasitas,
    s.tahun_berdiri,
    s.latitude,
    s.longitude,
    s.keterangan,
    s.foto,
    s.is_active,
    u1.username as created_by_username,
    u2.username as updated_by_username,
    DATE_FORMAT(s.created_at, '%d-%m-%Y %H:%i') as tanggal_input,
    DATE_FORMAT(s.updated_at, '%d-%m-%Y %H:%i') as tanggal_update
FROM sarana_ibadah s
LEFT JOIN kecamatan k ON s.kecamatan_id = k.id
LEFT JOIN users u1 ON s.created_by = u1.id
LEFT JOIN users u2 ON s.updated_by = u2.id
WHERE s.is_active = TRUE
ORDER BY s.created_at DESC;

-- View untuk statistik
CREATE VIEW v_statistik_sarana AS
SELECT 
    jenis,
    COUNT(*) as jumlah,
    SUM(kapasitas) as total_kapasitas,
    AVG(kapasitas) as rata_kapasitas
FROM sarana_ibadah
WHERE is_active = TRUE
GROUP BY jenis;

CREATE VIEW v_statistik_kecamatan AS
SELECT 
    k.nama as kecamatan,
    k.luas_km,
    COUNT(s.id) as jumlah_sarana,
    COUNT(DISTINCT s.jenis) as jenis_sarana
FROM kecamatan k
LEFT JOIN sarana_ibadah s ON k.id = s.kecamatan_id AND s.is_active = TRUE
GROUP BY k.id, k.nama, k.luas_km
ORDER BY jumlah_sarana DESC;