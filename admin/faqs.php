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
    $stmt = $pdo->prepare("DELETE FROM faqs WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: faqs.php?msg=deleted");
    exit;
}

// Handle Toggle Active
if (isset($_GET['toggle'])) {
    $id = $_GET['toggle'];
    $stmt = $pdo->prepare("UPDATE faqs SET is_active = NOT is_active WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: faqs.php?msg=toggled");
    exit;
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = $_POST['question'];
    $answer = $_POST['answer'];
    $order_num = $_POST['order_num'];
    
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Update
        $stmt = $pdo->prepare("UPDATE faqs SET question=?, answer=?, order_num=? WHERE id=?");
        $stmt->execute([$question, $answer, $order_num, $_POST['id']]);
        header("Location: faqs.php?msg=updated");
        exit;
    } else {
        // Insert
        $stmt = $pdo->prepare("INSERT INTO faqs (question, answer, order_num) VALUES (?, ?, ?)");
        $stmt->execute([$question, $answer, $order_num]);
        header("Location: faqs.php?msg=added");
        exit;
    }
}

// Fetch all faqs
$stmt = $pdo->query("SELECT * FROM faqs ORDER BY order_num ASC, id DESC");
$faqs = $stmt->fetchAll();

// Edit Mode Check
$editFaq = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM faqs WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editFaq = $stmt->fetch();
}

require 'header.php';
?>

<div class="w-full grid grid-cols-1 lg:grid-cols-3 gap-8 pb-12">
    
    <!-- Form Add/Edit -->
    <div class="lg:col-span-1 bg-white p-6 rounded-2xl shadow-sm border border-slate-200 h-fit">
        <h3 class="text-xl font-bold mb-6 flex items-center border-b pb-4">
            <i class="fas <?= $editFaq ? 'fa-edit text-blue-500' : 'fa-plus-circle text-orange-500' ?> mr-3"></i>
            <?= $editFaq ? 'Edit FAQ' : 'Tambah FAQ' ?>
        </h3>
        
        <?php if (isset($_GET['msg'])): ?>
            <?php 
            $msgText = '';
            if($_GET['msg'] == 'added') $msgText = 'FAQ berhasil ditambahkan.';
            if($_GET['msg'] == 'updated') $msgText = 'FAQ berhasil diperbarui.';
            if($_GET['msg'] == 'deleted') $msgText = 'FAQ berhasil dihapus.';
            if($_GET['msg'] == 'toggled') $msgText = 'Status aktif FAQ berhasil diubah.';
            ?>
            <div class="bg-green-50 text-green-700 p-4 rounded-xl mb-6 text-sm font-medium border border-green-200 flex items-center">
                <i class="fas fa-check-circle mr-2 text-green-500 text-lg"></i> <?= $msgText ?>
            </div>
        <?php endif; ?>

        <form action="faqs.php" method="POST" class="space-y-4">
            <?php if($editFaq): ?>
                <input type="hidden" name="id" value="<?= $editFaq->id ?>">
            <?php endif; ?>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Pertanyaan</label>
                <input type="text" name="question" value="<?= $editFaq ? htmlspecialchars($editFaq->question) : '' ?>" required class="w-full border border-slate-300 bg-slate-50 rounded-xl p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all" placeholder="Masukkan pertanyaan publik...">
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Jawaban</label>
                <textarea name="answer" rows="5" required class="w-full border border-slate-300 bg-slate-50 rounded-xl p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all resize-none" placeholder="Tuliskan jawaban lengkap di sini..."><?= $editFaq ? htmlspecialchars($editFaq->answer) : '' ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Urutan Tampil (Angka)</label>
                <input type="number" name="order_num" value="<?= $editFaq ? htmlspecialchars($editFaq->order_num) : '0' ?>" required class="w-full border border-slate-300 bg-slate-50 rounded-xl p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all">
            </div>

            <div class="pt-4 flex gap-3">
                <button type="submit" class="flex-1 <?= $editFaq ? 'bg-blue-600 hover:bg-blue-500 shadow-blue-600/30' : 'bg-orange-600 hover:bg-orange-500 shadow-orange-600/30' ?> text-white font-bold py-3 rounded-xl transition-all shadow-lg">
                    <?= $editFaq ? '<i class="fas fa-save mr-2"></i> Simpan' : '<i class="fas fa-plus mr-2"></i> Tambah' ?>
                </button>
                <?php if($editFaq): ?>
                <a href="faqs.php" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold py-3 px-5 rounded-xl transition-all text-center">
                    Batal
                </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Tabel FAQ -->
    <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <h3 class="text-xl font-bold mb-6 flex items-center border-b pb-4">
            <i class="fas fa-question-circle text-slate-500 mr-3"></i> Daftar Tanya Jawab (FAQ)
        </h3>
        
        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 text-xs font-bold uppercase tracking-wider">
                        <th class="p-4 border-b w-16 text-center">Urutan</th>
                        <th class="p-4 border-b">Tanya & Jawab</th>
                        <th class="p-4 border-b w-32 text-center">Status</th>
                        <th class="p-4 border-b w-24 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach($faqs as $f): ?>
                    <tr class="hover:bg-orange-50/50 transition-colors">
                        <td class="p-4 align-middle text-center font-bold text-slate-500">
                            <?= $f->order_num ?>
                        </td>
                        <td class="p-4 align-top">
                            <div class="font-bold text-slate-800 text-base mb-1">
                                <?= htmlspecialchars($f->question) ?>
                            </div>
                            <div class="text-xs text-slate-500 line-clamp-2">
                                <?= htmlspecialchars($f->answer) ?>
                            </div>
                        </td>
                        <td class="p-4 align-middle text-center">
                            <a href="faqs.php?toggle=<?= $f->id ?>" class="inline-block px-3 py-1 text-xs font-bold rounded-full <?= $f->is_active ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-100 text-red-700 border border-red-200' ?> hover:opacity-80 transition-opacity">
                                <?= $f->is_active ? 'AKTIF' : 'NONAKTIF' ?>
                            </a>
                        </td>
                        <td class="p-4 text-center align-middle whitespace-nowrap">
                            <div class="flex items-center justify-center space-x-2">
                                <a href="faqs.php?edit=<?= $f->id ?>" class="text-blue-600 hover:bg-blue-50 w-8 h-8 rounded flex items-center justify-center transition-colors" title="Edit Data">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="faqs.php?delete=<?= $f->id ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus FAQ ini?')" class="text-red-600 hover:bg-red-50 w-8 h-8 rounded flex items-center justify-center transition-colors" title="Hapus Data">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if(count($faqs) == 0): ?>
                    <tr>
                        <td colspan="4" class="p-8 text-center text-slate-400 bg-slate-50">
                            Belum ada FAQ yang ditambahkan.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require 'footer.php'; ?>
