<?php
require 'config.php';

// Log Visitor
$ip = $_SERVER['REMOTE_ADDR'];
$date = date('Y-m-d');
$time = date('H:i:s');
try {
    $stmt = $pdo->prepare("INSERT IGNORE INTO visitor_logs (ip_address, visit_date, visit_time) VALUES (?, ?, ?)");
    $stmt->execute([$ip, $date, $time]);
} catch (PDOException $e) {
    // Ignore duplicate entry errors
}

// Fetch Settings
$stmtSet = $pdo->query("SELECT * FROM settings");
$settingsData = $stmtSet->fetchAll();
$settings = [];
foreach ($settingsData as $row) {
    $settings[$row->key_name] = $row->key_value;
}




// Fetch Active Banners
$stmtBan = $pdo->query("SELECT * FROM banners WHERE is_active = 1 ORDER BY order_num ASC, id DESC");
$banners = $stmtBan->fetchAll();

// Fetch Kepegawaian
$stmtKep = $pdo->query("SELECT * FROM services WHERE type = 'kepegawaian' ORDER BY id ASC");
$layananKepegawaian = $stmtKep->fetchAll();

// Fetch Publik
$stmtPub = $pdo->query("SELECT * FROM services WHERE type = 'publik' ORDER BY id ASC");
$layananPublik = $stmtPub->fetchAll();

// Fetch Agendas
$stmtAgenda = $pdo->query("SELECT * FROM agendas ORDER BY start_date ASC");
$agendas = $stmtAgenda->fetchAll();

// Fetch Documents
$stmtDoc = $pdo->query("SELECT * FROM documents ORDER BY id DESC LIMIT 10");
$documents = $stmtDoc->fetchAll();

// Fetch Menus
$stmtMenu = $pdo->query("SELECT * FROM menus WHERE is_active = 1 ORDER BY order_num ASC");
$headerMenus = $stmtMenu->fetchAll();

// Fetch FAQs
$stmtFaq = $pdo->query("SELECT * FROM faqs WHERE is_active = 1 ORDER BY order_num ASC, id DESC");
$faqs = $stmtFaq->fetchAll();

// Format for FullCalendar
$calendarEvents = [];
foreach($agendas as $a) {
    $calendarEvents[] = [
        'id' => $a->id,
        'title' => $a->title,
        'start' => date('Y-m-d\TH:i:s', strtotime($a->start_date)),
        'end' => date('Y-m-d\TH:i:s', strtotime($a->end_date)),
        'color' => $a->status == 'open' ? '#ea580c' : ($a->status == 'closed' ? '#64748b' : '#ef4444'),
        'extendedProps' => [
            'description' => $a->description,
            'location' => $a->location
        ]
    ];
}
$calendarEventsJson = json_encode($calendarEvents);
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($settings['site_title'] ?? 'Portal Layanan KPU KLU') ?></title>
    
    <!-- PWA Meta Tags -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#ea580c">
    <link rel="apple-touch-icon" href="assets/images/logo-kpu.svg">
    
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
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(249, 115, 22, 0.2), 0 8px 10px -6px rgba(249, 115, 22, 0.1);
            border-color: #f97316;
        }
        
        .hero-pattern {
            background-image: linear-gradient(rgba(15, 23, 42, 0.7), rgba(15, 23, 42, 0.9)), url('https://images.unsplash.com/photo-1579294314136-124449830573?auto=format&fit=crop&q=80&w=2000');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9; 
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1; 
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8; 
        }
    </style>
