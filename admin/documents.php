<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$uploadDir = '../assets/uploads/documents/';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("SELECT file_url FROM documents WHERE id = ?");
    $stmt->execute([$id]);
    $doc = $stmt->fetch();
    
    if ($doc) {
        $filePath = '../' . $doc->file_url;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        $pdo->prepare("DELETE FROM documents WHERE id = ?")->execute([$id]);
    }
    header("Location: documents.php?msg=deleted");
    exit;
}

// Handle Add Document
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $title = $_POST['title'];
    $category = $_POST['category'];
    $error = '';

    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['file']['tmp_name'];
        $fileName = $_FILES['file']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $allowedExts = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];
        if (in_array($fileExtension, $allowedExts)) {
            $newFileName = time() . '_' . preg_replace("/[^a-zA-Z0-9.-]/", "_", $fileName);
            $destPath = $uploadDir . $newFileName;
            
            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $file_url = 'assets/uploads/documents/' . $newFileName;
                $stmt = $pdo->prepare("INSERT INTO documents (title, category, file_url) VALUES (?, ?, ?)");
                $stmt->execute([$title, $category, $file_url]);
                header("Location: documents.php?msg=added");
                exit;
            } else {
                $error = "Terjadi kesalahan saat mengunggah file.";
            }
        } else {
            $error = "Tipe file tidak diizinkan. Hanya PDF, Word, dan Excel.";
        }
    } else {
        $error = "Pilih file yang akan diunggah.";
    }
}

// Fetch all documents
$stmt = $pdo->query("SELECT * FROM documents ORDER BY id DESC");
$documents = $stmt->fetchAll();

require 'header.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <!-- Form Tambah Dokumen -->
    <div class="lg:col-span-1 bg-white p-6 rounded-2xl shadow-sm border border-slate-200 h-fit">
        <h3 class="text-xl font-bold mb-6 flex items-center border-b pb-4">
            <i class="fas fa-file-upload text-orange-500 mr-3"></i> Tambah Dokumen
        </h3>
        
        <?php if (isset($_GET['msg'])): ?>
            <div class="bg-green-50 text-green-700 p-4 rounded-xl mb-6 text-sm font-medium border border-green-200 flex items-center">
                <i class="fas fa-check-circle mr-2 text-green-500 text-lg"></i> Dokumen berhasil <?= $_GET['msg'] == 'added' ? 'diunggah' : 'dihapus' ?>.
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="bg-red-50 text-red-700 p-4 rounded-xl mb-6 text-sm font-medium border border-red-200">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form action="documents.php" method="POST" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Judul / Nama Dokumen</label>
                <input type="text" name="title" required class="w-full border border-slate-300 bg-slate-50 rounded-xl p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all" placeholder="Contoh: PKPU No 1 Tahun 2026">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Kategori</label>
                <select name="category" required class="w-full border border-slate-300 bg-slate-50 rounded-xl p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all">
                    <option value="Regulasi">Regulasi (PKPU/UU)</option>
                    <option value="Formulir">Formulir & Berkas</option>
                    <option value="Pengumuman">Pengumuman Terbuka</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Pilih File (PDF, Word, Excel)</label>
                <input type="file" name="file" accept=".pdf,.doc,.docx,.xls,.xlsx" required class="w-full border border-slate-300 bg-slate-50 rounded-xl p-2 focus:bg-white focus:border-orange-500 outline-none transition-all text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100">
            </div>

            <div class="pt-4">
                <button type="submit" name="submit" class="bg-orange-600 text-white px-5 py-3 rounded-xl hover:bg-orange-500 transition-all font-bold w-full shadow-md flex items-center justify-center">
                    <i class="fas fa-upload mr-2"></i> Unggah Dokumen
                </button>
            </div>
        </form>
    </div>

    <!-- Tabel Dokumen -->
    <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <h3 class="text-xl font-bold mb-6 flex items-center border-b pb-4">
            <i class="fas fa-folder-open text-slate-500 mr-3"></i> Pusat Unduhan
        </h3>
        
        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 text-xs font-bold uppercase tracking-wider">
                        <th class="p-4 border-b">Detail Dokumen</th>
                        <th class="p-4 border-b w-32 text-center">Tipe File</th>
                        <th class="p-4 border-b w-24 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach($documents as $d): ?>
                    <?php
                        $ext = strtolower(pathinfo($d->file_url, PATHINFO_EXTENSION));
                        $icon = 'fa-file';
                        $color = 'text-slate-400';
                        if($ext == 'pdf') { $icon = 'fa-file-pdf'; $color = 'text-red-500'; }
                        elseif(in_array($ext, ['doc','docx'])) { $icon = 'fa-file-word'; $color = 'text-blue-500'; }
                        elseif(in_array($ext, ['xls','xlsx'])) { $icon = 'fa-file-excel'; $color = 'text-green-500'; }
                    ?>
                    <tr class="hover:bg-orange-50/50 transition-colors">
                        <td class="p-4 align-top">
                            <div class="font-bold text-slate-800 text-base mb-1">
                                <?= htmlspecialchars($d->title) ?>
                            </div>
                            <div class="text-xs text-slate-500 flex items-center">
                                <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded text-[10px] font-bold uppercase border border-slate-200 mr-2"><?= htmlspecialchars($d->category) ?></span>
                                <i class="fas fa-calendar-alt mr-1"></i> <?= date('d M Y', strtotime($d->created_at)) ?>
                            </div>
                        </td>
                        <td class="p-4 align-middle text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas <?= $icon ?> <?= $color ?> text-2xl mb-1"></i>
                                <span class="text-[10px] font-bold text-slate-500 uppercase"><?= $ext ?></span>
                            </div>
                        </td>
                        <td class="p-4 text-center align-middle whitespace-nowrap">
                            <div class="flex items-center justify-center space-x-2">
                                <a href="../<?= htmlspecialchars($d->file_url) ?>" target="_blank" class="text-blue-600 hover:bg-blue-50 w-8 h-8 rounded flex items-center justify-center transition-colors" title="Download">
                                    <i class="fas fa-download"></i>
                                </a>
                                <a href="documents.php?delete=<?= $d->id ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus dokumen ini?')" class="text-red-600 hover:bg-red-50 w-8 h-8 rounded flex items-center justify-center transition-colors" title="Hapus Data">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if(count($documents) == 0): ?>
                    <tr>
                        <td colspan="3" class="p-8 text-center text-slate-400 bg-slate-50">
                            <i class="fas fa-file-excel text-4xl mb-3 block opacity-30"></i>
                            Belum ada dokumen yang diunggah.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require 'footer.php'; ?>
