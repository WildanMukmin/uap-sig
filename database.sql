-- Database untuk Web GIS Sarana Ibadah Bandar Lampung

CREATE DATABASE IF NOT EXISTS webgis_sarana_ibadah;
USE webgis_sarana_ibadah;

-- Tabel untuk menyimpan data sarana ibadah
CREATE TABLE IF NOT EXISTS sarana_ibadah (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    jenis ENUM('Masjid', 'Gereja', 'Pura', 'Vihara', 'Klenteng') NOT NULL,
    alamat TEXT,
    kecamatan VARCHAR(100),
    kapasitas INT,
    tahun_berdiri YEAR,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    keterangan TEXT,
    foto VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_jenis (jenis),
    INDEX idx_kecamatan (kecamatan),
    INDEX idx_koordinat (latitude, longitude)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data sample untuk testing
INSERT INTO sarana_ibadah (nama, jenis, alamat, kecamatan, kapasitas, tahun_berdiri, latitude, longitude, keterangan) VALUES
('Masjid Agung Al-Furqon', 'Masjid', 'Jl. Kartini No.1', 'Teluk Betung Selatan', 3000, 1982, -5.4285, 105.2619, 'Masjid terbesar di Bandar Lampung'),
('Gereja Katedral Santo Fransiskus Xaverius', 'Gereja', 'Jl. Kartini No.86', 'Teluk Betung Selatan', 800, 1936, -5.4312, 105.2645, 'Gereja Katolik bersejarah'),
('Masjid Al-Anwar', 'Masjid', 'Jl. Raden Intan', 'Tanjung Karang Pusat', 1500, 1970, -5.4200, 105.2650, 'Masjid di pusat kota'),
('Pura Agung Wira Loka Natha', 'Pura', 'Jl. Pulau Pisang', 'Tanjung Karang Barat', 500, 1985, -5.4350, 105.2550, 'Pura Hindu di Bandar Lampung'),
('Vihara Dhamma Sukha', 'Vihara', 'Jl. Diponegoro', 'Teluk Betung Utara', 300, 1990, -5.4150, 105.2700, 'Vihara Buddha'),
('Masjid Jami Al-Istiqomah', 'Masjid', 'Jl. ZA Pagar Alam', 'Kedaton', 1000, 1995, -5.3950, 105.2580, 'Masjid di wilayah Kedaton'),
('Gereja GPIB Immanuel', 'Gereja', 'Jl. Teuku Umar', 'Tanjung Karang Timur', 600, 1965, -5.4180, 105.2750, 'Gereja Protestan');

-- View untuk menampilkan data dalam format yang mudah dibaca
CREATE VIEW v_sarana_ibadah AS
SELECT 
    id,
    nama,
    jenis,
    alamat,
    kecamatan,
    kapasitas,
    tahun_berdiri,
    latitude,
    longitude,
    keterangan,
    foto,
    DATE_FORMAT(created_at, '%d-%m-%Y %H:%i') as tanggal_input,
    DATE_FORMAT(updated_at, '%d-%m-%Y %H:%i') as tanggal_update
FROM sarana_ibadah
ORDER BY created_at DESC;