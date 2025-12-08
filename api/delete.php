<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/auth.php';

// Require admin access
requireAdmin();

// Cek method
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, "Method not allowed", null, 405);
}

// Ambil data dari request
$input = json_decode(file_get_contents('php://input'), true);

// Jika tidak ada input JSON, coba ambil dari POST/GET
if (!$input) {
    $input = array_merge($_POST, $_GET);
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
$check = $conn->query("SELECT * FROM sarana_ibadah WHERE id = $id");
if ($check->num_rows === 0) {
    $conn->close();
    jsonResponse(false, "Data dengan ID $id tidak ditemukan", null, 404);
}

$deleted_data = $check->fetch_assoc();

// Soft delete - set is_active = FALSE instead of actually deleting
$stmt = $conn->prepare("UPDATE sarana_ibadah SET is_active = FALSE, updated_by = ? WHERE id = ?");
$updatedBy = $_SESSION['user_id'];
$stmt->bind_param("ii", $updatedBy, $id);

if ($stmt->execute()) {
    // Log activity
    logActivity($updatedBy, 'delete', 'sarana_ibadah', $id, "Deleted sarana ibadah: {$deleted_data['nama']}");
    
    $stmt->close();
    $conn->close();
    
    jsonResponse(true, "Data berhasil dihapus", $deleted_data, 200);
} else {
    $error = $stmt->error;
    $stmt->close();
    $conn->close();
    jsonResponse(false, "Failed to delete data: " . $error, null, 500);
}
?>