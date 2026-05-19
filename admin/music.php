<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$upload_dir = '../assets/uploads/music/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// 1. Handle General Settings Update
if (isset($_POST['update_general'])) {
    $music_active = $_POST['music_active'] ?? '0';
    $music_autoplay = $_POST['music_autoplay'] ?? '1';
    $music_source_type = $_POST['music_source_type'] ?? 'url';
    $music_url = $_POST['music_url'] ?? '';

    // If source type is upload, fetch active song path from db and update settings
    $music_file = '';
    if ($music_source_type == 'upload') {
        $stmtActiveSong = $pdo->query("SELECT file_path FROM songs WHERE is_active = 1 LIMIT 1");
        $music_file = $stmtActiveSong->fetchColumn() ?: '';
    }

    $updates = [
        'music_active' => $music_active,
        'music_autoplay' => $music_autoplay,
        'music_source_type' => $music_source_type,
        'music_url' => $music_url,
        'music_file' => $music_file
    ];

    $stmtUpdate = $pdo->prepare("UPDATE settings SET key_value = ? WHERE key_name = ?");
    foreach ($updates as $key => $val) {
        $stmtUpdate->execute([$val, $key]);
    }

    header("Location: music.php?msg=general_updated");
    exit;
}

// 2. Handle Upload New Song
if (isset($_POST['upload_song'])) {
    $title = trim($_POST['title'] ?? '');
    
    if (empty($title)) {
        header("Location: music.php?error=empty_title");
        exit;
    }

    if (isset($_FILES['music_file_upload']) && $_FILES['music_file_upload']['error'] == UPLOAD_ERR_OK) {
        $file_name = $_FILES['music_file_upload']['name'];
        $file_tmp = $_FILES['music_file_upload']['tmp_name'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (in_array($ext, ['mp3', 'ogg', 'wav'])) {
            $new_filename = 'bgm_' . time() . '.' . $ext;
            $file_path = 'assets/uploads/music/' . $new_filename;

            if (move_uploaded_file($file_tmp, $upload_dir . $new_filename)) {
                // Insert into songs library table
                $stmtInsert = $pdo->prepare("INSERT INTO songs (title, file_path, is_active) VALUES (?, ?, 0)");
                $stmtInsert->execute([$title, $file_path]);

                header("Location: music.php?msg=song_uploaded");
                exit;
            }
        } else {
            header("Location: music.php?error=invalid_format");
            exit;
        }
    } else {
        header("Location: music.php?error=upload_failed");
        exit;
    }
}

// 3. Handle Select / Activate Song
if (isset($_GET['activate'])) {
    $id = $_GET['activate'];
    
    // Fetch target song
    $stmtSong = $pdo->prepare("SELECT * FROM songs WHERE id = ?");
    $stmtSong->execute([$id]);
    $song = $stmtSong->fetch();

    if ($song) {
        // Deactivate all songs
        $pdo->exec("UPDATE songs SET is_active = 0");
        
        // Activate selected song
        $stmtAct = $pdo->prepare("UPDATE songs SET is_active = 1 WHERE id = ?");
        $stmtAct->execute([$id]);

        // Update settings table
        $stmtSetFile = $pdo->prepare("UPDATE settings SET key_value = ? WHERE key_name = 'music_file'");
        $stmtSetFile->execute([$song->file_path]);
        
        $stmtSetSource = $pdo->prepare("UPDATE settings SET key_value = 'upload' WHERE key_name = 'music_source_type'");
        $stmtSetSource->execute();

        header("Location: music.php?msg=song_activated");
        exit;
    }
}

// 4. Handle Delete Song
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // Fetch target song
    $stmtSong = $pdo->prepare("SELECT * FROM songs WHERE id = ?");
    $stmtSong->execute([$id]);
    $song = $stmtSong->fetch();

    if ($song) {
        // Delete physical file
        if (file_exists('../' . $song->file_path)) {
            unlink('../' . $song->file_path);
        }

        // Delete from database
        $stmtDel = $pdo->prepare("DELETE FROM songs WHERE id = ?");
        $stmtDel->execute([$id]);

        // If it was the active song, reset settings
        if ($song->is_active) {
            $stmtSetFile = $pdo->prepare("UPDATE settings SET key_value = '' WHERE key_name = 'music_file'");
            $stmtSetFile->execute();
        }

        header("Location: music.php?msg=song_deleted");
        exit;
    }
}

