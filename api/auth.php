<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/auth.php';

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get request data
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

// Get action
$action = $input['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        handleLogin($input);
        break;
    
    case 'logout':
        handleLogout();
        break;
    
    case 'check':
        handleCheck();
        break;
    
    case 'register':
        handleRegister($input);
        break;
    
    default:
        jsonResponse(false, "Invalid action", null, 400);
}

function handleLogin($input) {
    $username = $input['username'] ?? '';
    $password = $input['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        jsonResponse(false, "Username dan password harus diisi", null, 400);
    }
    
    $result = loginUser($username, $password);
    
    if ($result['success']) {
        jsonResponse(true, $result['message'], $result['data'], 200);
    } else {
        jsonResponse(false, $result['message'], null, 401);
    }
}

function handleLogout() {
    $result = logoutUser();
    jsonResponse(true, $result['message'], null, 200);
}

function handleCheck() {
    if (isLoggedIn()) {
        $user = getCurrentUser();
        jsonResponse(true, "Authenticated", [
            'logged_in' => true,
            'user' => $user
        ], 200);
    } else {
        jsonResponse(true, "Not authenticated", [
            'logged_in' => false,
            'user' => null
        ], 200);
    }
}

function handleRegister($input) {
    // Only admin can register new users
    requireAdmin();
    
    $username = $input['username'] ?? '';
    $password = $input['password'] ?? '';
    $fullName = $input['full_name'] ?? '';
    $email = $input['email'] ?? '';
    $role = $input['role'] ?? 'guest';
    
    if (empty($username) || empty($password) || empty($fullName)) {
        jsonResponse(false, "Username, password, dan nama lengkap harus diisi", null, 400);
    }
    
    if (strlen($password) < 6) {
        jsonResponse(false, "Password minimal 6 karakter", null, 400);
    }
    
    $result = registerUser($username, $password, $fullName, $email, $role);
    
    if ($result['success']) {
        // Log activity
        logActivity($_SESSION['user_id'], 'create', 'users', $result['data']['id'], "Registered new user: {$username}");
        jsonResponse(true, $result['message'], $result['data'], 201);
    } else {
        jsonResponse(false, $result['message'], null, 400);
    }
}
?>