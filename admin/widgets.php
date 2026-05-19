<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE settings SET key_value = ? WHERE key_name = ?");
    
    // Looping semua field POST untuk diupdate ke DB
    foreach ($_POST as $key => $value) {
        if ($key != 'submit') {
            $stmt->execute([$value, $key]);
        }
    }
    header("Location: widgets.php?msg=updated");
    exit;
}

// Ambil data settings saat ini
$stmt = $pdo->query("SELECT * FROM settings");
$settingsData = $stmt->fetchAll();
$settings = [];
foreach ($settingsData as $row) {
    $settings[$row->key_name] = $row->key_value;
}

require 'header.php';
?>

<div class="max-w-4xl mx-auto pb-12">
    <h2 class="text-2xl font-bold mb-6 text-slate-800 border-b pb-4 flex items-center">
        <i class="fas fa-cubes text-orange-500 mr-3"></i> Manajemen Widget & Asisten AI
    </h2>

    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'updated'): ?>
        <div class="bg-green-50 text-green-700 p-4 rounded-xl mb-6 text-sm font-medium border border-green-200 flex items-center">
            <i class="fas fa-check-circle mr-2 text-green-500 text-lg"></i> Pengaturan widget berhasil diperbarui.
        </div>
    <?php endif; ?>

    <form action="widgets.php" method="POST" class="bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-slate-200 space-y-8">
        
        <!-- Widget WhatsApp Melayang -->
        <div>
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                <i class="fab fa-whatsapp text-green-500 mr-2 text-xl"></i> Widget WhatsApp Melayang (Kiri Bawah)
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Status Tombol WhatsApp</label>
                    <select name="whatsapp_active" class="w-full border border-slate-300 bg-slate-50 rounded-xl p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all">
                        <option value="1" <?= ($settings['whatsapp_active'] ?? '1') == '1' ? 'selected' : '' ?>>AKTIF (Tampilkan)</option>
                        <option value="0" <?= ($settings['whatsapp_active'] ?? '1') == '0' ? 'selected' : '' ?>>NONAKTIF (Sembunyikan)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Nomor WhatsApp Tujuan</label>
                    <input type="text" name="whatsapp_number" value="<?= htmlspecialchars($settings['whatsapp_number'] ?? '') ?>" class="w-full border border-slate-300 bg-slate-50 rounded-xl p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all" placeholder="6281234567890">
                    <p class="text-xs text-slate-500 mt-2">Awali dengan 62 (Kode Negara Indonesia).</p>
                </div>
            </div>
        </div>

        <hr class="border-slate-100">

        <!-- Chatbot Asisten AI -->
        <div>
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                <i class="fas fa-robot text-blue-500 mr-2 text-xl"></i> Chatbot Asisten AI (Kanan Bawah)
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Status Chatbot AI</label>
                    <select name="chatbot_active" class="w-full border border-slate-300 bg-slate-50 rounded-xl p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all">
                        <option value="1" <?= ($settings['chatbot_active'] ?? '1') == '1' ? 'selected' : '' ?>>AKTIF (Tampilkan)</option>
                        <option value="0" <?= ($settings['chatbot_active'] ?? '1') == '0' ? 'selected' : '' ?>>NONAKTIF (Sembunyikan)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Asisten AI</label>
                    <input type="text" name="chatbot_name" value="<?= htmlspecialchars($settings['chatbot_name'] ?? 'Si-KPU (KPU Assistant)') ?>" class="w-full border border-slate-300 bg-slate-50 rounded-xl p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Pesan Sambutan Pertama (Welcome Message)</label>
                    <textarea name="chatbot_welcome_message" rows="3" class="w-full border border-slate-300 bg-slate-50 rounded-xl p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all"><?= htmlspecialchars($settings['chatbot_welcome_message'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <div class="pt-4 flex justify-end">
            <button type="submit" name="submit" class="bg-orange-600 text-white px-8 py-3 rounded-xl hover:bg-orange-500 transition-all font-bold shadow-md hover:shadow-lg flex items-center">
                <i class="fas fa-save mr-2"></i> Simpan Pengaturan
            </button>
        </div>

    </form>
</div>

<?php require 'footer.php'; ?>