// Fetch all settings
$stmt = $pdo->query("SELECT * FROM settings");
$settingsData = $stmt->fetchAll();
$settings = [];
foreach ($settingsData as $row) {
    $settings[$row->key_name] = $row->key_value;
}

// Fetch all songs library
$stmtSongs = $pdo->query("SELECT * FROM songs ORDER BY id DESC");
$songs = $stmtSongs->fetchAll();

require 'header.php';
?>

<div class="w-full grid grid-cols-1 lg:grid-cols-3 gap-8 pb-12">
    
    <!-- Bagian 1: Pengaturan Umum Musik -->
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <h3 class="text-xl font-bold mb-6 flex items-center border-b pb-4">
                <i class="fas fa-sliders-h text-orange-500 mr-3"></i> Konfigurasi Musik
            </h3>

            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'general_updated'): ?>
                <div class="bg-green-50 text-green-700 p-3 rounded-xl mb-4 text-xs font-medium border border-green-200">
                    Pengaturan berhasil disimpan!
                </div>
            <?php endif; ?>

            <form action="music.php" method="POST" class="space-y-4">
                <input type="hidden" name="update_general" value="1">
                
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Status Musik Latar</label>
                    <select name="music_active" class="w-full border border-slate-300 bg-slate-50 rounded-xl p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none text-sm transition-all">
                        <option value="1" <?= ($settings['music_active'] ?? '0') == '1' ? 'selected' : '' ?>>AKTIF (Mainkan)</option>
                        <option value="0" <?= ($settings['music_active'] ?? '0') == '0' ? 'selected' : '' ?>>NONAKTIF (Matikan)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Autoplay Musik</label>
                    <select name="music_autoplay" class="w-full border border-slate-300 bg-slate-50 rounded-xl p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none text-sm transition-all">
                        <option value="1" <?= ($settings['music_autoplay'] ?? '1') == '1' ? 'selected' : '' ?>>Ya (Otomatis)</option>
                        <option value="0" <?= ($settings['music_autoplay'] ?? '1') == '0' ? 'selected' : '' ?>>Tidak (Manual)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Sumber Lagu</label>
                    <select name="music_source_type" id="music_source_type" onchange="toggleSourceInputs()" class="w-full border border-slate-300 bg-slate-50 rounded-xl p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none text-sm transition-all">
                        <option value="url" <?= ($settings['music_source_type'] ?? 'url') == 'url' ? 'selected' : '' ?>>Tautan URL Eksternal</option>
                        <option value="upload" <?= ($settings['music_source_type'] ?? 'url') == 'upload' ? 'selected' : '' ?>>Musik Perpustakaan (Upload)</option>
                    </select>
                </div>

                <div id="url_source_group">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Tautan URL Audio (.mp3)</label>
                    <input type="text" name="music_url" value="<?= htmlspecialchars($settings['music_url'] ?? '') ?>" class="w-full border border-slate-300 bg-slate-50 rounded-xl p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none text-sm transition-all" placeholder="https://domain.com/song.mp3">
                </div>

                <button type="submit" class="w-full bg-orange-600 hover:bg-orange-500 text-white font-bold py-3 rounded-xl transition-all shadow-md">
                    <i class="fas fa-save mr-2"></i> Simpan Konfigurasi
                </button>
            </form>
        </div>

        <!-- Unggah Lagu Baru -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <h3 class="text-xl font-bold mb-6 flex items-center border-b pb-4">
                <i class="fas fa-cloud-upload-alt text-blue-500 mr-3"></i> Unggah Lagu Baru
            </h3>

            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'song_uploaded'): ?>
                <div class="bg-green-50 text-green-700 p-3 rounded-xl mb-4 text-xs font-medium border border-green-200">
                    Lagu berhasil diunggah ke perpustakaan!
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <?php 
                $err = '';
                if($_GET['error'] == 'empty_title') $err = 'Judul lagu tidak boleh kosong!';
                if($_GET['error'] == 'invalid_format') $err = 'Format tidak didukung (Gunakan MP3/WAV/OGG).';
                if($_GET['error'] == 'upload_failed') $err = 'Gagal mengunggah file lagu.';
                ?>
                <div class="bg-red-50 text-red-700 p-3 rounded-xl mb-4 text-xs font-medium border border-red-200">
                    <?= $err ?>
                </div>
            <?php endif; ?>

            <form action="music.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="upload_song" value="1">
                
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Nama/Judul Lagu</label>
                    <input type="text" name="title" required class="w-full border border-slate-300 bg-slate-50 rounded-xl p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none text-sm transition-all" placeholder="Contoh: Mars KPU Lombok Utara">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Pilih File Lagu (.mp3)</label>
                    <input type="file" name="music_file_upload" accept="audio/*" required class="w-full border border-slate-300 bg-slate-50 rounded-xl p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none text-sm transition-all">
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 rounded-xl transition-all shadow-md">
                    <i class="fas fa-plus mr-2"></i> Unggah Lagu
                </button>
            </form>
        </div>
    </div>

    <!-- Bagian 2: Daftar Perpustakaan Lagu -->
    <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <h3 class="text-xl font-bold mb-6 flex items-center border-b pb-4">
            <i class="fas fa-music text-slate-500 mr-3"></i> Perpustakaan Lagu (Uploads)
        </h3>

        <?php if (isset($_GET['msg'])): ?>
            <?php 
            $info = '';
            if($_GET['msg'] == 'song_activated') $info = 'Lagu aktif berhasil diubah dan dimainkan!';
            if($_GET['msg'] == 'song_deleted') $info = 'Lagu berhasil dihapus dari perpustakaan.';
            ?>
            <?php if($info): ?>
                <div class="bg-green-50 text-green-700 p-4 rounded-xl mb-6 text-sm font-medium border border-green-200 flex items-center">
                    <i class="fas fa-check-circle mr-2 text-green-500 text-lg"></i> <?= $info ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 text-xs font-bold uppercase tracking-wider">
                        <th class="p-4 border-b">Detail Lagu</th>
                        <th class="p-4 border-b w-32 text-center">Status</th>
                        <th class="p-4 border-b w-40 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach($songs as $s): ?>
                    <tr class="hover:bg-orange-50/50 transition-colors">
                        <td class="p-4 align-middle">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center text-orange-600 shrink-0">
                                    <i class="fas fa-music"></i>
                                </div>
                                <div class="overflow-hidden">
                                    <div class="font-bold text-slate-800 text-sm truncate"><?= htmlspecialchars($s->title) ?></div>
                                    <div class="text-xs text-slate-500 truncate"><?= basename($s->file_path) ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="p-4 align-middle text-center">
                            <?php if($s->is_active && ($settings['music_source_type'] ?? 'url') == 'upload'): ?>
                                <span class="px-3 py-1 text-xs font-bold rounded-full bg-green-100 text-green-700 border border-green-200">
                                    MEMUTAR
                                </span>
                            <?php else: ?>
                                <span class="px-3 py-1 text-xs font-bold rounded-full bg-slate-100 text-slate-500 border border-slate-200">
                                    SIAP
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="p-4 text-center align-middle whitespace-nowrap">
                            <div class="flex items-center justify-center space-x-2">
                                <a href="music.php?activate=<?= $s->id ?>" class="bg-slate-100 hover:bg-orange-600 hover:text-white text-slate-700 text-xs font-bold px-3 py-2 rounded-xl transition-all shadow-sm flex items-center" title="Pilih Lagu Ini">
                                    <i class="fas fa-play mr-1.5 text-[10px]"></i> Pilih
                                </a>
                                <a href="music.php?delete=<?= $s->id ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus lagu ini dari perpustakaan?')" class="text-red-600 hover:bg-red-50 w-9 h-9 rounded-xl flex items-center justify-center transition-colors border border-slate-100 hover:border-red-200" title="Hapus Lagu">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>

                    <?php if(count($songs) == 0): ?>
                    <tr>
                        <td colspan="3" class="p-8 text-center text-slate-400 bg-slate-50">
                            Belum ada lagu yang diunggah ke perpustakaan.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function toggleSourceInputs() {
        const sourceType = document.getElementById('music_source_type').value;
        const urlGroup = document.getElementById('url_source_group');

        if (sourceType === 'url') {
            urlGroup.classList.remove('hidden');
        } else {
            urlGroup.classList.add('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', toggleSourceInputs);
</script>

<?php require 'footer.php'; ?>
