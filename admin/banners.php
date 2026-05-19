<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Upload directory
$uploadDir = '../assets/uploads/banners/';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Get image url first
    $stmt = $pdo->prepare("SELECT image_url, media_type FROM banners WHERE id = ?");
    $stmt->execute([$id]);
    $banner = $stmt->fetch();
    
    // Delete file if it's a local upload
    if ($banner && in_array($banner->media_type, ['image', 'video']) && strpos($banner->image_url, 'assets/uploads') !== false) {
        $filePath = '../' . explode('assets/uploads', $banner->image_url)[1];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
    
    $stmt = $pdo->prepare("DELETE FROM banners WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: banners.php?msg=deleted");
    exit;
}

// Handle Status Toggle
if (isset($_GET['toggle'])) {
    $id = $_GET['toggle'];
    $stmt = $pdo->prepare("UPDATE banners SET is_active = NOT is_active WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: banners.php?msg=updated");
    exit;
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $subtitle = $_POST['subtitle'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $media_type = $_POST['media_type'] ?? 'image';
    
    $image_url = '';
    
    if ($media_type === 'youtube') {
        // Use the youtube link directly
        $image_url = $_POST['youtube_link'];
    } else {
        // Handle File Upload (Image or Video)
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = $media_type === 'video' ? ['mp4', 'webm'] : ['jpg', 'jpeg', 'png', 'webp'];
            $filename = $_FILES['image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $newName = uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $newName)) {
                    $image_url = 'assets/uploads/banners/' . $newName;
                }
            }
        }
    }
    
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Update
        $id = $_POST['id'];
        if ($image_url != '') {
            $stmt = $pdo->prepare("UPDATE banners SET title=?, subtitle=?, image_url=?, media_type=?, is_active=? WHERE id=?");
            $stmt->execute([$title, $subtitle, $image_url, $media_type, $is_active, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE banners SET title=?, subtitle=?, media_type=?, is_active=? WHERE id=?");
            $stmt->execute([$title, $subtitle, $media_type, $is_active, $id]);
        }
        header("Location: banners.php?msg=updated");
        exit;
    } else {
        // Insert
        // If no image uploaded but required, fallback to default or error
        if ($image_url == '' && $media_type === 'image') {
            $image_url = 'https://images.unsplash.com/photo-1579294314136-124449830573?auto=format&fit=crop&q=80&w=2000'; // fallback
        }
        $stmt = $pdo->prepare("INSERT INTO banners (title, subtitle, image_url, media_type, is_active) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $subtitle, $image_url, $media_type, $is_active]);
        header("Location: banners.php?msg=added");
        exit;
    }
}

// Fetch all banners
$stmt = $pdo->query("SELECT * FROM banners ORDER BY id DESC");
$banners = $stmt->fetchAll();

// Edit Mode Check
$editBanner = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM banners WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editBanner = $stmt->fetch();
}

