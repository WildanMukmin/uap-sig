<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/auth.php';

// Require admin access
requireAdmin();

// Cek method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, "Method not allowed", null, 405);
}

// Ambil data dari request
$input = json_decode(file_get_contents('php://input'), true);

// Jika tidak ada input JSON, coba ambil dari POST biasa
if (!$input) {
    $input = $_POST;
}

// Validasi input
$required = ['nama', 'jenis', 'latitude', 'longitude'];
foreach ($required as $field) {
    if (empty($input[$field])) {
        jsonResponse(false, "Field '$field' is required", null, 400);
    }
}

// Sanitize input
$nama = sanitizeInput($input['nama']);
$jenis = sanitizeInput($input['jenis']);
$alamat = isset($input['alamat']) ? sanitizeInput($input['alamat']) : '';
$kecamatanName = isset($input['kecamatan']) ? sanitizeInput($input['kecamatan']) : '';
$kapasitas = isset($input['kapasitas']) ? intval($input['kapasitas']) : 0;
$tahun_berdiri = isset($input['tahun_berdiri']) ? intval($input['tahun_berdiri']) : null;
$latitude = floatval($input['latitude']);
$longitude = floatval($input['longitude']);
$keterangan = isset($input['keterangan']) ? sanitizeInput($input['keterangan']) : '';

// Validasi jenis
$valid_jenis = ['Masjid', 'Gereja', 'Pura', 'Vihara', 'Klenteng'];
if (!in_array($jenis, $valid_jenis)) {
    jsonResponse(false, "Invalid jenis. Must be one of: " . implode(', ', $valid_jenis), null, 400);
}

// Validasi koordinat (untuk wilayah Bandar Lampung)
if ($latitude < -5.5 || $latitude > -5.3 || $longitude < 105.1 || $longitude > 105.4) {
    jsonResponse(false, "Koordinat di luar wilayah Bandar Lampung", null, 400);
}

$conn = getConnection();

if (!$conn) {
    jsonResponse(false, "Database connection failed", null, 500);
}

// Get kecamatan_id if kecamatan name provided
$kecamatanId = null;
if (!empty($kecamatanName)) {
    $kecStmt = $conn->prepare("SELECT id FROM kecamatan WHERE nama = ?");
    $kecStmt->bind_param("s", $kecamatanName);
    $kecStmt->execute();
    $kecResult = $kecStmt->get_result();
    if ($kecResult->num_rows > 0) {
        $kecRow = $kecResult->fetch_assoc();
        $kecamatanId = $kecRow['id'];
    }
    $kecStmt->close();
}

// Get current user ID
$createdBy = $_SESSION['user_id'];

// Prepared statement untuk mencegah SQL injection
$stmt = $conn->prepare("INSERT INTO sarana_ibadah (nama, jenis, alamat, kecamatan_id, kecamatan_name, kapasitas, tahun_berdiri, latitude, longitude, keterangan, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param("sssissiddsi", $nama, $jenis, $alamat, $kecamatanId, $kecamatanName, $kapasitas, $tahun_berdiri, $latitude, $longitude, $keterangan, $createdBy);

if ($stmt->execute()) {
    $new_id = $conn->insert_id;
    
    // Log activity
    logActivity($createdBy, 'create', 'sarana_ibadah', $new_id, "Created sarana ibadah: {$nama}");
    
    // Ambil data yang baru diinsert
    $result = $conn->query("SELECT * FROM sarana_ibadah WHERE id = $new_id");
    $new_data = $result->fetch_assoc();
    
    jsonResponse(true, "Data berhasil ditambahkan", $new_data, 201);
} else {
    jsonResponse(false, "Failed to insert data: " . $stmt->error, null, 500);
}

$stmt->close();
$conn->close();
?>