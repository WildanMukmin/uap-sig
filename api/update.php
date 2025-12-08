<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/auth.php';

// Require admin access
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
    jsonResponse(false, "Field 'id' wajib diisi", null, 400);
}

$id = intval($input['id']);

$conn = getConnection();

if (!$conn) {
    jsonResponse(false, "Database connection failed", null, 500);
}

// Cek apakah data exists
$check = $conn->query("SELECT id FROM sarana_ibadah WHERE id = $id AND is_active = TRUE");
if ($check->num_rows === 0) {
    $conn->close();
    jsonResponse(false, "Data dengan ID $id tidak ditemukan", null, 404);
}

// Build update query dynamically
$updates = [];
$types = "";
$values = [];

$allowed_fields = ['nama', 'jenis', 'alamat', 'kecamatan', 'kapasitas', 'tahun_berdiri', 'latitude', 'longitude', 'keterangan'];

// Get kecamatan_id if kecamatan name provided
$kecamatanId = null;
$kecamatanName = null;

foreach ($allowed_fields as $field) {
    if (isset($input[$field]) && $input[$field] !== '') {
        if ($field === 'kecamatan') {
            $kecamatanName = sanitizeInput($input[$field]);
            
            // Get kecamatan_id
            $kecStmt = $conn->prepare("SELECT id FROM kecamatan WHERE nama = ?");
            $kecStmt->bind_param("s", $kecamatanName);
            $kecStmt->execute();
            $kecResult = $kecStmt->get_result();
            if ($kecResult->num_rows > 0) {
                $kecRow = $kecResult->fetch_assoc();
                $kecamatanId = $kecRow['id'];
            }
            $kecStmt->close();
            
            $updates[] = "kecamatan_id = ?";
            $types .= "i";
            $values[] = $kecamatanId;
            
            $updates[] = "kecamatan_name = ?";
            $types .= "s";
            $values[] = $kecamatanName;
        } else {
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
}

if (empty($updates)) {
    $conn->close();
    jsonResponse(false, "Tidak ada data yang akan diupdate", null, 400);
}

// Validasi jenis jika ada
if (isset($input['jenis'])) {
    $valid_jenis = ['Masjid', 'Gereja', 'Pura', 'Vihara', 'Klenteng'];
    if (!in_array($input['jenis'], $valid_jenis)) {
        $conn->close();
        jsonResponse(false, "Jenis tidak valid", null, 400);
    }
}

// Add updated_by
$updatedBy = $_SESSION['user_id'];
$updates[] = "updated_by = ?";
$types .= "i";
$values[] = $updatedBy;

// Tambahkan ID ke parameter
$types .= "i";
$values[] = $id;

// Buat query
$sql = "UPDATE sarana_ibadah SET " . implode(", ", $updates) . " WHERE id = ?";
$stmt = $conn->prepare($sql);

// Bind parameters secara dinamis
$stmt->bind_param($types, ...$values);

if ($stmt->execute()) {
    // Log activity
    logActivity($updatedBy, 'update', 'sarana_ibadah', $id, "Updated sarana ibadah ID: {$id}");
    
    // Ambil data yang sudah diupdate
    $result = $conn->query("SELECT * FROM sarana_ibadah WHERE id = $id");
    $updated_data = $result->fetch_assoc();
    
    $stmt->close();
    $conn->close();
    
    jsonResponse(true, "Data berhasil diupdate", $updated_data, 200);
} else {
    $error = $stmt->error;
    $stmt->close();
    $conn->close();
    jsonResponse(false, "Failed to update data: " . $error, null, 500);
}
?>