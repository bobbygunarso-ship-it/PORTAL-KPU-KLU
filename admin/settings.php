<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle image upload if exists
    if (isset($_FILES['popup_image_file']) && $_FILES['popup_image_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['popup_image_file']['tmp_name'];
        $fileName = $_FILES['popup_image_file']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($fileExtension, $allowedExts)) {
            $newFileName = 'popup_' . time() . '.' . $fileExtension;
            $destPath = '../assets/uploads/' . $newFileName;
            
            // Delete old file if exists
            $stmtGet = $pdo->prepare("SELECT key_value FROM settings WHERE key_name = 'popup_image'");
            $stmtGet->execute();
            $oldImage = $stmtGet->fetchColumn();
            if ($oldImage && file_exists('../' . $oldImage)) {
                unlink('../' . $oldImage);
            }

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $stmtImg = $pdo->prepare("UPDATE settings SET key_value = ? WHERE key_name = 'popup_image'");
                $stmtImg->execute(['assets/uploads/' . $newFileName]);
            }
        }
    }

    $stmt = $pdo->prepare("UPDATE settings SET key_value = ? WHERE key_name = ?");
    
    // Looping semua field POST untuk diupdate ke DB
    foreach ($_POST as $key => $value) {
        if ($key != 'submit') {
            $stmt->execute([$value, $key]);
        }
    }
    header("Location: settings.php?msg=updated");
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

<div class="max-w-4xl mx-auto">
    <h2 class="text-2xl font-bold mb-6 text-slate-800 border-b pb-4 flex items-center">
        <i class="fas fa-cog text-orange-500 mr-3"></i> Pengaturan Website
    </h2>

    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'updated'): ?>
        <div class="bg-green-50 text-green-700 p-4 rounded-xl mb-6 text-sm font-medium border border-green-200 flex items-center">
            <i class="fas fa-check-circle mr-2 text-green-500 text-lg"></i> Pengaturan berhasil diperbarui.
        </div>
    <?php endif; ?>

    <form action="settings.php" method="POST" enctype="multipart/form-data" class="bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-slate-200 space-y-8">
        
        <!-- Informasi Umum -->
        <div>
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                <i class="fas fa-info-circle text-slate-400 mr-2"></i> Informasi Umum
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Website (Title)</label>
                    <input type="text" name="site_title" value="<?= htmlspecialchars($settings['site_title'] ?? '') ?>" class="w-full border border-slate-300 bg-slate-50 rounded-xl p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Deskripsi Website (Footer)</label>
                    <textarea name="site_desc" rows="3" class="w-full border border-slate-300 bg-slate-50 rounded-xl p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all"><?= htmlspecialchars($settings['site_desc'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <hr class="border-slate-100">

        <!-- Kontak & Alamat -->
        <div>
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                <i class="fas fa-address-book text-slate-400 mr-2"></i> Kontak & Alamat
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Alamat Kantor</label>
                    <textarea name="address" rows="2" class="w-full border border-slate-300 bg-slate-50 rounded-xl p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all"><?= htmlspecialchars($settings['address'] ?? '') ?></textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Nomor Telepon</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($settings['phone'] ?? '') ?>" class="w-full border border-slate-300 bg-slate-50 rounded-xl p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Email Publik</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($settings['email'] ?? '') ?>" class="w-full border border-slate-300 bg-slate-50 rounded-xl p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all">
                </div>
            </div>
        </div>

        <hr class="border-slate-100">

        <!-- Sosial Media -->
        <div>
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                <i class="fas fa-hashtag text-slate-400 mr-2"></i> Link Sosial Media
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Facebook URL</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-blue-600"><i class="fab fa-facebook"></i></div>
                        <input type="text" name="facebook" value="<?= htmlspecialchars($settings['facebook'] ?? '') ?>" class="w-full border border-slate-300 bg-slate-50 rounded-xl pl-10 p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Instagram URL</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-pink-600"><i class="fab fa-instagram"></i></div>
                        <input type="text" name="instagram" value="<?= htmlspecialchars($settings['instagram'] ?? '') ?>" class="w-full border border-slate-300 bg-slate-50 rounded-xl pl-10 p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Twitter / X URL</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-800"><i class="fab fa-twitter"></i></div>
                        <input type="text" name="twitter" value="<?= htmlspecialchars($settings['twitter'] ?? '') ?>" class="w-full border border-slate-300 bg-slate-50 rounded-xl pl-10 p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">YouTube URL</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-red-600"><i class="fab fa-youtube"></i></div>
                        <input type="text" name="youtube" value="<?= htmlspecialchars($settings['youtube'] ?? '') ?>" class="w-full border border-slate-300 bg-slate-50 rounded-xl pl-10 p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">WhatsApp Number (Awali dengan 62)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-green-600"><i class="fab fa-whatsapp"></i></div>
                        <input type="text" name="whatsapp_number" value="<?= htmlspecialchars($settings['whatsapp_number'] ?? '') ?>" class="w-full border border-slate-300 bg-slate-50 rounded-xl pl-10 p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all" placeholder="6281234567890">
                    </div>
                    <p class="text-xs text-slate-500 mt-2">Nomor ini digunakan untuk tombol Live Chat WA.</p>
                </div>
            </div>
        </div>

        <hr class="border-slate-100">

        <!-- Pop-up Pengumuman -->
        <div>
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                <i class="fas fa-bullhorn text-orange-500 mr-2"></i> Pengumuman Pop-up (Urgent Announcement)
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Status Pop-up</label>
                    <select name="popup_active" class="w-full border border-slate-300 bg-slate-50 rounded-xl p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all">
                        <option value="1" <?= ($settings['popup_active'] ?? '0') == '1' ? 'selected' : '' ?>>AKTIF (Tampilkan)</option>
                        <option value="0" <?= ($settings['popup_active'] ?? '0') == '0' ? 'selected' : '' ?>>NONAKTIF (Sembunyikan)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Judul Pengumuman</label>
                    <input type="text" name="popup_title" value="<?= htmlspecialchars($settings['popup_title'] ?? '') ?>" class="w-full border border-slate-300 bg-slate-50 rounded-xl p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Isi Pengumuman</label>
                    <textarea name="popup_content" rows="3" class="w-full border border-slate-300 bg-slate-50 rounded-xl p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all"><?= htmlspecialchars($settings['popup_content'] ?? '') ?></textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Link Tujuan (Klik Detail)</label>
                    <input type="text" name="popup_link" value="<?= htmlspecialchars($settings['popup_link'] ?? '') ?>" class="w-full border border-slate-300 bg-slate-50 rounded-xl p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all" placeholder="Contoh: index.php#agenda">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Gambar Banner Pop-up</label>
                    <input type="file" name="popup_image_file" accept="image/*" class="w-full border border-slate-300 bg-slate-50 rounded-xl p-2 focus:bg-white focus:border-orange-500 outline-none transition-all text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100">
                    <?php if(!empty($settings['popup_image'])): ?>
                        <div class="mt-2 flex items-center space-x-2">
                            <img src="../<?= htmlspecialchars($settings['popup_image']) ?>" class="h-12 w-auto rounded border border-slate-200">
                            <span class="text-xs text-slate-500">Gambar saat ini</span>
                        </div>
                    <?php endif; ?>
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
