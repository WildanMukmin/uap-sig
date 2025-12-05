<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

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
    jsonResponse(false, "Field 'id' is required", null, 400);
}

$id = intval($input['id']);

$conn = getConnection();

if (!$conn) {
    jsonResponse(false, "Database connection failed", null, 500);
}

// Cek apakah data exists
$check = $conn->query("SELECT * FROM sarana_ibadah WHERE id = $id");
if ($check->num_rows === 0) {
    jsonResponse(false, "Data dengan ID $id tidak ditemukan", null, 404);
}

$deleted_data = $check->fetch_assoc();

// Delete data
$stmt = $conn->prepare("DELETE FROM sarana_ibadah WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    jsonResponse(true, "Data berhasil dihapus", $deleted_data, 200);
} else {
    jsonResponse(false, "Failed to delete data: " . $stmt->error, null, 500);
}

$stmt->close();
$conn->close();
?>