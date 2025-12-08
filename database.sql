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

-- Insert data sarana ibadah
INSERT INTO sarana_ibadah (nama, jenis, alamat, kecamatan_name, kapasitas, latitude, longitude, keterangan, luas, fgsibd, namobj, remark, created_by, is_active) VALUES
('Gereja Masehi Advent Hari Ketujuh Kemiling', 'Gereja', '', 'Kemiling', 0, -5.403230043, 105.209281007, '', 0.0, 2, 'Gereja Masehi Advent Hari Ketujuh Kemiling', 'Gereja', 1, TRUE),
('Masjid Nurul Ihsan Beringinjaya', 'Masjid', '', 'Kemiling', 0, -5.402252818, 105.209885448, '', 0.0, 1, 'Masjid Nurul Ihsan Beringinjaya', 'Masjid', 1, TRUE),
('Masjid La Tansa Perum Wana Asri', 'Masjid', '', 'Kemiling', 0, -5.40103295, 105.205786998, '', 0.0, 1, 'Masjid La Tansa Perum Wana Asri', 'Masjid', 1, TRUE),
('Masjid At Taqwa Sukarame Dua', 'Masjid', '', 'Teluk Betung Barat', 0, -5.439070053, 105.239001379, '', 0.0, 1, 'Masjid At Taqwa Sukarame Dua', 'Masjid', 1, TRUE),
('Mushola Al Asyri Sukarame Dua', 'Masjid', '', 'Teluk Betung Barat', 0, -5.437326307, 105.233349777, '', 0.0, 1, 'Mushola Al Asyri Sukarame Dua', 'Masjid', 1, TRUE),
('Masjid Al Amin Batuputuk', 'Masjid', '', 'Teluk Betung Barat', 0, -5.436391725, 105.216595282, '', 0.0, 1, 'Masjid Al Amin Batuputuk', 'Masjid', 1, TRUE),
('Mushola Duta Wisata', 'Masjid', '', 'Teluk Betung Timur', 0, -5.476227761, 105.251131124, '', 0.0, 1, 'Mushola Duta Wisata', 'Masjid', 1, TRUE),
('Masjid At Taqwa Keteguhan', 'Masjid', '', 'Teluk Betung Timur', 0, -5.461601285, 105.247378667, '', 0.0, 1, 'Masjid At Taqwa Keteguhan', 'Masjid', 1, TRUE),
('Masjid An Nur Kotakarangraya', 'Masjid', '', 'Teluk Betung Timur', 0, -5.45958943, 105.255863543, '', 0.0, 1, 'Masjid An Nur Kotakarangraya', 'Masjid', 1, TRUE),
('Vihara Thay Hin Bo Telukbetung', 'Vihara', '', 'Teluk Betung Selatan', 0, -5.44878925, 105.263174219, '', 0.0, 4, 'Vihara Thay Hin Bo Telukbetung', 'Vihara', 1, TRUE),
('Gereja Agape Telukbetung', 'Gereja', '', 'Teluk Betung Selatan', 0, -5.447783363, 105.261440127, '', 0.0, 2, 'Gereja Agape Telukbetung', 'Gereja', 1, TRUE),
('Mushola Al Falah Pesawahan', 'Masjid', '', 'Teluk Betung Selatan', 0, -5.44725252, 105.260113999, '', 0.0, 1, 'Mushola Al Falah Pesawahan', 'Masjid', 1, TRUE),
('Mushola Al Ikhlas Sumurputri', 'Masjid', '', 'Teluk Betung Selatan', 0, -5.438376254, 105.254338027, '', 0.0, 1, 'Mushola Al Ikhlas Sumurputri', 'Masjid', 1, TRUE),
('Vihara Kshanti Maitreya Sukaraja', 'Vihara', '', 'Bumi Waras', 0, -5.44126761, 105.285658884, '', 0.0, 4, 'Vihara Kshanti Maitreya Sukaraja', 'Vihara', 1, TRUE),
('Masjid Al Munawarah Bumiwaras', 'Masjid', '', 'Bumi Waras', 0, -5.440309302, 105.283221987, '', 0.0, 1, 'Masjid Al Munawarah Bumiwaras', 'Masjid', 1, TRUE),
('Gereja Maranatha Bumiraya', 'Gereja', '', 'Bumi Waras', 0, -5.438407109, 105.280633463, '', 0.0, 2, 'Gereja Maranatha Bumiraya', 'Gereja', 1, TRUE),
('Gereja Baptis Indonesia Imanuel Panjang', 'Gereja', '', 'Panjang', 0, -5.481196235, 105.322478631, '', 0.0, 2, 'Gereja Baptis Indonesia Imanuel Panjang', 'Gereja', 1, TRUE),
('Masjid Baiturrohmah Harapanjaya', 'Masjid', '', 'Panjang', 0, -5.481033973, 105.321413286, '', 0.0, 1, 'Masjid Baiturrohmah Harapanjaya', 'Masjid', 1, TRUE),
('Kelenteng Telukharapan', 'Klenteng', '', 'Panjang', 0, -5.478537173, 105.321430007, '', 0.0, 5, 'Kelenteng Telukharapan', 'Kelenteng', 1, TRUE),
('Mushola Baitul Jamil Bukit Palm Hijau', 'Masjid', '', 'Sukabumi', 0, -5.412143962, 105.325203954, '', 0.0, 1, 'Mushola Baitul Jamil Bukit Palm Hijau', 'Masjid', 1, TRUE),
('Masjid Nurul Jannah Panitrik', 'Masjid', '', 'Sukabumi', 0, -5.409863504, 105.309397247, '', 0.0, 1, 'Masjid Nurul Jannah Panitrik', 'Masjid', 1, TRUE),
('Gereja Injil Indonesia Villa Bukit Tirtayasa', 'Gereja', '', 'Sukabumi', 0, -5.404442077, 105.314548611, '', 0.0, 2, 'Gereja Injil Indonesia Villa Bukit Tirtayasa', 'Gereja', 1, TRUE),
('Masjid Al Aulia Waydadi', 'Masjid', '', 'Sukarame', 0, -5.37732086, 105.296852564, '', 0.0, 1, 'Masjid Al Aulia Waydadi', 'Masjid', 1, TRUE),
('Masjid Darussalam Waydadi', 'Masjid', '', 'Sukarame', 0, -5.377150807, 105.293070552, '', 0.0, 1, 'Masjid Darussalam Waydadi', 'Masjid', 1, TRUE),
('Masjid Tawakal Sukarame Satu', 'Masjid', '', 'Sukarame', 0, -5.376474239, 105.28906265, '', 0.0, 1, 'Masjid Tawakal Sukarame Satu', 'Masjid', 1, TRUE),
('Mushola Pematangwangi', 'Masjid', '', 'Tanjung Senang', 0, -5.362691843, 105.278317495, '', 0.0, 1, 'Mushola Pematangwangi', 'Masjid', 1, TRUE),
('Gereja Labuhanratu', 'Gereja', '', 'Tanjung Senang', 0, -5.360829767, 105.255695692, '', 0.0, 2, 'Gereja Labuhanratu', 'Gereja', 1, TRUE),
('Masjid Nurul Amal Tanjungraya', 'Masjid', '', 'Tanjung Senang', 0, -5.360728543, 105.280264054, '', 0.0, 1, 'Masjid Nurul Amal Tanjungraya', 'Masjid', 1, TRUE),
('Masjid Baitul Makmur Rajabasa', 'Masjid', '', 'Rajabasa', 0, -5.379763345, 105.231874246, '', 0.0, 1, 'Masjid Baitul Makmur Rajabasa', 'Masjid', 1, TRUE),
('Masjid Baburrohman Gunungterang', 'Masjid', '', 'Rajabasa', 0, -5.37958834, 105.241577634, '', 0.0, 1, 'Masjid Baburrohman Gunungterang', 'Masjid', 1, TRUE),
('Masjid Nurul Islah Gedongmeneng', 'Masjid', '', 'Rajabasa', 0, -5.374868118, 105.242278922, '', 0.0, 1, 'Masjid Nurul Islah Gedongmeneng', 'Masjid', 1, TRUE),
('Masjid Nurul Huda Gunungagung', 'Masjid', '', 'Langkapura', 0, -5.391873, 105.235596, '', 0.0, 1, 'Masjid Nurul Huda Gunungagung', 'Masjid', 1, TRUE),
('Masjid Saikhul Ulum Langkapura', 'Masjid', '', 'Langkapura', 0, -5.389809551, 105.222274318, '', 0.0, 1, 'Masjid Saikhul Ulum Langkapura', 'Masjid', 1, TRUE),
('Mushola Gunungterang', 'Masjid', '', 'Langkapura', 0, -5.38432464, 105.229727999, '', 0.0, 1, 'Mushola Gunungterang', 'Masjid', 1, TRUE),
('Masjid Abraham Labuhanratu', 'Masjid', '', 'Labuhan Ratu', 0, -5.374508313, 105.254870991, '', 0.0, 1, 'Masjid Abraham Labuhanratu', 'Masjid', 1, TRUE),
('Masjid Al Hikmah Kampungbaru Tiga', 'Masjid', '', 'Labuhan Ratu', 0, -5.372230337, 105.250709037, '', 0.0, 1, 'Masjid Al Hikmah Kampungbaru Tiga', 'Masjid', 1, TRUE),
('Masjid Al Hikmah Kampungbaru', 'Masjid', '', 'Labuhan Ratu', 0, -5.372194151, 105.250709023, '', 0.0, 1, 'Masjid Al Hikmah Kampungbaru', 'Masjid', 1, TRUE),
('Vihara Virya Paramita Sepangjaya', 'Vihara', '', 'Labuhan Ratu', 0, -5.369825622, 105.268155322, '', 0.0, 4, 'Vihara Virya Paramita Sepangjaya', 'Vihara', 1, TRUE),
('Masjid Al Awwal Sidodadi', 'Masjid', '', 'Kedaton', 0, -5.393980059, 105.258928663, '', 0.0, 1, 'Masjid Al Awwal Sidodadi', 'Masjid', 1, TRUE),
('Gereja GGP Anugerah Kedaton', 'Gereja', '', 'Kedaton', 0, -5.392094749, 105.259019278, '', 0.0, 2, 'Gereja GGP Anugerah Kedaton', 'Gereja', 1, TRUE),
('Masjid Al Hikmah Kedaton', 'Masjid', '', 'Kedaton', 0, -5.383718809, 105.258982576, '', 0.0, 1, 'Masjid Al Hikmah Kedaton', 'Masjid', 1, TRUE),
('Gereja Pantekosta Indonesia Jagabaya Tiga', 'Gereja', '', 'Way Halim', 0, -5.402906403, 105.272556695, '', 0.0, 2, 'Gereja Pantekosta Indonesia Jagabaya Tiga', 'Gereja', 1, TRUE),
('Masjid Khoirul Karim', 'Masjid', '', 'Way Halim', 0, -5.40057339, 105.270407357, '', 0.0, 1, 'Masjid Khoirul Karim', 'Masjid', 1, TRUE),
('Masjid Ad Du''a Wayhalim', 'Masjid', '', 'Way Halim', 0, -5.381161549, 105.274491544, '', 0.0, 1, 'Masjid Ad Du''a Wayhalim', 'Masjid', 1, TRUE),
('Vihara Suci Mulia Bumikedamaian', 'Vihara', '', 'Kedamaian', 0, -5.41433744, 105.280487208, '', 0.0, 4, 'Vihara Suci Mulia Bumikedamaian', 'Vihara', 1, TRUE),
('Gereja Bethany Bumi Asri', 'Gereja', '', 'Kedamaian', 0, -5.414234702, 105.287374489, '', 0.0, 2, 'Gereja Bethany Bumi Asri', 'Gereja', 1, TRUE),
('Masjid Al Amin Bumikedamaian', 'Masjid', '', 'Kedamaian', 0, -5.4140917, 105.283682526, '', 0.0, 1, 'Masjid Al Amin Bumikedamaian', 'Masjid', 1, TRUE),
('Gereja Kristen Tritunggal Bandarlampung', 'Gereja', '', 'Teluk Betung Utara', 0, -5.445527365, 105.264517271, '', 0.0, 2, 'Gereja Kristen Tritunggal Bandarlampung', 'Gereja', 1, TRUE),
('Masjid Nurul Iman Pengajaran', 'Masjid', '', 'Teluk Betung Utara', 0, -5.43318452, 105.252178408, '', 0.0, 1, 'Masjid Nurul Iman Pengajaran', 'Masjid', 1, TRUE),
('Vihara Pausen Thai Ti Bandarlampung', 'Vihara', '', 'Teluk Betung Utara', 0, -5.433134931, 105.253052021, '', 0.0, 4, 'Vihara Pausen Thai Ti Bandarlampung', 'Vihara', 1, TRUE),
('Mushola Babut Taubah Pelita', 'Masjid', '', 'Enggal', 0, -5.420441414, 105.256873192, '', 0.0, 1, 'Mushola Babut Taubah Pelita', 'Masjid', 1, TRUE),
('Vihara Cetya Setya Dharma Bandarlampung', 'Vihara', '', 'Enggal', 0, -5.413947874, 105.256653858, '', 0.0, 4, 'Vihara Cetya Setya Dharma Bandarlampung', 'Vihara', 1, TRUE),
('Gereja Katedral Kristus Raja Bandarlampung', 'Gereja', '', 'Enggal', 0, -5.409138251, 105.258320681, '', 0.0, 2, 'Gereja Katedral Kristus Raja Bandarlampung', 'Gereja', 1, TRUE),
('Mushola Nurul Karim kotabaru', 'Masjid', '', 'Tanjung Karang Timur', 0, -5.42006296, 105.271503902, '', 0.0, 1, 'Mushola Nurul Karim kotabaru', 'Masjid', 1, TRUE),
('Mushola Nurul Falah Sawahlama', 'Masjid', '', 'Tanjung Karang Timur', 0, -5.407916572, 105.264513176, '', 0.0, 1, 'Mushola Nurul Falah Sawahlama', 'Masjid', 1, TRUE),
('Gereja Pantekosta Ora Et Labora Bandarlampung', 'Gereja', '', 'Tanjung Karang Timur', 0, -5.406938229, 105.263291851, '', 0.0, 2, 'Gereja Pantekosta Ora Et Labora Bandarlampung', 'Gereja', 1, TRUE),
('Masjid Nurul Iman Durianpayung', 'Masjid', '', 'Tanjung Karang Pusat', 0, -5.423953295, 105.248563878, '', 0.0, 1, 'Masjid Nurul Iman Durianpayung', 'Masjid', 1, TRUE),
('Masjid Baitul Musyarofah Durianpayung', 'Masjid', '', 'Tanjung Karang Pusat', 0, -5.421230465, 105.244935867, '', 0.0, 1, 'Masjid Baitul Musyarofah Durianpayung', 'Masjid', 1, TRUE),
('Masjid Muawwanah Tanjungkarang', 'Masjid', '', 'Tanjung Karang Pusat', 0, -5.417188358, 105.252673972, '', 0.0, 1, 'Masjid Muawwanah Tanjungkarang', 'Masjid', 1, TRUE),
('Gereja GPIB Maituria Sukajawabaru', 'Gereja', '', 'Tanjung Karang Pusat', 0, -5.409472405, 105.255661861, '', 0.0, 2, 'Gereja GPIB Maituria Sukajawabaru', 'Gereja', 1, TRUE),
('Masjid Mushowwirul Iman Pasirgintung', 'Masjid', '', 'Tanjung Karang Pusat', 0, -5.406835529, 105.255325297, '', 0.0, 1, 'Masjid Mushowwirul Iman Pasirgintung', 'Masjid', 1, TRUE),
('Mushola Husnul Khotimah Pasirgintung', 'Masjid', '', 'Tanjung Karang Pusat', 0, -5.40546849, 105.256882876, '', 0.0, 1, 'Mushola Husnul Khotimah Pasirgintung', 'Masjid', 1, TRUE),
('Mushola Al Ikhlas Sukajawabaru', 'Masjid', '', 'Tanjung Karang Barat', 0, -5.409573405, 105.252105425, '', 0.0, 1, 'Mushola Al Ikhlas Sukajawabaru', 'Masjid', 1, TRUE),
('Masjid Baiturrahman Gedongair', 'Masjid', '', 'Tanjung Karang Barat', 0, -5.401992377, 105.245791112, '', 0.0, 1, 'Masjid Baiturrahman Gedongair', 'Masjid', 1, TRUE);

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