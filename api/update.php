<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../config/auth.php';
requireAdmin();

// Cek method
if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, "Method not allowed", null, 405);
}

// Ambil data dari request
$input = json_decode(file_get_contents('php://input'), true);

// Jika tidak ada input JSON, coba ambil dari POST biasa
if (!$input) {
    $input = $_POST;
}

// Validasi input
if (empty($input['id'])) {
    jsonResponse(false, "Field 'id' is required", null, 400);
}

$id = intval($input['id']);

$conn = getConnection();

if (!$conn) {
    jsonResponse(false, "Database connection failed", null, 500);
}

// Cek apakah data exists
$check = $conn->query("SELECT id FROM sarana_ibadah WHERE id = $id");
if ($check->num_rows === 0) {
    jsonResponse(false, "Data dengan ID $id tidak ditemukan", null, 404);
}

// Build update query dynamically
$updates = [];
$types = "";
$values = [];

$allowed_fields = ['nama', 'jenis', 'alamat', 'kecamatan', 'kapasitas', 'tahun_berdiri', 'latitude', 'longitude', 'keterangan'];

foreach ($allowed_fields as $field) {
    if (isset($input[$field]) && $input[$field] !== '') {
        $updates[] = "$field = ?";
        
        // Tentukan tipe data
        if ($field === 'kapasitas' || $field === 'tahun_berdiri') {
            $types .= "i";
            $values[] = intval($input[$field]);
        } elseif ($field === 'latitude' || $field === 'longitude') {
            $types .= "d";
            $values[] = floatval($input[$field]);
        } else {
            $types .= "s";
            $values[] = sanitizeInput($input[$field]);
        }
    }
}

if (empty($updates)) {
    jsonResponse(false, "No fields to update", null, 400);
}

// Validasi jenis jika ada
if (isset($input['jenis'])) {
    $valid_jenis = ['Masjid', 'Gereja', 'Pura', 'Vihara', 'Klenteng'];
    if (!in_array($input['jenis'], $valid_jenis)) {
        jsonResponse(false, "Invalid jenis", null, 400);
    }
}

// Tambahkan ID ke parameter
$types .= "i";
$values[] = $id;

// Buat query
$sql = "UPDATE sarana_ibadah SET " . implode(", ", $updates) . " WHERE id = ?";
$stmt = $conn->prepare($sql);

// Bind parameters secara dinamis
$stmt->bind_param($types, ...$values);

if ($stmt->execute()) {
    // Ambil data yang sudah diupdate
    $result = $conn->query("SELECT * FROM sarana_ibadah WHERE id = $id");
    $updated_data = $result->fetch_assoc();
    
    jsonResponse(true, "Data berhasil diupdate", $updated_data, 200);
} else {
    jsonResponse(false, "Failed to update data: " . $stmt->error, null, 500);
}

$stmt->close();
$conn->close();
?>