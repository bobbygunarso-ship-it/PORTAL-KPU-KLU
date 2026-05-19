<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: index.php?msg=deleted");
    exit;
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $icon = $_POST['icon'];
    $url = $_POST['url'];
    
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Update
        $stmt = $pdo->prepare("UPDATE services SET type=?, title=?, description=?, icon=?, url=? WHERE id=?");
        $stmt->execute([$type, $title, $description, $icon, $url, $_POST['id']]);
        header("Location: index.php?msg=updated");
        exit;
    } else {
        // Insert
        $stmt = $pdo->prepare("INSERT INTO services (type, title, description, icon, url) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$type, $title, $description, $icon, $url]);
        header("Location: index.php?msg=added");
        exit;
    }
}

// Fetch all services
$stmt = $pdo->query("SELECT * FROM services ORDER BY type ASC, id ASC");
$services = $stmt->fetchAll();

// Fetch Statistics
$today = date('Y-m-d');
$month = date('Y-m');

$stmt = $pdo->prepare("SELECT COUNT(*) FROM visitor_logs WHERE visit_date = ?");
$stmt->execute([$today]);
$visitorsToday = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM visitor_logs WHERE visit_date LIKE ?");
$stmt->execute([$month . '%']);
$visitorsMonth = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM visitor_logs");
$visitorsTotal = $stmt->fetchColumn();

// Chart Data (Last 7 days)
$chartLabels = [];
$chartData = [];
for($i=6; $i>=0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $chartLabels[] = date('d M', strtotime($d));
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM visitor_logs WHERE visit_date = ?");
    $stmt->execute([$d]);
    $chartData[] = $stmt->fetchColumn();
}


