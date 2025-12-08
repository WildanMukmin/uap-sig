<?php
session_start();

require_once 'database.php';

// Function untuk check apakah user sudah login
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

// Function untuk check apakah user adalah admin
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Function untuk get current user data
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'full_name' => $_SESSION['full_name'],
        'role' => $_SESSION['role']
    ];
}

// Function untuk require admin access
function requireAdmin() {
    if (!isAdmin()) {
        jsonResponse(false, "Unauthorized. Admin access required.", null, 403);
    }
}

// Function untuk require login
function requireLogin() {
    if (!isLoggedIn()) {
        jsonResponse(false, "Unauthorized. Please login first.", null, 401);
    }
}

// Function untuk login
function loginUser($username, $password) {
    $conn = getConnection();
    
    if (!$conn) {
        return ['success' => false, 'message' => 'Database connection failed'];
    }
    
    $stmt = $conn->prepare("SELECT id, username, password, full_name, email, role, is_active FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        $conn->close();
        return ['success' => false, 'message' => 'Username tidak ditemukan'];
    }
    
    $user = $result->fetch_assoc();
    
    if (!$user['is_active']) {
        $stmt->close();
        $conn->close();
        return ['success' => false, 'message' => 'Akun tidak aktif'];
    }
    
    // Simple password check (in production, use password_hash and password_verify)
    if ($password !== $user['password']) {
        $stmt->close();
        $conn->close();
        return ['success' => false, 'message' => 'Password salah'];
    }
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role'] = $user['role'];
    
    // Update last login
    $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $updateStmt->bind_param("i", $user['id']);
    $updateStmt->execute();
    $updateStmt->close();
    
    // Log activity
    logActivity($user['id'], 'login', 'users', $user['id'], 'User logged in');
    
    $stmt->close();
    $conn->close();
    
    return [
        'success' => true,
        'message' => 'Login berhasil',
        'data' => [
            'username' => $user['username'],
            'full_name' => $user['full_name'],
            'role' => $user['role']
        ]
    ];
}

// Function untuk logout
function logoutUser() {
    if (isLoggedIn()) {
        $userId = $_SESSION['user_id'];
        logActivity($userId, 'logout', 'users', $userId, 'User logged out');
    }
    
    session_unset();
    session_destroy();
    
    return ['success' => true, 'message' => 'Logout berhasil'];
}

// Function untuk log activity
function logActivity($userId, $action, $tableName = null, $recordId = null, $description = null) {
    $conn = getConnection();
    
    if (!$conn) {
        return false;
    }
    
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    
    $stmt = $conn->prepare("INSERT INTO activity_log (user_id, action, table_name, record_id, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $userId, $action, $tableName, $recordId, $description, $ipAddress, $userAgent);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    
    return true;
}

// Function untuk register user baru (hanya admin yang bisa)
function registerUser($username, $password, $fullName, $email, $role = 'guest') {
    $conn = getConnection();
    
    if (!$conn) {
        return ['success' => false, 'message' => 'Database connection failed'];
    }
    
    // Check if username exists
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $checkStmt->bind_param("s", $username);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        $checkStmt->close();
        $conn->close();
        return ['success' => false, 'message' => 'Username sudah digunakan'];
    }
    $checkStmt->close();
    
    // Insert user (simple password storage - in production use password_hash)
    $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, email, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $password, $fullName, $email, $role);
    
    if ($stmt->execute()) {
        $newId = $conn->insert_id;
        $stmt->close();
        $conn->close();
        return [
            'success' => true,
            'message' => 'User berhasil didaftarkan',
            'data' => ['id' => $newId, 'username' => $username]
        ];
    } else {
        $error = $stmt->error;
        $stmt->close();
        $conn->close();
        return ['success' => false, 'message' => 'Gagal mendaftar user: ' . $error];
    }
}
?>