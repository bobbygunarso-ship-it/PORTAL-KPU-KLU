<?php
require 'config.php';

// Fetch Settings
$stmtSet = $pdo->query("SELECT * FROM settings");
$settingsData = $stmtSet->fetchAll();
$settings = [];
foreach ($settingsData as $row) {
    $settings[$row->key_name] = $row->key_value;
}

// Handle Form Submission
$messageSuccess = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_message'])) {
    $name = $_POST['name'] ?? '';
    $whatsapp = $_POST['whatsapp'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';
    
    if (!empty($name) && !empty($whatsapp) && !empty($message)) {
        $stmtM = $pdo->prepare("INSERT INTO messages (name, whatsapp, subject, message) VALUES (?, ?, ?, ?)");
        $stmtM->execute([$name, $whatsapp, $subject, $message]);
        $messageSuccess = true;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hubungi Kami - <?= htmlspecialchars($settings['site_title'] ?? 'KPU KLU') ?></title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-slate-50 text-slate-800">

    <!-- Header / Navbar -->
    <header class="bg-white/90 backdrop-blur-md shadow-sm sticky top-0 z-50 border-b border-slate-100">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Logo -->
                <a href="index.php" class="flex items-center space-x-3 group">
                    <img src="assets/images/logo-kpu.svg" alt="Logo KPU" class="h-12 w-auto transition-transform duration-300 group-hover:scale-105 drop-shadow-sm">
                    <div class="flex flex-col hidden sm:flex">
                        <span class="font-bold text-lg md:text-xl text-slate-900 leading-none mb-1">KPU Kabupaten</span>
                        <span class="font-extrabold text-base md:text-lg text-orange-600 leading-none uppercase tracking-wide">Lombok Utara</span>
                    </div>
                </a>
                <!-- Back Link -->
                <a href="index.php" class="text-slate-600 hover:text-orange-600 font-semibold flex items-center transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali ke Beranda
                </a>
            </div>
        </div>
    </header>

    <main class="min-h-screen py-16">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-4xl">
            <div class="text-center mb-12">
                <h1 class="text-3xl md:text-4xl font-bold text-slate-900 mb-4">Hubungi Kami / Pengaduan</h1>
                <p class="text-slate-600">Sampaikan pertanyaan, masukan, atau aduan Anda terkait layanan KPU Kabupaten Lombok Utara.</p>
            </div>
            
            <?php if($messageSuccess): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-2xl mb-8 flex items-center shadow-sm">
                <i class="fas fa-check-circle text-green-500 text-2xl mr-4"></i>
                <div>
                    <h4 class="font-bold">Pesan Terkirim!</h4>
                    <p class="text-sm">Terima kasih, pesan atau aduan Anda telah kami terima dan akan segera ditindaklanjuti.</p>
                </div>
            </div>
            <?php endif; ?>

            <form action="kontak.php" method="POST" class="bg-white p-8 md:p-10 rounded-3xl shadow-sm border border-slate-200">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Lengkap</label>
                        <input type="text" name="name" required class="w-full border border-slate-300 bg-slate-50 rounded-xl p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all" placeholder="Masukkan nama Anda">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nomor WhatsApp</label>
                        <input type="text" name="whatsapp" required class="w-full border border-slate-300 bg-slate-50 rounded-xl p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all" placeholder="081234567890">
                    </div>
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Subjek / Topik</label>
                    <input type="text" name="subject" required class="w-full border border-slate-300 bg-slate-50 rounded-xl p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all" placeholder="Contoh: Info Pendaftaran KPPS">
                </div>
                <div class="mb-8">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Isi Pesan</label>
                    <textarea name="message" rows="5" required class="w-full border border-slate-300 bg-slate-50 rounded-xl p-4 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all resize-none" placeholder="Tuliskan pesan, pertanyaan, atau aduan Anda di sini..."></textarea>
                </div>
                <button type="submit" name="submit_message" class="w-full bg-orange-600 text-white font-bold py-4 rounded-xl hover:bg-orange-500 transition-all shadow-lg shadow-orange-600/30 flex items-center justify-center">
                    <i class="fas fa-paper-plane mr-2"></i> Kirim Pesan
                </button>
            </form>
        </div>
    </main>

    <!-- Footer Simple -->
    <footer class="bg-slate-900 text-slate-400 py-6 text-center text-sm border-t-[6px] border-orange-500">
        &copy; <?= date('Y') ?> Komisi Pemilihan Umum Kabupaten Lombok Utara. Hak Cipta Dilindungi.
    </footer>
</body>
</html>