// Edit Mode Check
$editService = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editService = $stmt->fetch();
}
?>
<?php require 'header.php'; ?>
    
    <!-- Statistics Dashboard -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex items-center">
            <div class="w-14 h-14 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center text-2xl mr-4 shrink-0">
                <i class="fas fa-user-clock"></i>
            </div>
            <div>
                <div class="text-sm text-slate-500 font-semibold mb-1">Pengunjung Hari Ini</div>
                <div class="text-2xl font-bold text-slate-800"><?= $visitorsToday ?></div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex items-center">
            <div class="w-14 h-14 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-2xl mr-4 shrink-0">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div>
                <div class="text-sm text-slate-500 font-semibold mb-1">Pengunjung Bulan Ini</div>
                <div class="text-2xl font-bold text-slate-800"><?= $visitorsMonth ?></div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex items-center">
            <div class="w-14 h-14 rounded-xl bg-green-100 text-green-600 flex items-center justify-center text-2xl mr-4 shrink-0">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <div class="text-sm text-slate-500 font-semibold mb-1">Total Pengunjung</div>
                <div class="text-2xl font-bold text-slate-800"><?= $visitorsTotal ?></div>
            </div>
        </div>
    </div>

    <!-- Grafik -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 mb-8">
        <h3 class="text-lg font-bold mb-4 text-slate-800 border-b pb-3"><i class="fas fa-chart-line text-orange-500 mr-2"></i> Grafik Kunjungan (7 Hari Terakhir)</h3>
        <div class="h-64 w-full">
            <canvas id="visitorChart"></canvas>
        </div>
    </div>

    <div class="w-full grid grid-cols-1 lg:grid-cols-3 gap-8 pb-12">
        <!-- Form Add/Edit -->
        <div class="lg:col-span-1 bg-white p-6 rounded-2xl shadow-sm border border-slate-200 h-fit">
            <h3 class="text-xl font-bold mb-6 flex items-center border-b pb-4">
                <i class="fas <?= $editService ? 'fa-edit text-blue-500' : 'fa-plus-circle text-orange-500' ?> mr-3"></i>
                <?= $editService ? 'Edit Layanan' : 'Tambah Layanan Baru' ?>
            </h3>
            
            <?php if (isset($_GET['msg'])): ?>
                <div class="bg-green-50 text-green-700 p-4 rounded-xl mb-6 text-sm font-medium border border-green-200 flex items-center">
                    <i class="fas fa-check-circle mr-2 text-green-500 text-lg"></i> Data layanan berhasil diperbarui.
                </div>
            <?php endif; ?>

            <form action="index.php" method="POST" class="space-y-5">
                <?php if ($editService): ?>
                    <input type="hidden" name="id" value="<?= $editService->id ?>">
                <?php endif; ?>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Kategori Layanan</label>
                    <select name="type" required class="w-full border border-slate-300 bg-slate-50 rounded-xl p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all">
                        <option value="kepegawaian" <?= ($editService && $editService->type == 'kepegawaian') ? 'selected' : '' ?>>Layanan Kepegawaian (Internal)</option>
                        <option value="publik" <?= ($editService && $editService->type == 'publik') ? 'selected' : '' ?>>Layanan Publik (Masyarakat)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Judul Layanan</label>
                    <input type="text" name="title" value="<?= $editService ? htmlspecialchars($editService->title) : '' ?>" required class="w-full border border-slate-300 bg-slate-50 rounded-xl p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all" placeholder="Misal: JDIH">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Deskripsi Singkat</label>
                    <textarea name="description" rows="3" class="w-full border border-slate-300 bg-slate-50 rounded-xl p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all" placeholder="Tuliskan deskripsi layanan..."><?= $editService ? htmlspecialchars($editService->description) : '' ?></textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Class Ikon FontAwesome</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i class="fas fa-icons"></i>
                        </div>
                        <input type="text" name="icon" placeholder="fas fa-globe" value="<?= $editService ? htmlspecialchars($editService->icon) : '' ?>" required class="w-full border border-slate-300 bg-slate-50 rounded-xl pl-10 p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all">
                    </div>
                    <p class="text-xs text-slate-500 mt-2">Cari ikon gratis di <a href="https://fontawesome.com/search?o=r&m=free" target="_blank" class="text-orange-600 hover:underline font-semibold">fontawesome.com</a></p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">URL / Link Tujuan</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i class="fas fa-link"></i>
                        </div>
                        <input type="text" name="url" placeholder="https://..." value="<?= $editService ? htmlspecialchars($editService->url) : '#' ?>" required class="w-full border border-slate-300 bg-slate-50 rounded-xl pl-10 p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all">
                    </div>
                </div>

                <div class="flex space-x-3 pt-4">
                    <button type="submit" class="bg-orange-600 text-white px-5 py-3 rounded-xl hover:bg-orange-500 transition-all font-bold w-full shadow-md hover:shadow-lg hover:-translate-y-0.5 flex items-center justify-center">
                        <i class="fas <?= $editService ? 'fa-save' : 'fa-plus' ?> mr-2"></i> <?= $editService ? 'Update Data' : 'Simpan Data' ?>
                    </button>
                    <?php if ($editService): ?>
                        <a href="index.php" class="bg-slate-200 text-slate-700 px-5 py-3 rounded-xl hover:bg-slate-300 transition-all font-bold text-center flex items-center justify-center">Batal</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Tabel Layanan -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <h3 class="text-xl font-bold mb-6 flex items-center border-b pb-4">
                <i class="fas fa-list text-slate-500 mr-3"></i> Daftar Layanan Terdaftar
            </h3>
            
            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-600 text-xs font-bold uppercase tracking-wider">
                            <th class="p-4 border-b">Ikon</th>
                            <th class="p-4 border-b">Detail Layanan</th>
                            <th class="p-4 border-b">Kategori</th>
                            <th class="p-4 border-b text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach($services as $s): ?>
                        <tr class="hover:bg-orange-50/50 transition-colors group">
                            <td class="p-4 text-center align-top w-16">
                                <div class="w-10 h-10 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center text-lg mx-auto shadow-sm">
                                    <i class="<?= htmlspecialchars($s->icon) ?>"></i>
                                </div>
                            </td>
                            <td class="p-4 align-top">
                                <div class="font-bold text-slate-800 text-base mb-1 group-hover:text-orange-600 transition-colors"><?= htmlspecialchars($s->title) ?></div>
                                <div class="text-sm text-slate-500 max-w-sm line-clamp-2"><?= htmlspecialchars($s->description) ?></div>
                                <a href="<?= htmlspecialchars($s->url) ?>" target="_blank" class="text-xs text-blue-500 mt-2 inline-block hover:underline truncate w-48"><i class="fas fa-external-link-alt text-[10px] mr-1"></i> <?= htmlspecialchars($s->url) ?></a>
                            </td>
                            <td class="p-4 align-top">
                                <?php if($s->type == 'kepegawaian'): ?>
                                    <span class="bg-blue-100 text-blue-700 text-xs px-2.5 py-1 rounded-full font-bold border border-blue-200">Kepegawaian</span>
                                <?php else: ?>
                                    <span class="bg-emerald-100 text-emerald-700 text-xs px-2.5 py-1 rounded-full font-bold border border-emerald-200">Publik</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-center align-top whitespace-nowrap">
                                <div class="flex items-center justify-center space-x-2 opacity-100 sm:opacity-70 group-hover:opacity-100 transition-opacity">
                                    <a href="index.php?edit=<?= $s->id ?>" class="text-blue-600 hover:text-white hover:bg-blue-600 w-8 h-8 rounded flex items-center justify-center transition-colors shadow-sm bg-blue-50" title="Edit Data">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="index.php?delete=<?= $s->id ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus layanan \'<?= addslashes($s->title) ?>\'?')" class="text-red-600 hover:text-white hover:bg-red-600 w-8 h-8 rounded flex items-center justify-center transition-colors shadow-sm bg-red-50" title="Hapus Data">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(count($services) == 0): ?>
                        <tr>
                            <td colspan="4" class="p-8 text-center text-slate-400 bg-slate-50">
                                <i class="fas fa-folder-open text-4xl mb-3 block opacity-30"></i>
                                Belum ada data layanan. Silakan tambah data baru.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        if(document.getElementById('visitorChart')) {
            const ctx = document.getElementById('visitorChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?= json_encode($chartLabels) ?>,
                    datasets: [{
                        label: 'Pengunjung',
                        data: <?= json_encode($chartData) ?>,
                        borderColor: '#ea580c',
                        backgroundColor: 'rgba(234, 88, 12, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } }
                    }
                }
            });
        }
    </script>
<?php require 'footer.php'; ?>
