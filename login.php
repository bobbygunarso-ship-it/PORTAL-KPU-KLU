<?php
session_start();
require 'config.php';

// Jika sudah login, redirect ke admin
if (isset($_SESSION['user_id'])) {
    header("Location: admin/index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = 'Username dan password wajib diisi.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user->password)) {
            // Login sukses
            $_SESSION['user_id'] = $user->id;
            $_SESSION['username'] = $user->username;
            header("Location: admin/index.php");
            exit;
        } else {
            // Login gagal
            $error = 'Username atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Manajemen - KPU Kabupaten Lombok Utara</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        kpu: {
                            orange: '#f97316', // orange-500
                            dark: '#1e293b',   // slate-800
                            darker: '#0f172a'  // slate-900
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .login-bg {
            background-image: linear-gradient(rgba(15, 23, 42, 0.85), rgba(249, 115, 22, 0.65)), url('https://images.unsplash.com/photo-1541802082853-48b4d82f25df?auto=format&fit=crop&q=80&w=2070');
            background-size: cover;
            background-position: center;
        }
    </style>
</head>
<body class="font-sans text-slate-800 bg-white antialiased h-screen flex overflow-hidden">
    
    <!-- Left Section - Image & Branding -->
    <div class="hidden lg:flex lg:w-1/2 login-bg flex-col justify-between p-14 text-white relative">
        <div class="relative z-10">
            <a href="index.php" class="inline-block">
                <img src="assets/images/logo-kpu.svg" alt="Logo KPU" class="h-16 w-auto mb-8 drop-shadow-md brightness-110 hover:scale-105 transition-transform duration-300">
            </a>
        </div>
        
        <div class="space-y-6 max-w-lg relative z-10 mb-20">
            <div class="inline-block px-4 py-1.5 rounded-full bg-white/20 text-white font-semibold text-xs tracking-widest uppercase backdrop-blur-md border border-white/30 shadow-lg">
                Sistem Manajemen Portal
            </div>
            <h1 class="text-4xl xl:text-5xl font-extrabold leading-tight drop-shadow-lg">
                Selamat Datang di <br/>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-200 to-white">Admin Panel</span>
            </h1>
            <p class="text-lg text-slate-200 font-light drop-shadow-md mt-4 leading-relaxed">
                Platform terpusat untuk mengelola konten website, berita, layanan publik, dan informasi pemilu KPU Kabupaten Lombok Utara.
            </p>
        </div>
        
        <div class="text-sm text-slate-300 relative z-10 flex items-center">
            <i class="fas fa-shield-halved mr-2 text-orange-400"></i> Sistem diamankan dengan enkripsi end-to-end.
        </div>
    </div>

    <!-- Right Section - Login Form -->
    <div class="w-full lg:w-1/2 flex flex-col justify-center items-center p-8 sm:p-12 md:p-24 bg-white relative overflow-y-auto">
        <!-- Mobile Logo (hidden on large screens) -->
        <div class="lg:hidden absolute top-8 left-8 flex items-center space-x-3">
            <img src="assets/images/logo-kpu.svg" alt="Logo KPU" class="h-12 w-auto">
        </div>

        <div class="w-full max-w-md mt-16 lg:mt-0">
            <div class="mb-10 text-center lg:text-left">
                <h2 class="text-3xl font-bold text-slate-900 mb-3">Masuk ke Akun</h2>
                <p class="text-slate-500">Silakan masukkan kredensial Anda untuk mengakses panel manajemen.</p>
            </div>

            <?php if ($error): ?>
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-md">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-500"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700 font-medium"><?= htmlspecialchars($error) ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="space-y-6">
                <!-- Username / Email -->
                <div>
                    <label for="username" class="block text-sm font-semibold text-slate-700 mb-2">Username atau Email</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors group-focus-within:text-orange-500">
                            <i class="fas fa-user text-slate-400 group-focus-within:text-orange-500 transition-colors"></i>
                        </div>
                        <input type="text" id="username" name="username" class="block w-full pl-11 pr-4 py-3.5 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all bg-slate-50 focus:bg-white shadow-sm" placeholder="admin" required>
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">Password</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-slate-400 group-focus-within:text-orange-500 transition-colors"></i>
                        </div>
                        <input type="password" id="password" name="password" class="block w-full pl-11 pr-12 py-3.5 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all bg-slate-50 focus:bg-white shadow-sm" placeholder="••••••••" required>
                        <button type="button" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-orange-500 transition-colors focus:outline-none" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-4">
                    <button type="submit" class="w-full flex justify-center py-4 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-orange-600 hover:bg-orange-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-300 hover:shadow-lg hover:shadow-orange-600/30 transform hover:-translate-y-0.5">
                        <i class="fas fa-sign-in-alt mr-2 text-base"></i> Masuk ke Dasbor
                    </button>
                </div>
            </form>

            <div class="mt-10 pt-8 border-t border-slate-100">
                <a href="index.php" class="flex items-center justify-center text-sm font-medium text-slate-500 hover:text-slate-800 transition-colors group">
                    <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center mr-3 group-hover:bg-slate-200 transition-colors">
                        <i class="fas fa-arrow-left text-slate-500 group-hover:text-slate-700"></i>
                    </div>
                    Kembali ke Portal Utama
                </a>
            </div>
            
            <div class="mt-12 text-center text-sm text-slate-400 lg:hidden">
                &copy; 2026 KPU Kabupaten Lombok Utara
            </div>
        </div>
    </div>

    <!-- Script to toggle password visibility -->
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        const eyeIcon = togglePassword.querySelector('i');

        togglePassword.addEventListener('click', function (e) {
            e.preventDefault();
            
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            if (type === 'password') {
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            } else {
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            }
        });
    </script>
</body>
</html>
