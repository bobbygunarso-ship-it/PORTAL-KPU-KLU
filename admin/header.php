<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - KPU KLU</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        kpu: { orange: '#f97316', dark: '#1e293b' }
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> 
        body { font-family: 'Inter', sans-serif; } 
    </style>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen flex flex-col md:flex-row">
    
    <!-- Sidebar -->
    <aside class="w-full md:w-64 bg-slate-900 text-slate-300 md:min-h-screen flex-shrink-0">
        <div class="p-4 md:p-6 flex justify-between items-center md:block border-b border-slate-800">
            <div>
                <h2 class="text-xl font-bold text-white flex items-center">
                    <i class="fas fa-cogs text-orange-500 mr-3"></i> Admin KPU
                </h2>
                <p class="text-sm mt-1 text-slate-400 hidden md:block">Halo, <?= htmlspecialchars($_SESSION['username']) ?></p>
            </div>
            <button id="adminMobileMenuBtn" class="md:hidden text-slate-300 hover:text-white focus:outline-none">
                <i class="fas fa-bars text-2xl"></i>
            </button>
        </div>
        
        <nav id="adminSidebarNav" class="hidden md:block p-4 space-y-2">
            <a href="index.php" class="flex items-center px-4 py-3 <?= $currentPage == 'index.php' ? 'bg-orange-600 text-white' : 'hover:bg-slate-800 hover:text-white' ?> rounded-lg transition-colors">
                <i class="fas fa-list w-6"></i> Layanan
            </a>
            <a href="banners.php" class="flex items-center px-4 py-3 <?= $currentPage == 'banners.php' ? 'bg-orange-600 text-white' : 'hover:bg-slate-800 hover:text-white' ?> rounded-lg transition-colors">
                <i class="fas fa-images w-6"></i> Banner Utama
            </a>
            <a href="agendas.php" class="flex items-center px-4 py-3 <?= $currentPage == 'agendas.php' ? 'bg-orange-600 text-white' : 'hover:bg-slate-800 hover:text-white' ?> rounded-lg transition-colors">
                <i class="fas fa-calendar-alt w-6"></i> Agenda
            </a>
            <a href="documents.php" class="flex items-center px-4 py-3 <?= $currentPage == 'documents.php' ? 'bg-orange-600 text-white' : 'hover:bg-slate-800 hover:text-white' ?> rounded-lg transition-colors">
                <i class="fas fa-file-pdf w-6"></i> Dokumen
            </a>
            <a href="faqs.php" class="flex items-center px-4 py-3 <?= $currentPage == 'faqs.php' ? 'bg-orange-600 text-white' : 'hover:bg-slate-800 hover:text-white' ?> rounded-lg transition-colors">
                <i class="fas fa-question-circle w-6"></i> Tanya Jawab (FAQ)
            </a>
            <a href="messages.php" class="flex items-center px-4 py-3 <?= $currentPage == 'messages.php' ? 'bg-orange-600 text-white' : 'hover:bg-slate-800 hover:text-white' ?> rounded-lg transition-colors">
                <i class="fas fa-envelope w-6"></i> Pesan Masuk
            </a>
            <a href="settings.php" class="flex items-center px-4 py-3 <?= $currentPage == 'settings.php' ? 'bg-orange-600 text-white' : 'hover:bg-slate-800 hover:text-white' ?> rounded-lg transition-colors">
                <i class="fas fa-cog w-6"></i> Pengaturan
            </a>
            <a href="menus.php" class="flex items-center px-4 py-3 <?= $currentPage == 'menus.php' ? 'bg-orange-600 text-white' : 'hover:bg-slate-800 hover:text-white' ?> rounded-lg transition-colors">
                <i class="fas fa-bars w-6"></i> Menu Header
            </a>
            <a href="widgets.php" class="flex items-center px-4 py-3 <?= $currentPage == 'widgets.php' ? 'bg-orange-600 text-white' : 'hover:bg-slate-800 hover:text-white' ?> rounded-lg transition-colors">
                <i class="fas fa-cubes w-6"></i> Widgets & Asisten AI
            </a>
            <a href="music.php" class="flex items-center px-4 py-3 <?= $currentPage == 'music.php' ? 'bg-orange-600 text-white' : 'hover:bg-slate-800 hover:text-white' ?> rounded-lg transition-colors">
                <i class="fas fa-music w-6"></i> Musik Latar (BGM)
            </a>
            <a href="users.php" class="flex items-center px-4 py-3 <?= $currentPage == 'users.php' ? 'bg-orange-600 text-white' : 'hover:bg-slate-800 hover:text-white' ?> rounded-lg transition-colors">
                <i class="fas fa-users w-6"></i> Pengguna
            </a>
            <a href="backup.php" class="flex items-center px-4 py-3 hover:bg-green-700 hover:text-white text-green-400 rounded-lg transition-colors mt-2 bg-green-950/20 border border-green-900/30">
                <i class="fas fa-database w-6"></i> Backup Database
            </a>
            <hr class="border-slate-800 my-4">
            <a href="../index.php" target="_blank" class="flex items-center px-4 py-3 hover:bg-slate-800 hover:text-white rounded-lg transition-colors text-slate-400">
                <i class="fas fa-external-link-alt w-6"></i> Lihat Website
            </a>
            <a href="../logout.php" class="flex items-center px-4 py-3 hover:bg-red-600 hover:text-white rounded-lg transition-colors text-red-400">
                <i class="fas fa-sign-out-alt w-6"></i> Logout
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="flex-grow p-4 md:p-8 w-full overflow-hidden">
