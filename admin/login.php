<?php
require_once('includes/db_connect.php');
require_once('includes/auth.php');

// Redirect if already logged in
if (isset($_SESSION['admin_loggedin']) && $_SESSION['admin_loggedin'] === true) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password!";
    } else {
        if (adminLogin($username, $password)) {
            header('Location: dashboard.php');
            exit;
        } else {
            $error = "Invalid username or password!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Gujarat Yatra</title>
    <script src="../assets/tailwindcss.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .login-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .form-group {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .form-input {
            width: 100%;
            padding: 12px 16px 12px 48px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f9fafb;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #4f46e5;
            background: white;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        
        .form-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            transition: color 0.3s ease;
        }
        
        .form-input:focus + .form-icon {
            color: #4f46e5;
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(79, 70, 229, 0.3);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-login:hover::before {
            left: 100%;
        }
        
        .demo-credentials {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1.5rem;
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(79, 70, 229, 0.4);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(79, 70, 229, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(79, 70, 229, 0);
            }
        }
        
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        
        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        
        .error-shake {
            animation: shake 0.5s ease-in-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <!-- Background Elements -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-40 -right-32 w-80 h-80 bg-purple-300 rounded-full mix-blend-multiply filter blur-xl opacity-20 floating"></div>
        <div class="absolute -bottom-40 -left-32 w-80 h-80 bg-yellow-300 rounded-full mix-blend-multiply filter blur-xl opacity-20 floating" style="animation-delay: -2s;"></div>
        <div class="absolute top-40 left-1/2 w-80 h-80 bg-pink-300 rounded-full mix-blend-multiply filter blur-xl opacity-20 floating" style="animation-delay: -4s;"></div>
    </div>

    <div class="login-container w-full max-w-md mx-auto pulse">
        <!-- Header -->
        <div class="login-header">
            <div class="flex items-center justify-center mb-4">
                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mr-3">
                    <i class="fas fa-map-marked-alt text-2xl text-white"></i>
                </div>
                <div class="text-left">
                    <h1 class="text-2xl font-bold">Gujarat Yatra</h1>
                    <p class="text-blue-100 text-sm">Admin Portal</p>
                </div>
            </div>
            <p class="text-blue-100">Sign in to access your dashboard</p>
        </div>

        <!-- Login Form -->
        <div class="p-8">
            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center error-shake">
                    <i class="fas fa-exclamation-circle mr-3"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div class="form-group">
                    <input type="text" 
                           id="username" 
                           name="username" 
                           required 
                           value="<?php echo htmlspecialchars($username); ?>"
                           class="form-input"
                           placeholder="Enter your username">
                    <i class="fas fa-user form-icon"></i>
                </div>

                <div class="form-group">
                    <input type="password" 
                           id="password" 
                           name="password" 
                           required 
                           class="form-input"
                           placeholder="Enter your password">
                    <i class="fas fa-lock form-icon"></i>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Sign In
                </button>
            </form>

            <!-- Demo Credentials -->
            <div class="demo-credentials">
                <div class="flex items-center mb-2">
                    <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                    <h3 class="text-sm font-semibold text-blue-900">Demo Credentials</h3>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div>
                        <span class="font-medium text-gray-600">Username:</span>
                        <span class="text-gray-800 font-semibold">admin</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-600">Password:</span>
                        <span class="text-gray-800 font-semibold">Admin@123</span>
                    </div>
                </div>
            </div>

            <!-- Security Notice -->
            <div class="mt-6 text-center">
                <div class="flex items-center justify-center text-gray-500 text-xs">
                    <i class="fas fa-shield-alt mr-2"></i>
                    <span>Your admin session is securely encrypted</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add interactive effects
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.form-input');
            const loginBtn = document.querySelector('.btn-login');
            
            // Add focus effects
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('transform', 'scale-105');
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.classList.remove('transform', 'scale-105');
                });
            });
            
            // Button loading state
            loginBtn.addEventListener('click', function() {
                if (this.classList.contains('loading')) return;
                
                const username = document.getElementById('username').value;
                const password = document.getElementById('password').value;
                
                if (username && password) {
                    this.classList.add('loading');
                    this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Signing In...';
                    
                    // Simulate loading state
                    setTimeout(() => {
                        this.classList.remove('loading');
                        this.innerHTML = '<i class="fas fa-sign-in-alt mr-2"></i>Sign In';
                    }, 2000);
                }
            });
            
            // Auto-hide error message after 5 seconds
            const errorDiv = document.querySelector('.bg-red-50');
            if (errorDiv) {
                setTimeout(() => {
                    errorDiv.style.opacity = '0';
                    errorDiv.style.transition = 'opacity 0.5s ease';
                    setTimeout(() => errorDiv.remove(), 500);
                }, 5000);
            }
            
            // Add character counter for username
            const usernameInput = document.getElementById('username');
            usernameInput.addEventListener('input', function() {
                const charCount = this.value.length;
                const icon = this.parentElement.querySelector('.form-icon');
                
                if (charCount > 0) {
                    icon.style.color = '#10b981';
                } else {
                    icon.style.color = '#9ca3af';
                }
            });
        });
    </script>
</body>
</html>