require 'header.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <!-- Form Add/Edit -->
    <div class="lg:col-span-1 bg-white p-6 rounded-2xl shadow-sm border border-slate-200 h-fit">
        <h3 class="text-xl font-bold mb-6 flex items-center border-b pb-4">
            <i class="fas <?= $editBanner ? 'fa-edit text-blue-500' : 'fa-image text-orange-500' ?> mr-3"></i>
            <?= $editBanner ? 'Edit Banner' : 'Tambah Banner' ?>
        </h3>
        
        <?php if (isset($_GET['msg'])): ?>
            <div class="bg-green-50 text-green-700 p-4 rounded-xl mb-6 text-sm font-medium border border-green-200 flex items-center">
                <i class="fas fa-check-circle mr-2 text-green-500 text-lg"></i> Data banner berhasil diperbarui.
            </div>
        <?php endif; ?>

        <form action="banners.php" method="POST" enctype="multipart/form-data" class="space-y-5">
            <?php if ($editBanner): ?>
                <input type="hidden" name="id" value="<?= $editBanner->id ?>">
            <?php endif; ?>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Jenis Media</label>
                <div class="flex space-x-4">
                    <label class="flex items-center text-sm">
                        <input type="radio" name="media_type" value="image" class="text-orange-600 focus:ring-orange-500 h-4 w-4" onchange="toggleMediaType()" <?= (!$editBanner || $editBanner->media_type == 'image') ? 'checked' : '' ?>>
                        <span class="ml-2 text-slate-700"><i class="fas fa-image text-slate-400 mr-1"></i> Gambar</span>
                    </label>
                    <label class="flex items-center text-sm">
                        <input type="radio" name="media_type" value="video" class="text-orange-600 focus:ring-orange-500 h-4 w-4" onchange="toggleMediaType()" <?= ($editBanner && $editBanner->media_type == 'video') ? 'checked' : '' ?>>
                        <span class="ml-2 text-slate-700"><i class="fas fa-video text-slate-400 mr-1"></i> Video Lokal</span>
                    </label>
                    <label class="flex items-center text-sm">
                        <input type="radio" name="media_type" value="youtube" class="text-orange-600 focus:ring-orange-500 h-4 w-4" onchange="toggleMediaType()" <?= ($editBanner && $editBanner->media_type == 'youtube') ? 'checked' : '' ?>>
                        <span class="ml-2 text-slate-700"><i class="fab fa-youtube text-red-500 mr-1"></i> YouTube</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Judul Utama</label>
                <input type="text" name="title" value="<?= $editBanner ? htmlspecialchars($editBanner->title) : '' ?>" required class="w-full border border-slate-300 bg-slate-50 rounded-xl p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all" placeholder="Teks besar di tengah...">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Subjudul / Deskripsi</label>
                <textarea name="subtitle" rows="3" class="w-full border border-slate-300 bg-slate-50 rounded-xl p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all" placeholder="Teks kecil di bawah judul..."><?= $editBanner ? htmlspecialchars($editBanner->subtitle) : '' ?></textarea>
            </div>

            <!-- Upload File Container -->
            <div id="fileUploadContainer" class="<?= ($editBanner && $editBanner->media_type == 'youtube') ? 'hidden' : 'block' ?>">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Upload File</label>
                <?php if($editBanner && in_array($editBanner->media_type, ['image', 'video']) && $editBanner->image_url): ?>
                    <div class="mb-3">
                        <?php if($editBanner->media_type == 'image'): ?>
                            <img src="<?= strpos($editBanner->image_url, 'http') === 0 ? $editBanner->image_url : '../' . $editBanner->image_url ?>" class="w-full h-32 object-cover rounded-lg border border-slate-200">
                        <?php else: ?>
                            <video src="<?= '../' . $editBanner->image_url ?>" class="w-full h-32 object-cover rounded-lg border border-slate-200" controls></video>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <input type="file" name="image" id="fileInput" class="w-full border border-slate-300 bg-slate-50 rounded-xl p-2 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all text-sm" <?= ($editBanner || ($editBanner && $editBanner->media_type == 'youtube')) ? '' : 'required' ?>>
                <p id="fileHelpText" class="text-xs text-slate-500 mt-2">Format: JPG, PNG, WEBP. Ukuran ideal: 1920x1080px.</p>
            </div>
            
            <!-- YouTube URL Container -->
            <div id="youtubeContainer" class="<?= ($editBanner && $editBanner->media_type == 'youtube') ? 'block' : 'hidden' ?>">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Link YouTube</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fab fa-youtube"></i>
                    </div>
                    <input type="url" name="youtube_link" id="youtubeInput" value="<?= ($editBanner && $editBanner->media_type == 'youtube') ? htmlspecialchars($editBanner->image_url) : '' ?>" class="w-full border border-slate-300 bg-slate-50 rounded-xl pl-10 p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all" placeholder="https://www.youtube.com/watch?v=...">
                </div>
                <p class="text-xs text-slate-500 mt-2">Masukkan URL lengkap video YouTube.</p>
            </div>
            
            <div class="flex items-center">
                <input type="checkbox" name="is_active" id="is_active" value="1" <?= ($editBanner && !$editBanner->is_active) ? '' : 'checked' ?> class="w-4 h-4 text-orange-600 border-slate-300 rounded focus:ring-orange-500">
                <label for="is_active" class="ml-2 block text-sm text-slate-700 font-semibold">Tampilkan Banner Ini</label>
            </div>

            <div class="flex space-x-3 pt-4">
                <button type="submit" class="bg-orange-600 text-white px-5 py-3 rounded-xl hover:bg-orange-500 transition-all font-bold w-full shadow-md hover:shadow-lg hover:-translate-y-0.5 flex items-center justify-center">
                    <i class="fas <?= $editBanner ? 'fa-save' : 'fa-plus' ?> mr-2"></i> <?= $editBanner ? 'Update Banner' : 'Simpan Banner' ?>
                </button>
                <?php if ($editBanner): ?>
                    <a href="banners.php" class="bg-slate-200 text-slate-700 px-5 py-3 rounded-xl hover:bg-slate-300 transition-all font-bold text-center flex items-center justify-center">Batal</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Tabel Banner -->
    <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <h3 class="text-xl font-bold mb-6 flex items-center border-b pb-4">
            <i class="fas fa-list text-slate-500 mr-3"></i> Daftar Banner
        </h3>
        
        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 text-xs font-bold uppercase tracking-wider">
                        <th class="p-4 border-b w-32">Media</th>
                        <th class="p-4 border-b">Detail</th>
                        <th class="p-4 border-b text-center">Status</th>
                        <th class="p-4 border-b text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach($banners as $b): ?>
                    <tr class="hover:bg-orange-50/50 transition-colors group">
                        <td class="p-4 align-top">
                            <div class="w-24 h-16 rounded-lg overflow-hidden bg-slate-100 shadow-sm border border-slate-200 flex items-center justify-center relative">
                                <?php if($b->media_type == 'youtube'): ?>
                                    <div class="absolute inset-0 bg-slate-800 flex items-center justify-center">
                                        <i class="fab fa-youtube text-red-500 text-3xl"></i>
                                    </div>
                                <?php elseif($b->media_type == 'video'): ?>
                                    <div class="absolute inset-0 bg-slate-800 flex items-center justify-center text-white">
                                        <i class="fas fa-play-circle text-2xl"></i>
                                    </div>
                                    <video src="<?= '../' . htmlspecialchars($b->image_url) ?>" class="w-full h-full object-cover opacity-50"></video>
                                <?php else: ?>
                                    <img src="<?= strpos($b->image_url, 'http') === 0 ? htmlspecialchars($b->image_url) : '../' . htmlspecialchars($b->image_url) ?>" class="w-full h-full object-cover">
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="p-4 align-top">
                            <div class="font-bold text-slate-800 text-base mb-1">
                                <?= htmlspecialchars($b->title) ?>
                            </div>
                            <div class="flex items-center space-x-2 mb-1">
                                <?php if($b->media_type == 'youtube'): ?>
                                    <span class="text-[10px] bg-red-100 text-red-700 px-2 py-0.5 rounded font-bold uppercase">YouTube</span>
                                <?php elseif($b->media_type == 'video'): ?>
                                    <span class="text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded font-bold uppercase">Video</span>
                                <?php else: ?>
                                    <span class="text-[10px] bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded font-bold uppercase">Gambar</span>
                                <?php endif; ?>
                            </div>
                            <div class="text-sm text-slate-500 max-w-sm line-clamp-2"><?= htmlspecialchars($b->subtitle) ?></div>
                        </td>
                        <td class="p-4 align-top text-center">
                            <a href="banners.php?toggle=<?= $b->id ?>" class="inline-block" title="Klik untuk mengubah status">
                                <?php if($b->is_active): ?>
                                    <span class="bg-green-100 text-green-700 text-xs px-2.5 py-1 rounded-full font-bold border border-green-200"><i class="fas fa-check mr-1"></i> Aktif</span>
                                <?php else: ?>
                                    <span class="bg-slate-100 text-slate-600 text-xs px-2.5 py-1 rounded-full font-bold border border-slate-200"><i class="fas fa-eye-slash mr-1"></i> Nonaktif</span>
                                <?php endif; ?>
                            </a>
                        </td>
                        <td class="p-4 text-center align-top whitespace-nowrap">
                            <div class="flex items-center justify-center space-x-2">
                                <a href="banners.php?edit=<?= $b->id ?>" class="text-blue-600 hover:text-white hover:bg-blue-600 w-8 h-8 rounded flex items-center justify-center transition-colors shadow-sm bg-blue-50" title="Edit Data">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="banners.php?delete=<?= $b->id ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus banner ini?')" class="text-red-600 hover:text-white hover:bg-red-600 w-8 h-8 rounded flex items-center justify-center transition-colors shadow-sm bg-red-50" title="Hapus Data">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if(count($banners) == 0): ?>
                    <tr>
                        <td colspan="4" class="p-8 text-center text-slate-400 bg-slate-50">
                            <i class="fas fa-images text-4xl mb-3 block opacity-30"></i>
                            Belum ada banner.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function toggleMediaType() {
    const mediaType = document.querySelector('input[name="media_type"]:checked').value;
    const fileContainer = document.getElementById('fileUploadContainer');
    const ytContainer = document.getElementById('youtubeContainer');
    const fileInput = document.getElementById('fileInput');
    const ytInput = document.getElementById('youtubeInput');
    const helpText = document.getElementById('fileHelpText');
    
    // Reset required states
    fileInput.required = false;
    ytInput.required = false;

    if (mediaType === 'youtube') {
        fileContainer.classList.add('hidden');
        ytContainer.classList.remove('hidden');
        <?php if(!$editBanner): ?> ytInput.required = true; <?php endif; ?>
    } else {
        fileContainer.classList.remove('hidden');
        ytContainer.classList.add('hidden');
        <?php if(!$editBanner): ?> fileInput.required = true; <?php endif; ?>
        
        if (mediaType === 'video') {
            fileInput.accept = 'video/mp4,video/webm';
            helpText.innerText = 'Format: MP4, WEBM. Pastikan ukuran file tidak melebihi limit server.';
        } else {
            fileInput.accept = 'image/*';
            helpText.innerText = 'Format: JPG, PNG, WEBP. Ukuran ideal: 1920x1080px.';
        }
    }
}
// Init state on load
document.addEventListener('DOMContentLoaded', toggleMediaType);
</script>

<?php require 'footer.php'; ?>