</head>
<body class="font-sans text-slate-800 bg-slate-50 antialiased flex flex-col min-h-screen">

    <!-- Header / Navbar -->
    <header class="bg-white shadow-sm sticky top-0 z-50 border-b border-slate-200">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo & Title -->
                <div class="flex items-center space-x-4">
                    <img src="assets/images/logo-kpu.svg" alt="Logo KPU" class="h-12 w-auto">
                    <div class="flex flex-col">
                        <span class="font-bold text-lg md:text-xl text-slate-900 leading-none mb-1">KPU Kabupaten</span>
                        <span class="font-extrabold text-base md:text-lg text-orange-600 leading-none uppercase tracking-wide">Lombok Utara</span>
                    </div>
                </div>

                <!-- Desktop Navigation -->
                <nav class="hidden md:flex space-x-8">
                    <?php foreach($headerMenus as $menu): ?>
                    <a href="<?= htmlspecialchars($menu->url) ?>" class="text-slate-600 hover:text-orange-600 font-medium transition-colors border-b-2 border-transparent hover:border-orange-600 py-2">
                        <?= htmlspecialchars($menu->title) ?>
                    </a>
                    <?php endforeach; ?>
                    <a href="login.php" class="text-white bg-orange-600 hover:bg-orange-500 rounded-lg px-4 py-2 font-medium transition-colors border border-transparent shadow-sm"><i class="fas fa-lock text-sm mr-1"></i> Admin</a>
                </nav>

                <!-- Mobile Menu Button -->
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-btn" class="text-slate-600 hover:text-orange-600 focus:outline-none p-2 transition-colors">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile Navigation -->
        <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-slate-100 shadow-inner">
            <div class="px-4 pt-2 pb-4 space-y-1">
                <?php foreach($headerMenus as $menu): ?>
                <a href="<?= htmlspecialchars($menu->url) ?>" class="block px-3 py-3 rounded-md text-base font-medium text-slate-700 hover:text-orange-600 hover:bg-orange-50 transition-colors">
                    <?= htmlspecialchars($menu->title) ?>
                </a>
                <?php endforeach; ?>
                <a href="login.php" class="block px-3 py-3 rounded-md text-base font-medium text-orange-600 hover:bg-orange-50 transition-colors"><i class="fas fa-lock text-sm mr-1"></i> Admin Login</a>
            </div>
        </div>
    </header>

    <main class="flex-grow">
        <!-- Hero Section Slider -->
        <section id="beranda" class="relative h-[80vh] min-h-[600px] overflow-hidden flex items-center justify-center text-center px-4">
            
            <!-- Slider Backgrounds -->
            <div id="hero-slider" class="absolute inset-0 z-0 w-full h-full">
                <?php foreach($banners as $index => $banner): ?>
                <div class="slider-slide absolute inset-0 w-full h-full transition-opacity duration-1000 ease-in-out <?= $index === 0 ? 'opacity-100' : 'opacity-0' ?>" data-index="<?= $index ?>">
                    <div class="absolute inset-0 bg-slate-900/70 z-10"></div> <!-- Overlay -->
                    <?php if($banner->media_type == 'youtube'): 
                        // Extract YouTube ID
                        preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i', $banner->image_url, $match);
                        $yt_id = $match[1] ?? '';
                    ?>
                        <?php if($yt_id): ?>
                            <iframe class="absolute top-1/2 left-1/2 w-[300vw] h-[300vh] min-w-full min-h-full -translate-x-1/2 -translate-y-1/2 pointer-events-none" 
                                    src="https://www.youtube.com/embed/<?= $yt_id ?>?autoplay=1&mute=1&loop=1&playlist=<?= $yt_id ?>&controls=0&showinfo=0&rel=0&modestbranding=1" 
                                    frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                        <?php endif; ?>
                    <?php elseif($banner->media_type == 'video'): ?>
                        <video src="<?= strpos($banner->image_url, 'http') === 0 ? htmlspecialchars($banner->image_url) : htmlspecialchars($banner->image_url) ?>" class="w-full h-full object-cover" autoplay loop muted playsinline></video>
                    <?php else: ?>
                        <img src="<?= strpos($banner->image_url, 'http') === 0 ? htmlspecialchars($banner->image_url) : htmlspecialchars($banner->image_url) ?>" class="w-full h-full object-cover">
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Slider Content -->
            <div class="relative z-10 max-w-5xl mx-auto space-y-6">
                <div class="inline-block px-5 py-2 rounded-full bg-slate-900/50 text-orange-400 font-semibold text-sm tracking-widest uppercase mb-2 border border-orange-500/30 backdrop-blur-md">
                    Portal Layanan Terpadu
                </div>
                
                <div id="hero-content">
                    <?php foreach($banners as $index => $banner): ?>
                    <div class="slider-text transition-opacity duration-700 <?= $index === 0 ? 'block' : 'hidden' ?>" data-index="<?= $index ?>">
                        <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-white leading-tight drop-shadow-2xl">
                            <?= htmlspecialchars($banner->title) ?>
                        </h1>
                        <p class="mt-6 text-lg md:text-xl text-slate-200 font-light max-w-3xl mx-auto drop-shadow-md">
                            <?= htmlspecialchars($banner->subtitle) ?>
                        </p>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="pt-10 flex flex-col sm:flex-row justify-center gap-4">
                    <a href="#layanan-publik" class="px-8 py-4 bg-orange-600 hover:bg-orange-500 text-white rounded-lg font-semibold transition-all duration-300 shadow-lg shadow-orange-600/30 flex items-center justify-center gap-3 transform hover:-translate-y-1">
                        <i class="fas fa-users text-lg"></i> Layanan Publik
                    </a>
                    <a href="#layanan-kepegawaian" class="px-8 py-4 bg-slate-800/80 hover:bg-slate-700 text-white rounded-lg font-semibold transition-all duration-300 shadow-lg border border-slate-600 backdrop-blur-md flex items-center justify-center gap-3 transform hover:-translate-y-1">
                        <i class="fas fa-id-badge text-lg"></i> Layanan Internal
                    </a>
                </div>
            </div>
            
            <?php if(count($banners) > 1): ?>
            <!-- Slider Controls -->
            <button onclick="prevSlide()" class="absolute left-4 top-1/2 -translate-y-1/2 z-20 w-12 h-12 bg-black/30 hover:bg-orange-600 text-white rounded-full flex items-center justify-center transition-colors">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button onclick="nextSlide()" class="absolute right-4 top-1/2 -translate-y-1/2 z-20 w-12 h-12 bg-black/30 hover:bg-orange-600 text-white rounded-full flex items-center justify-center transition-colors">
                <i class="fas fa-chevron-right"></i>
            </button>
            <?php endif; ?>
        </section>

        <script>
            let currentSlide = 0;
            const slides = document.querySelectorAll('.slider-slide');
            const texts = document.querySelectorAll('.slider-text');
            const totalSlides = slides.length;

            function showSlide(index) {
                if (totalSlides <= 1) return;
                
                slides.forEach((s) => {
                    s.classList.remove('opacity-100');
                    s.classList.add('opacity-0');
                });
                texts.forEach((t) => {
                    t.classList.remove('block');
                    t.classList.add('hidden');
                });
                
                slides[index].classList.remove('opacity-0');
                slides[index].classList.add('opacity-100');
                
                texts[index].classList.remove('hidden');
                texts[index].classList.add('block');
            }

            function nextSlide() {
                currentSlide = (currentSlide + 1) % totalSlides;
                showSlide(currentSlide);
            }

            function prevSlide() {
                currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
                showSlide(currentSlide);
            }

            if (totalSlides > 1) {
                setInterval(nextSlide, 5000); // Auto slide 5s
            }
        </script>


        <!-- Layanan Kepegawaian Section -->
        <section id="layanan-kepegawaian" class="py-24 bg-slate-50 relative border-b border-slate-200">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mb-4 relative inline-block">
                        Layanan Kepegawaian
                        <span class="absolute -bottom-3 left-1/2 transform -translate-x-1/2 w-24 h-1.5 bg-orange-500 rounded-full"></span>
                    </h2>
                    <p class="text-slate-600 mt-6 text-lg">Sistem informasi dan portal layanan khusus untuk ASN dan Pegawai di lingkungan KPU Kabupaten Lombok Utara.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 xl:gap-8">
                    <?php if(empty($layananKepegawaian)): ?>
                        <div class="col-span-full text-center text-slate-500 py-10">Belum ada data layanan kepegawaian.</div>
                    <?php endif; ?>
                    
                    <?php foreach($layananKepegawaian as $item): ?>
                    <a href="<?= htmlspecialchars($item->url) ?>" class="glass-card bg-white rounded-2xl p-8 border border-slate-200 group flex flex-col items-center text-center shadow-sm">
                        <div class="w-20 h-20 rounded-2xl bg-slate-50 group-hover:bg-orange-50 flex items-center justify-center text-slate-500 group-hover:text-orange-600 transition-colors duration-300 mb-6 shadow-inner border border-slate-100 group-hover:border-orange-100">
                            <i class="<?= htmlspecialchars($item->icon) ?> text-4xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-3 group-hover:text-orange-600 transition-colors"><?= htmlspecialchars($item->title) ?></h3>
                        <p class="text-slate-500 text-sm leading-relaxed"><?= htmlspecialchars($item->description) ?></p>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Layanan Publik Section -->
        <section id="layanan-publik" class="py-24 bg-white relative">
            <!-- decorative background blob -->
            <div class="absolute top-0 right-0 -mt-20 -mr-20 w-80 h-80 bg-orange-50 rounded-full blur-3xl opacity-50 pointer-events-none"></div>
            
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mb-4 relative inline-block">
                        Layanan Publik
                        <span class="absolute -bottom-3 left-1/2 transform -translate-x-1/2 w-24 h-1.5 bg-orange-500 rounded-full"></span>
                    </h2>
                    <p class="text-slate-600 mt-6 text-lg">Akses informasi, data, dan layanan kepemiluan untuk masyarakat Kabupaten Lombok Utara secara cepat dan transparan.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-24">
                    <?php if(empty($layananPublik)): ?>
                        <div class="col-span-full text-center text-slate-500 py-10">Belum ada data layanan publik.</div>
                    <?php endif; ?>

                    <?php foreach($layananPublik as $item): ?>
                    <a href="<?= htmlspecialchars($item->url) ?>" class="group block">
                        <div class="bg-white border border-slate-100 rounded-3xl overflow-hidden shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] hover:shadow-[0_10px_30px_-5px_rgba(249,115,22,0.15)] hover:border-orange-200 transition-all duration-300 h-full flex flex-col p-8">
                            <div class="w-16 h-16 rounded-2xl bg-orange-50 flex items-center justify-center text-orange-600 group-hover:bg-orange-500 group-hover:text-white transition-colors duration-300 mb-6">
                                <i class="<?= htmlspecialchars($item->icon) ?> text-3xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-slate-800 mb-3 group-hover:text-orange-600 transition-colors"><?= htmlspecialchars($item->title) ?></h3>
                            <p class="text-slate-600 text-sm leading-relaxed flex-grow"><?= htmlspecialchars($item->description) ?></p>
                            <div class="mt-6 flex items-center text-sm font-semibold text-orange-600 opacity-0 group-hover:opacity-100 transform translate-y-2 group-hover:translate-y-0 transition-all duration-300">
                                Kunjungi Portal <i class="fas fa-arrow-right ml-2"></i>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>

                <!-- Pusat Unduhan Dokumen -->
                <div class="bg-slate-50 rounded-3xl p-8 md:p-12 border border-slate-200">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
                        <div>
                            <h3 class="text-2xl font-bold text-slate-800 mb-2 flex items-center">
                                <i class="fas fa-folder-open text-orange-500 mr-3"></i> Pusat Unduhan
                            </h3>
                            <p class="text-slate-600">Download regulasi, formulir pendaftaran, dan dokumen penting lainnya.</p>
                        </div>
                        <a href="#" class="bg-white text-slate-700 hover:text-orange-600 hover:border-orange-200 px-5 py-2.5 rounded-xl border border-slate-200 font-semibold transition-all shadow-sm flex items-center shrink-0">
                            Lihat Semua <i class="fas fa-arrow-right ml-2 text-xs"></i>
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach($documents as $doc): ?>
                        <?php
                            $ext = strtolower(pathinfo($doc->file_url, PATHINFO_EXTENSION));
                            $icon = 'fa-file';
                            $color = 'text-slate-400';
                            if($ext == 'pdf') { $icon = 'fa-file-pdf'; $color = 'text-red-500'; }
                            elseif(in_array($ext, ['doc','docx'])) { $icon = 'fa-file-word'; $color = 'text-blue-500'; }
                            elseif(in_array($ext, ['xls','xlsx'])) { $icon = 'fa-file-excel'; $color = 'text-green-500'; }
                        ?>
                        <div class="bg-white p-5 rounded-2xl border border-slate-200 hover:border-orange-300 hover:shadow-md transition-all flex items-center justify-between group">
                            <div class="flex items-center space-x-4 overflow-hidden">
                                <div class="w-12 h-12 rounded-xl bg-slate-50 flex items-center justify-center shrink-0">
                                    <i class="fas <?= $icon ?> <?= $color ?> text-2xl"></i>
                                </div>
                                <div class="truncate">
                                    <h4 class="font-bold text-slate-800 text-sm md:text-base group-hover:text-orange-600 transition-colors truncate"><?= htmlspecialchars($doc->title) ?></h4>
                                    <div class="text-xs text-slate-500 mt-1 flex items-center">
                                        <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded mr-2 font-semibold uppercase"><?= htmlspecialchars($doc->category) ?></span>
                                        <span><?= date('d M Y', strtotime($doc->created_at)) ?></span>
                                    </div>
                                </div>
                            </div>
                            <a href="<?= htmlspecialchars($doc->file_url) ?>" target="_blank" class="w-10 h-10 rounded-full bg-slate-50 group-hover:bg-orange-500 text-slate-400 group-hover:text-white flex items-center justify-center shrink-0 transition-colors">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                        <?php endforeach; ?>
                        
                        <?php if(empty($documents)): ?>
                        <div class="col-span-full text-center text-slate-400 py-8 bg-white rounded-2xl border border-slate-100">
                            Belum ada dokumen yang tersedia untuk diunduh.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
        <!-- Agenda Section -->
        <section id="agenda" class="py-24 bg-white relative border-b border-slate-200">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 mb-4 tracking-tight">Agenda <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-500 to-orange-600">Kegiatan</span></h2>
                    <p class="text-lg text-slate-500">Jadwal kegiatan, sosialisasi, dan rapat koordinasi Komisi Pemilihan Umum Kabupaten Lombok Utara.</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Daftar Agenda -->
                    <div class="lg:col-span-1">
                        <h3 class="text-xl font-bold mb-6 flex items-center border-l-4 border-orange-500 pl-3 text-slate-800">Daftar Agenda</h3>
                        <div class="space-y-4 max-h-[600px] overflow-y-auto pr-2 custom-scrollbar">
                            <?php 
                            $upcomingAgendas = array_filter($agendas, function($a) { return $a->status == 'open' && strtotime($a->end_date) > time(); });
                            if(empty($upcomingAgendas)) {
                                $upcomingAgendas = array_slice($agendas, 0, 5);
                            } else {
                                $upcomingAgendas = array_slice($upcomingAgendas, 0, 5);
                            }
                            ?>
                            
                            <?php foreach($upcomingAgendas as $a): ?>
                            <div class="bg-slate-50 border border-slate-200 p-5 rounded-2xl hover:border-orange-300 hover:shadow-md transition-all group">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-bold text-slate-800 group-hover:text-orange-600 transition-colors leading-tight pr-2"><?= htmlspecialchars($a->title) ?></h4>
                                    <?php if($a->status == 'open'): ?>
                                        <span class="bg-green-100 text-green-700 text-[10px] px-2 py-1 rounded font-bold uppercase border border-green-200 shrink-0">Open</span>
                                    <?php elseif($a->status == 'closed'): ?>
                                        <span class="bg-slate-200 text-slate-600 text-[10px] px-2 py-1 rounded font-bold uppercase border border-slate-300 shrink-0">Closed</span>
                                    <?php else: ?>
                                        <span class="bg-red-100 text-red-700 text-[10px] px-2 py-1 rounded font-bold uppercase border border-red-200 shrink-0">Batal</span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-xs text-slate-500 space-y-1 mb-3">
                                    <div class="flex items-center"><i class="far fa-clock w-4 text-orange-400"></i> <?= date('d M Y, H:i', strtotime($a->start_date)) ?></div>
                                    <div class="flex items-center"><i class="fas fa-map-marker-alt w-4 text-orange-400"></i> <?= htmlspecialchars($a->location) ?></div>
                                </div>
                                <?php if($a->description): ?>
                                <p class="text-sm text-slate-600 line-clamp-2"><?= htmlspecialchars($a->description) ?></p>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                            
                            <?php if(empty($agendas)): ?>
                            <div class="text-center p-8 bg-slate-50 rounded-2xl border border-slate-200 text-slate-400">
                                Belum ada agenda yang dijadwalkan.
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Kalender -->
                    <div class="lg:col-span-2">
                        <h3 class="text-xl font-bold mb-6 flex items-center border-l-4 border-orange-500 pl-3 text-slate-800">Kalender Publik</h3>
                        <div class="bg-white p-2 md:p-6 rounded-2xl border border-slate-200 shadow-sm relative z-0">
                            <div id="calendar"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        

        <!-- FAQ Section -->
        <section id="faq" class="py-24 bg-slate-50 relative border-b border-slate-200">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-4xl">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 mb-4 tracking-tight">Tanya & Jawab <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-500 to-orange-600">(FAQ)</span></h2>
                    <p class="text-lg text-slate-500">Pertanyaan yang paling sering ditanyakan masyarakat seputar kegiatan dan layanan KPU Lombok Utara.</p>
                </div>

                <div class="space-y-4">
                    <?php foreach($faqs as $index => $faq): ?>
                    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden transition-all duration-300">
                        <button onclick="toggleFaq(<?= $index ?>)" class="w-full px-6 py-5 flex items-center justify-between font-bold text-slate-800 text-left hover:text-orange-600 transition-colors focus:outline-none group">
                            <span><?= htmlspecialchars($faq->question) ?></span>
                            <i id="faq-icon-<?= $index ?>" class="fas fa-chevron-down text-slate-400 group-hover:text-orange-500 transition-transform duration-300"></i>
                        </button>
                        <div id="faq-answer-<?= $index ?>" class="max-h-0 overflow-hidden transition-all duration-300 ease-in-out">
                            <div class="px-6 pb-6 text-slate-600 text-sm md:text-base leading-relaxed border-t border-slate-100 pt-4 bg-slate-50/50">
                                <?= nl2br(htmlspecialchars($faq->answer)) ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if(empty($faqs)): ?>
                    <div class="text-center p-8 bg-white rounded-2xl border border-slate-200 text-slate-400">
                        Belum ada Tanya Jawab yang ditambahkan.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <script>
            function toggleFaq(index) {
                const answer = document.getElementById(`faq-answer-${index}`);
                const icon = document.getElementById(`faq-icon-${index}`);
                
                // Toggle active state
                if (answer.style.maxHeight && answer.style.maxHeight !== '0px') {
                    answer.style.maxHeight = '0px';
                    icon.classList.remove('rotate-180');
                } else {
                    // Close all others first
                    document.querySelectorAll('[id^="faq-answer-"]').forEach((el, idx) => {
                        el.style.maxHeight = '0px';
                        const otherIcon = document.getElementById(`faq-icon-${idx}`);
                        if(otherIcon) otherIcon.classList.remove('rotate-180');
                    });
                    
                    answer.style.maxHeight = answer.scrollHeight + 'px';
                    icon.classList.add('rotate-180');
                }
            }
        </script>

    </main>

    <!-- Footer -->
    <footer id="kontak" class="bg-slate-900 text-slate-300 pt-20 pb-8 border-t-[6px] border-orange-500">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-10 lg:gap-12 mb-16">
                <!-- Branding & Address -->
                <div class="md:col-span-12 lg:col-span-5">
                    <div class="flex items-center space-x-4 mb-6 bg-slate-800/50 p-5 rounded-2xl inline-flex border border-slate-700/50">
                        <img src="assets/images/logo-kpu.svg" alt="Logo KPU" class="h-16 w-auto brightness-110 drop-shadow-md">
                        <div class="flex flex-col">
                            <span class="font-bold text-xl text-white leading-tight mb-1">KPU Kabupaten</span>
                            <span class="font-extrabold text-lg text-orange-500 leading-tight uppercase tracking-wider">Lombok Utara</span>
                        </div>
                    </div>
                    <p class="text-slate-400 mb-8 leading-relaxed text-sm md:text-base max-w-md">
                        <?= nl2br(htmlspecialchars($settings['site_desc'] ?? '')) ?>
                    </p>
                    <div class="space-y-3">
                        <div class="flex items-start space-x-4 text-slate-300 bg-slate-800/30 p-4 rounded-xl border border-slate-800">
                            <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center flex-shrink-0 border border-slate-700">
                                <i class="fas fa-location-dot text-orange-500"></i>
                            </div>
                            <span class="leading-relaxed text-sm pt-1"><?= nl2br(htmlspecialchars($settings['address'] ?? '')) ?></span>
                        </div>
                        
                        <?php if(!empty($settings['phone'])): ?>
                        <div class="flex items-center space-x-4 text-slate-300 bg-slate-800/30 p-4 rounded-xl border border-slate-800">
                            <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center flex-shrink-0 border border-slate-700">
                                <i class="fas fa-phone text-orange-500"></i>
                            </div>
                            <span class="leading-relaxed text-sm"><?= htmlspecialchars($settings['phone']) ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if(!empty($settings['email'])): ?>
                        <div class="flex items-center space-x-4 text-slate-300 bg-slate-800/30 p-4 rounded-xl border border-slate-800">
                            <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center flex-shrink-0 border border-slate-700">
                                <i class="fas fa-envelope text-orange-500"></i>
                            </div>
                            <span class="leading-relaxed text-sm"><?= htmlspecialchars($settings['email']) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tautan Cepat -->
                <div class="md:col-span-6 lg:col-span-3 lg:col-start-7">
                    <h4 class="text-white text-lg font-bold mb-6 flex items-center">
                        <span class="w-2 h-6 bg-orange-500 rounded mr-3"></span>
                        Tautan Cepat
                    </h4>
                    <ul class="space-y-4">
                        <li><a href="#beranda" class="text-slate-400 hover:text-orange-400 transition-colors flex items-center group"><i class="fas fa-chevron-right mr-3 text-xs text-slate-600 group-hover:text-orange-500 transition-colors"></i> Beranda</a></li>
                        <li><a href="#layanan-kepegawaian" class="text-slate-400 hover:text-orange-400 transition-colors flex items-center group"><i class="fas fa-chevron-right mr-3 text-xs text-slate-600 group-hover:text-orange-500 transition-colors"></i> Layanan Kepegawaian</a></li>
                        <li><a href="#layanan-publik" class="text-slate-400 hover:text-orange-400 transition-colors flex items-center group"><i class="fas fa-chevron-right mr-3 text-xs text-slate-600 group-hover:text-orange-500 transition-colors"></i> Layanan Publik</a></li>
                        <li><a href="https://kpu.go.id" target="_blank" class="text-slate-400 hover:text-orange-400 transition-colors flex items-center group"><i class="fas fa-chevron-right mr-3 text-xs text-slate-600 group-hover:text-orange-500 transition-colors"></i> Website KPU RI</a></li>
                    <li><a href="kontak.php" class="text-slate-400 hover:text-orange-400 transition-colors flex items-center group"><i class="fas fa-chevron-right mr-3 text-xs text-slate-600 group-hover:text-orange-500 transition-colors"></i> Hubungi Kami</a></li>
                        </ul>
                </div>

                <!-- Media Sosial -->
                <div class="md:col-span-6 lg:col-span-3">
                    <h4 class="text-white text-lg font-bold mb-6 flex items-center">
                        <span class="w-2 h-6 bg-orange-500 rounded mr-3"></span>
                        Media Sosial
                    </h4>
                    <p class="text-slate-400 mb-6 text-sm leading-relaxed">Ikuti kami di media sosial untuk mendapatkan informasi dan pembaruan terkini seputar kepemiluan.</p>
                    <div class="flex space-x-3">
                        <?php if(!empty($settings['facebook'])): ?>
                        <a href="<?= htmlspecialchars($settings['facebook']) ?>" target="_blank" class="w-12 h-12 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-300 hover:bg-[#1877F2] hover:border-[#1877F2] hover:text-white transition-all duration-300 hover:-translate-y-1" aria-label="Facebook">
                            <i class="fab fa-facebook-f text-lg"></i>
                        </a>
                        <?php endif; ?>
                        
                        <?php if(!empty($settings['twitter'])): ?>
                        <a href="<?= htmlspecialchars($settings['twitter']) ?>" target="_blank" class="w-12 h-12 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-300 hover:bg-black hover:border-black hover:text-white transition-all duration-300 hover:-translate-y-1" aria-label="Twitter">
                            <i class="fab fa-twitter text-lg"></i>
                        </a>
                        <?php endif; ?>
                        
                        <?php if(!empty($settings['instagram'])): ?>
                        <a href="<?= htmlspecialchars($settings['instagram']) ?>" target="_blank" class="w-12 h-12 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-300 hover:bg-gradient-to-br hover:from-[#833AB4] hover:via-[#FD1D1D] hover:to-[#F56040] hover:border-transparent hover:text-white transition-all duration-300 hover:-translate-y-1" aria-label="Instagram">
                            <i class="fab fa-instagram text-lg"></i>
                        </a>
                        <?php endif; ?>
                        
                        <?php if(!empty($settings['youtube'])): ?>
                        <a href="<?= htmlspecialchars($settings['youtube']) ?>" target="_blank" class="w-12 h-12 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-300 hover:bg-[#FF0000] hover:border-[#FF0000] hover:text-white transition-all duration-300 hover:-translate-y-1" aria-label="YouTube">
                            <i class="fab fa-youtube text-lg"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Copyright -->
            <div class="pt-8 border-t border-slate-800/80 flex flex-col md:flex-row justify-between items-center text-sm text-slate-500">
                <p>&copy; 2026 KPU Kabupaten Lombok Utara. Hak Cipta Dilindungi Undang-Undang.</p>
                <div class="mt-4 md:mt-0 font-medium">
                    Dikelola oleh <span class="text-slate-400">Sub Bagian Data dan Informasi</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Script for Mobile Menu -->
    <script>
        const btn = document.getElementById('mobile-menu-btn');
        const menu = document.getElementById('mobile-menu');
        const icon = btn.querySelector('i');

        btn.addEventListener('click', () => {
            menu.classList.toggle('hidden');
            if (menu.classList.contains('hidden')) {
                icon.classList.remove('fa-xmark');
                icon.classList.add('fa-bars');
            } else {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-xmark');
            }
        });

        // Close mobile menu when clicking on a link
        const mobileLinks = menu.querySelectorAll('a');
        mobileLinks.forEach(link => {
            link.addEventListener('click', () => {
                menu.classList.add('hidden');
                icon.classList.remove('fa-xmark');
                icon.classList.add('fa-bars');
            });
        });
    </script>

    <!-- PWA Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('sw.js')
                    .then(registration => {
                        console.log('ServiceWorker registration successful with scope: ', registration.scope);
                    })
                    .catch(error => {
                        console.log('ServiceWorker registration failed: ', error);
                    });
            });
        }
    </script>
    
    <!-- FullCalendar -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            if(calendarEl) {
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,listMonth'
                    },
                    themeSystem: 'standard',
                    events: <?= $calendarEventsJson ?>,
                    height: 600,
                    eventTimeFormat: {
                        hour: '2-digit',
                        minute: '2-digit',
                        meridiem: false,
                        hour12: false
                    },
                    eventClick: function(info) {
                        alert('Agenda: ' + info.event.title + '\nLokasi: ' + info.event.extendedProps.location + '\nDeskripsi: ' + info.event.extendedProps.description);
                    }
                });
                calendar.render();
            }
        });
    </script>
    
    <!-- Pop-up Announcement Modal -->
    <?php if(($settings['popup_active'] ?? '0') == '1'): ?>
    <div id="announcement-modal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-300">
        <div class="bg-white rounded-3xl overflow-hidden max-w-lg w-full shadow-2xl border border-slate-100 transform scale-95 transition-transform duration-300 relative">
            <!-- Close Button -->
            <button onclick="closeAnnouncement()" class="absolute top-4 right-4 z-10 w-10 h-10 rounded-full bg-white/80 hover:bg-white text-slate-700 hover:text-slate-900 flex items-center justify-center shadow-md transition-all">
                <i class="fas fa-times text-lg"></i>
            </button>

            <!-- Image/Banner -->
            <?php if(!empty($settings['popup_image'])): ?>
                <img src="<?= htmlspecialchars($settings['popup_image']) ?>" alt="Pengumuman" class="w-full h-48 md:h-64 object-cover">
            <?php else: ?>
                <div class="w-full h-32 bg-gradient-to-r from-orange-500 to-red-500 flex items-center justify-center text-white text-5xl">
                    <i class="fas fa-bullhorn animate-bounce"></i>
                </div>
            <?php endif; ?>

            <!-- Content -->
            <div class="p-6 md:p-8">
                <h3 class="text-xl md:text-2xl font-bold text-slate-800 mb-3"><?= htmlspecialchars($settings['popup_title'] ?? 'Pengumuman Resmi') ?></h3>
                <p class="text-slate-600 text-sm md:text-base leading-relaxed mb-6"><?= nl2br(htmlspecialchars($settings['popup_content'] ?? '')) ?></p>
                <div class="flex space-x-3">
                    <?php if(!empty($settings['popup_link'])): ?>
                        <a href="<?= htmlspecialchars($settings['popup_link']) ?>" onclick="closeAnnouncement()" class="flex-1 bg-orange-600 text-white font-bold py-3 px-5 rounded-xl hover:bg-orange-500 transition-colors text-center text-sm shadow-lg shadow-orange-600/30">
                            Lihat Selengkapnya <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    <?php endif; ?>
                    <button onclick="closeAnnouncement()" class="flex-1 bg-slate-100 text-slate-700 font-bold py-3 px-5 rounded-xl hover:bg-slate-200 transition-colors text-center text-sm border border-slate-200">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const hasShown = sessionStorage.getItem('announcement_shown');
            if (!hasShown) {
                const modal = document.getElementById('announcement-modal');
                const card = modal.querySelector('div');
                setTimeout(() => {
                    modal.classList.remove('opacity-0', 'pointer-events-none');
                    card.classList.remove('scale-95');
                    card.classList.add('scale-100');
                }, 500); // Small delay for smooth load
            }
        });

        function closeAnnouncement() {
            const modal = document.getElementById('announcement-modal');
            const card = modal.querySelector('div');
            modal.classList.add('opacity-0', 'pointer-events-none');
            card.classList.remove('scale-100');
            card.classList.add('scale-95');
            sessionStorage.setItem('announcement_shown', 'true');
        }
    </script>
    <?php endif; ?>
    
    <!-- Floating WhatsApp Button (Bottom Left) -->
    <?php if(!empty($settings['whatsapp_number']) && ($settings['whatsapp_active'] ?? '1') == '1'): ?>
    <a href="https://wa.me/<?= htmlspecialchars(preg_replace('/[^0-9]/', '', $settings['whatsapp_number'])) ?>" target="_blank" class="fixed bottom-6 left-6 md:bottom-10 md:left-10 bg-[#25D366] text-white w-16 h-16 rounded-full flex items-center justify-center text-3xl shadow-[0_10px_25px_rgba(37,211,102,0.4)] hover:scale-110 transition-transform duration-300 z-50 group">
        <i class="fab fa-whatsapp"></i>
        <!-- Tooltip -->
        <span class="absolute left-20 bg-white text-slate-800 text-sm font-bold px-4 py-2 rounded-xl shadow-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none whitespace-nowrap border border-slate-100">
            Live Chat WA
            <div class="absolute top-1/2 -left-2 -mt-2 w-4 h-4 bg-white transform rotate-45 border-b border-l border-slate-100"></div>
        </span>
    </a>
    <?php endif; ?>

    <!-- AI Chatbot Assistant (Bottom Right) -->
    <?php if(($settings['chatbot_active'] ?? '1') == '1'): ?>
    <!-- Chat Widget Window -->
    <div id="chatbot-window" class="fixed bottom-24 right-6 md:bottom-28 md:right-10 w-[350px] md:w-[380px] h-[480px] bg-white/95 backdrop-blur-md rounded-3xl border border-slate-200 shadow-2xl flex flex-col overflow-hidden z-50 opacity-0 scale-95 pointer-events-none transition-all duration-300 transform origin-bottom-right">
        <!-- Chat Header -->
        <div class="bg-gradient-to-r from-orange-500 to-orange-600 p-4 flex items-center justify-between text-white shadow-md">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center text-xl animate-pulse">
                    <i class="fas fa-robot"></i>
                </div>
                <div>
                    <h4 class="font-bold text-sm leading-tight"><?= htmlspecialchars($settings['chatbot_name'] ?? 'Si-KPU') ?></h4>
                    <span class="text-[10px] bg-green-500/80 px-2 py-0.5 rounded-full font-semibold uppercase tracking-wider">Online</span>
                </div>
            </div>
            <button onclick="toggleChatbot()" class="text-white hover:opacity-80 p-1.5 focus:outline-none transition-opacity">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <!-- Chat Messages Container -->
        <div id="chatbot-messages" class="flex-grow p-4 overflow-y-auto space-y-3 custom-scrollbar bg-slate-50/50">
            <!-- Bot message -->
            <div class="flex items-start space-x-2">
                <div class="w-7 h-7 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 text-xs shrink-0 mt-0.5">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="bg-white border border-slate-100 text-slate-700 text-xs p-3 rounded-2xl rounded-tl-none shadow-sm max-w-[80%] leading-relaxed">
                    <?= htmlspecialchars($settings['chatbot_welcome_message'] ?? '') ?>
                </div>
            </div>

            <!-- Predefined Chips/Buttons -->
            <div class="flex flex-wrap gap-2 pl-9">
                <button onclick="sendQuickReply('Layanan Kepegawaian')" class="bg-orange-50 hover:bg-orange-100 text-orange-700 border border-orange-200 text-[10px] font-bold px-3 py-1.5 rounded-full transition-all">
                    💼 Kepegawaian
                </button>
                <button onclick="sendQuickReply('Layanan Publik')" class="bg-orange-50 hover:bg-orange-100 text-orange-700 border border-orange-200 text-[10px] font-bold px-3 py-1.5 rounded-full transition-all">
                    🗳️ Layanan Publik
                </button>
                <button onclick="sendQuickReply('Pengajuan Audiensi')" class="bg-orange-50 hover:bg-orange-100 text-orange-700 border border-orange-200 text-[10px] font-bold px-3 py-1.5 rounded-full transition-all">
                    🤝 Audiensi
                </button>
                <button onclick="sendQuickReply('Aplikasi KPU')" class="bg-orange-50 hover:bg-orange-100 text-orange-700 border border-orange-200 text-[10px] font-bold px-3 py-1.5 rounded-full transition-all">
                    📲 Aplikasi KPU
                </button>
            </div>
        </div>

        <!-- Chat Input Form -->
        <form id="chatbot-form" onsubmit="handleChatSubmit(event)" class="p-3 bg-white border-t border-slate-100 flex items-center gap-2">
            <input type="text" id="chatbot-input" placeholder="Tulis pertanyaan Anda..." autocomplete="off" class="flex-grow bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs outline-none focus:bg-white focus:border-orange-500 transition-all">
            <button type="submit" class="w-9 h-9 rounded-xl bg-orange-600 hover:bg-orange-500 text-white flex items-center justify-center shadow-lg shadow-orange-600/30 shrink-0 transition-all">
                <i class="fas fa-paper-plane text-xs"></i>
            </button>
        </form>
    </div>

    <!-- Toggle Button (Bottom Right) -->
    <button onclick="toggleChatbot()" id="chatbot-toggle-btn" class="fixed bottom-6 right-6 md:bottom-10 md:right-10 bg-orange-600 hover:bg-orange-500 text-white w-16 h-16 rounded-full flex items-center justify-center text-3xl shadow-[0_10px_25px_rgba(234,88,12,0.4)] hover:scale-110 transition-transform duration-300 z-50 group">
        <i class="fas fa-robot"></i>
        <!-- Tooltip -->
        <span class="absolute right-20 bg-white text-slate-800 text-sm font-bold px-4 py-2 rounded-xl shadow-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none whitespace-nowrap border border-slate-100">
            Asisten AI KPU
            <div class="absolute top-1/2 -right-2 -mt-2 w-4 h-4 bg-white transform rotate-45 border-t border-r border-slate-100"></div>
        </span>
    </button>

    <script>
        const faqsData = <?= json_encode($faqs) ?>;
        
        function toggleChatbot() {
            const win = document.getElementById('chatbot-window');
            const btn = document.getElementById('chatbot-toggle-btn');
            const icon = btn.querySelector('i');
            
            if (win.classList.contains('opacity-0')) {
                // Open
                win.classList.remove('opacity-0', 'scale-95', 'pointer-events-none');
                win.classList.add('opacity-100', 'scale-100');
                icon.classList.remove('fa-robot');
                icon.classList.add('fa-comment-slash');
            } else {
                // Close
                win.classList.add('opacity-0', 'scale-95', 'pointer-events-none');
                win.classList.remove('opacity-100', 'scale-100');
                icon.classList.remove('fa-comment-slash');
                icon.classList.add('fa-robot');
            }
        }

        function appendMessage(sender, text, isHtml = false) {
            const container = document.getElementById('chatbot-messages');
            
            // Message wrapper
            const msgWrap = document.createElement('div');
            msgWrap.className = sender === 'bot' ? 'flex items-start space-x-2' : 'flex items-start justify-end space-x-2';
            
            // Icon for bot
            let iconHtml = '';
            if (sender === 'bot') {
                iconHtml = `
                    <div class="w-7 h-7 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 text-xs shrink-0 mt-0.5 animate-pulse">
                        <i class="fas fa-robot"></i>
                    </div>
                `;
            }
            
            const contentClass = sender === 'bot' 
                ? 'bg-white border border-slate-100 text-slate-700 text-xs p-3 rounded-2xl rounded-tl-none shadow-sm max-w-[80%] leading-relaxed'
                : 'bg-orange-600 text-white text-xs p-3 rounded-2xl rounded-tr-none shadow-md max-w-[80%] leading-relaxed';
                
            msgWrap.innerHTML = `
                ${iconHtml}
                <div class="${contentClass}">
                    ${isHtml ? text : escapeHtml(text)}
                </div>
            `;
            
            container.appendChild(msgWrap);
            container.scrollTop = container.scrollHeight;
        }

        function escapeHtml(text) {
            return text
                .toString()
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function showTypingIndicator() {
            const container = document.getElementById('chatbot-messages');
            const indicator = document.createElement('div');
            indicator.id = 'chatbot-typing';
            indicator.className = 'flex items-start space-x-2';
            indicator.innerHTML = `
                <div class="w-7 h-7 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 text-xs shrink-0 mt-0.5">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="bg-white border border-slate-100 text-slate-400 text-xs py-2 px-4 rounded-2xl rounded-tl-none shadow-sm flex space-x-1 items-center h-8">
                    <div class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce"></div>
                    <div class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce [animation-delay:0.2s]"></div>
                    <div class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce [animation-delay:0.4s]"></div>
                </div>
            `;
            container.appendChild(indicator);
            container.scrollTop = container.scrollHeight;
        }

        function removeTypingIndicator() {
            const el = document.getElementById('chatbot-typing');
            if (el) el.remove();
        }

        function handleChatSubmit(e) {
            e.preventDefault();
            const input = document.getElementById('chatbot-input');
            const text = input.value.trim();
            if (!text) return;
            
            input.value = '';
            
            // Add user message
            appendMessage('user', text);
            
            // Process bot response
            showTypingIndicator();
            setTimeout(() => {
                removeTypingIndicator();
                const response = getBotResponse(text);
                appendMessage('bot', response, true);
            }, 800);
        }

        function sendQuickReply(text) {
            appendMessage('user', text);
            showTypingIndicator();
            setTimeout(() => {
                removeTypingIndicator();
                const response = getBotResponse(text);
                appendMessage('bot', response, true);
            }, 600);
        }

        function getBotResponse(query) {
            const cleanQuery = query.toLowerCase().trim();
            
            // 1. Check keyword categories
            if (cleanQuery.includes('kepegawaian') || cleanQuery.includes('pegawai') || cleanQuery.includes('kerja')) {
                return `<strong>💼 Layanan Kepegawaian KPU Lombok Utara:</strong><br><br>Kami mengelola kebutuhan administrasi kepegawaian internal secara optimal. Layanan kami meliputi:<br>• Pengajuan Cuti Pegawai KPU<br>• Kenaikan Pangkat & Jabatan<br>• Mutasi Jabatan Organisasi<br>• Layanan Kesejahteraan Pegawai<br><br>Anda dapat melihat panduan selengkapnya pada bagian Kepegawaian di halaman utama.`;
            }
            
            if (cleanQuery.includes('publik') || cleanQuery.includes('layanan') || cleanQuery.includes('informasi')) {
                return `<strong>🗳️ Layanan Publik KPU KLU:</strong><br><br>KPU Lombok Utara bertekad memberikan pelayanan transparan bagi masyarakat:<br>• <strong>PPID:</strong> Layanan permohonan informasi publik resmi.<br>• <strong>Lindungi Hakmu:</strong> Cek status hak pilih Anda.<br>• <strong>JDIH KPU:</strong> Portal regulasi dan keputusan hukum pemilu.<br><br>Untuk mengajukan pengaduan resmi, gunakan halaman <a href="kontak.php" class="text-orange-600 font-bold hover:underline">Hubungi Kami</a>.`;
            }
            
            if (cleanQuery.includes('audiensi') || cleanQuery.includes('kunjung') || cleanQuery.includes('rapat')) {
                return `<strong>🤝 Pengajuan Audiensi / Kunjungan:</strong><br><br>Lembaga, sekolah, organisasi, maupun partai politik yang ingin mengadakan audiensi atau kunjungan kerja ke kantor KPU Lombok Utara dapat mengirimkan permohonan tertulis resmi.<br><br>Permohonan dapat diajukan secara langsung ke Kantor KPU Lombok Utara atau hubungi kami melalui formulir di halaman <a href="kontak.php" class="text-orange-600 font-bold hover:underline">Hubungi Kami (Kontak)</a>.`;
            }

            if (cleanQuery.includes('aplikasi') || cleanQuery.includes('sistem') || cleanQuery.includes('web')) {
                return `<strong>📲 Sistem & Aplikasi KPU:</strong><br><br>KPU menggunakan berbagai aplikasi digital untuk transparansi pemilu:<br>• <strong>SIAKBA:</strong> Pendaftaran anggota PPK, PPS, dan KPPS.<br>• <strong>SILON:</strong> Sistem Informasi Pencalonan.<br>• <strong>SIDALIH:</strong> Sistem Informasi Data Pemilih.<br>• <strong>SIREKAP:</strong> Rekapitulasi suara hasil pemilu.<br><br>Tautan akses aplikasi pemilu bisa Anda temukan lengkap pada bagian Layanan Publik.`;
            }
            
            // 2. Intelligent search match from dynamically fetched FAQs
            let bestMatch = null;
            let highestScore = 0;
            
            faqsData.forEach(faq => {
                const words = faq.question.toLowerCase().split(/\s+/);
                let score = 0;
                
                words.forEach(word => {
                    if (word.length > 3 && cleanQuery.includes(word)) {
                        score += 1;
                    }
                });
                
                if (score > highestScore) {
                    highestScore = score;
                    bestMatch = faq;
                }
            });
            
            // If we have a good match (at least 2 matching key words or 1 long unique word)
            if (bestMatch && highestScore >= 1) {
                return `<strong>Pertanyaan: ${bestMatch.question}</strong><br><br>${bestMatch.answer}`;
            }
            
            // Default fall back
            return `Maaf, saya tidak menemukan jawaban yang tepat terkait <em>"${escapeHtml(query)}"</em>.<br><br>Anda bisa mencoba topik umum berikut:<br>
                <div class="mt-2 flex flex-col space-y-1">
                    <button onclick="sendQuickReply('Layanan Kepegawaian')" class="text-left text-orange-600 hover:underline font-semibold">• Layanan Kepegawaian</button>
                    <button onclick="sendQuickReply('Layanan Publik')" class="text-left text-orange-600 hover:underline font-semibold">• Layanan Publik</button>
                    <button onclick="sendQuickReply('Pengajuan Audiensi')" class="text-left text-orange-600 hover:underline font-semibold">• Pengajuan Audiensi</button>
                    <button onclick="sendQuickReply('Aplikasi KPU')" class="text-left text-orange-600 hover:underline font-semibold">• Akses Aplikasi Pemilu</button>
                </div><br>
                Atau Anda juga bisa langsung chat dengan admin layanan informasi kami via <a href="https://wa.me/<?= htmlspecialchars(preg_replace('/[^0-9]/', '', $settings['whatsapp_number'] ?? '')) ?>" target="_blank" class="text-green-600 font-bold hover:underline"><i class="fab fa-whatsapp"></i> WhatsApp</a>.`;
        }
    </script>
    <?php endif; ?>

    <!-- Background Music Player -->
    <?php if(($settings['music_active'] ?? '0') == '1'): ?>
    <?php
        $musicSrc = ($settings['music_source_type'] ?? 'url') == 'upload' ? htmlspecialchars($settings['music_file'] ?? '') : htmlspecialchars($settings['music_url'] ?? '');
    ?>
    <?php if(!empty($musicSrc)): ?>
    <audio id="bg-music" src="<?= $musicSrc ?>" loop></audio>

    <!-- Floating Mute/Unmute Button (Above WhatsApp Button in Bottom Left) -->
    <button onclick="toggleMusic()" id="music-toggle-btn" class="fixed bottom-24 left-6 md:bottom-28 md:left-10 bg-slate-900/90 text-orange-500 border border-slate-700/50 backdrop-blur-md w-12 h-12 rounded-full flex items-center justify-center text-xl shadow-lg hover:scale-110 transition-transform duration-300 z-50 group">
        <i id="music-icon" class="fas fa-volume-xmark"></i>
        <!-- Tooltip -->
        <span class="absolute left-16 bg-white text-slate-800 text-xs font-bold px-3 py-1.5 rounded-lg shadow-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none whitespace-nowrap border border-slate-100">
            Musik: Mati
            <div class="absolute top-1/2 -left-1.5 -mt-1.5 w-3 h-3 bg-white transform rotate-45 border-b border-l border-slate-100"></div>
        </span>
    </button>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const audio = document.getElementById('bg-music');
            const btn = document.getElementById('music-toggle-btn');
            const icon = document.getElementById('music-icon');
            
            const autoplayEnabled = <?= ($settings['music_autoplay'] ?? '1') == '1' ? 'true' : 'false' ?>;
            let hasInteracted = false;

            // Set volume standard
            audio.volume = 0.3;

            if (autoplayEnabled) {
                // Try to play on first document click/scroll/keypress (browser bypass)
                const playOnFirstInteraction = () => {
                    if (!hasInteracted) {
                        audio.play().then(() => {
                            hasInteracted = true;
                            updateMusicUI(true);
                            cleanupListeners();
                        }).catch(err => {
                            console.log("Autoplay blocked, waiting for direct user click.");
                        });
                    }
                };

                const cleanupListeners = () => {
                    document.removeEventListener('click', playOnFirstInteraction);
                    document.removeEventListener('touchstart', playOnFirstInteraction);
                    document.removeEventListener('scroll', playOnFirstInteraction);
                };

                document.addEventListener('click', playOnFirstInteraction);
                document.addEventListener('touchstart', playOnFirstInteraction);
                document.addEventListener('scroll', playOnFirstInteraction);
            }
        });

        function updateMusicUI(isPlaying) {
            const audio = document.getElementById('bg-music');
            const btn = document.getElementById('music-toggle-btn');
            const icon = document.getElementById('music-icon');
            const tooltip = btn.querySelector('span');

            if (isPlaying) {
                icon.className = 'fas fa-volume-high animate-[spin_6s_linear_infinite]';
                tooltip.innerHTML = 'Musik: Nyala <div class="absolute top-1/2 -left-1.5 -mt-1.5 w-3 h-3 bg-white transform rotate-45 border-b border-l border-slate-100"></div>';
                btn.classList.add('text-green-500');
                btn.classList.remove('text-orange-500');
            } else {
                icon.className = 'fas fa-volume-xmark';
                tooltip.innerHTML = 'Musik: Mati <div class="absolute top-1/2 -left-1.5 -mt-1.5 w-3 h-3 bg-white transform rotate-45 border-b border-l border-slate-100"></div>';
                btn.classList.remove('text-green-500');
                btn.classList.add('text-orange-500');
            }
        }

        function toggleMusic() {
            const audio = document.getElementById('bg-music');
            
            if (audio.paused) {
                audio.play().then(() => {
                    updateMusicUI(true);
                });
            } else {
                audio.pause();
                updateMusicUI(false);
            }
        }
    </script>
    <?php endif; ?>
    <?php endif; ?>
</body>
</html>
