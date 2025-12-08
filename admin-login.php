<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Web GIS Sarana Ibadah</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body class="bg-gradient-to-br from-blue-500 to-blue-700 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo & Title -->
        <div class="text-center mb-8">
            <div class="bg-white w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                <i class="fas fa-map-marked-alt text-4xl text-blue-600"></i>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Admin Panel</h1>
            <p class="text-blue-100">Web GIS Sarana Ibadah Bandar Lampung</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Login</h2>
            
            <!-- Alert Container -->
            <div id="alert-container" class="mb-4 hidden">
                <div id="alert" class="p-4 rounded-lg"></div>
            </div>

            <!-- Login Form -->
            <form id="login-form" class="space-y-6">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-2"></i>Username
                    </label>
                    <input type="text" id="username" name="username" required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                        placeholder="Masukkan username">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2"></i>Password
                    </label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition pr-12"
                            placeholder="Masukkan password">
                        <button type="button" onclick="togglePassword()" 
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <i id="password-icon" class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" id="login-btn"
                    class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold py-3 rounded-lg hover:from-blue-700 hover:to-blue-800 transition transform hover:scale-105 shadow-lg">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    <span id="login-text">Login</span>
                    <i id="login-spinner" class="fas fa-spinner fa-spin ml-2 hidden"></i>
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="index.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali ke Halaman Utama
                </a>
            </div>

            <!-- Demo Credentials -->
            <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                <p class="text-xs text-gray-600 font-semibold mb-2">Demo Credentials:</p>
                <div class="text-xs text-gray-600 space-y-1">
                    <p><strong>Username:</strong> admin</p>
                    <p><strong>Password:</strong> admin123</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6 text-blue-100 text-sm">
            <p>&copy; 2024 Web GIS Sarana Ibadah Bandar Lampung</p>
        </div>
    </div>

    <script>
        const API_URL = 'api/auth.php';

        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordIcon = document.getElementById('password-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.classList.remove('fa-eye');
                passwordIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordIcon.classList.remove('fa-eye-slash');
                passwordIcon.classList.add('fa-eye');
            }
        }

        // Show alert
        function showAlert(message, type = 'error') {
            const alertContainer = document.getElementById('alert-container');
            const alert = document.getElementById('alert');
            
            alertContainer.classList.remove('hidden');
            
            if (type === 'error') {
                alert.className = 'p-4 rounded-lg bg-red-100 border border-red-400 text-red-700';
                alert.innerHTML = `<i class="fas fa-exclamation-circle mr-2"></i>${message}`;
            } else if (type === 'success') {
                alert.className = 'p-4 rounded-lg bg-green-100 border border-green-400 text-green-700';
                alert.innerHTML = `<i class="fas fa-check-circle mr-2"></i>${message}`;
            }
            
            setTimeout(() => {
                alertContainer.classList.add('hidden');
            }, 5000);
        }

        // Handle login
        document.getElementById('login-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            
            const loginBtn = document.getElementById('login-btn');
            const loginText = document.getElementById('login-text');
            const loginSpinner = document.getElementById('login-spinner');
            
            // Disable button and show spinner
            loginBtn.disabled = true;
            loginText.textContent = 'Loading...';
            loginSpinner.classList.remove('hidden');
            
            try {
                const response = await fetch(API_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'login',
                        username: username,
                        password: password
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert('Login berhasil! Mengalihkan...', 'success');
                    setTimeout(() => {
                        window.location.href = 'admin-dashboard.php';
                    }, 1000);
                } else {
                    showAlert(result.message || 'Login gagal', 'error');
                    loginBtn.disabled = false;
                    loginText.textContent = 'Login';
                    loginSpinner.classList.add('hidden');
                }
            } catch (error) {
                console.error('Login error:', error);
                showAlert('Terjadi kesalahan saat login', 'error');
                loginBtn.disabled = false;
                loginText.textContent = 'Login';
                loginSpinner.classList.add('hidden');
            }
        });
    </script>
</body>
</html>