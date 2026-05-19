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
    $stmt = $pdo->prepare("DELETE FROM menus WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: menus.php?msg=deleted");
    exit;
}

// Handle Toggle Active
if (isset($_GET['toggle'])) {
    $id = $_GET['toggle'];
    $stmt = $pdo->prepare("UPDATE menus SET is_active = NOT is_active WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: menus.php?msg=toggled");
    exit;
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $url = $_POST['url'];
    $order_num = $_POST['order_num'];
    
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Update
        $stmt = $pdo->prepare("UPDATE menus SET title=?, url=?, order_num=? WHERE id=?");
        $stmt->execute([$title, $url, $order_num, $_POST['id']]);
        header("Location: menus.php?msg=updated");
        exit;
    } else {
        // Insert
        $stmt = $pdo->prepare("INSERT INTO menus (title, url, order_num) VALUES (?, ?, ?)");
        $stmt->execute([$title, $url, $order_num]);
        header("Location: menus.php?msg=added");
        exit;
    }
}

// Fetch all menus
$stmt = $pdo->query("SELECT * FROM menus ORDER BY order_num ASC");
$menus = $stmt->fetchAll();

// Edit Mode Check
$editMenu = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM menus WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editMenu = $stmt->fetch();
}
?>
<?php require 'header.php'; ?>
    <div class="w-full grid grid-cols-1 lg:grid-cols-3 gap-8 pb-12">
        
        <!-- Form Add/Edit -->
        <div class="lg:col-span-1 bg-white p-6 rounded-2xl shadow-sm border border-slate-200 h-fit">
            <h3 class="text-xl font-bold mb-6 flex items-center border-b pb-4">
                <i class="fas <?= $editMenu ? 'fa-edit text-blue-500' : 'fa-plus-circle text-orange-500' ?> mr-3"></i>
                <?= $editMenu ? 'Edit Menu' : 'Tambah Menu' ?>
            </h3>
            
            <?php if (isset($_GET['msg'])): ?>
                <?php 
                $msgText = '';
                if($_GET['msg'] == 'added') $msgText = 'Menu berhasil ditambahkan.';
                if($_GET['msg'] == 'updated') $msgText = 'Menu berhasil diperbarui.';
                if($_GET['msg'] == 'deleted') $msgText = 'Menu berhasil dihapus.';
                if($_GET['msg'] == 'toggled') $msgText = 'Status menu berhasil diubah.';
                ?>
                <div class="bg-green-50 text-green-700 p-4 rounded-xl mb-6 text-sm font-medium border border-green-200 flex items-center">
                    <i class="fas fa-check-circle mr-2 text-green-500 text-lg"></i> <?= $msgText ?>
                </div>
            <?php endif; ?>

            <form action="menus.php" method="POST" class="space-y-4">
                <?php if($editMenu): ?>
                    <input type="hidden" name="id" value="<?= $editMenu->id ?>">
                <?php endif; ?>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Menu</label>
                    <input type="text" name="title" value="<?= $editMenu ? htmlspecialchars($editMenu->title) : '' ?>" required class="w-full border border-slate-300 bg-slate-50 rounded-xl p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all" placeholder="Contoh: Beranda">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">URL / Link Target</label>
                    <input type="text" name="url" value="<?= $editMenu ? htmlspecialchars($editMenu->url) : '' ?>" required class="w-full border border-slate-300 bg-slate-50 rounded-xl p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all" placeholder="Contoh: index.php#beranda">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Urutan Tampil (Angka)</label>
                    <input type="number" name="order_num" value="<?= $editMenu ? htmlspecialchars($editMenu->order_num) : '0' ?>" required class="w-full border border-slate-300 bg-slate-50 rounded-xl p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all">
                </div>

                <div class="pt-4 flex gap-3">
                    <button type="submit" class="flex-1 <?= $editMenu ? 'bg-blue-600 hover:bg-blue-500 shadow-blue-600/30' : 'bg-orange-600 hover:bg-orange-500 shadow-orange-600/30' ?> text-white font-bold py-3 rounded-xl transition-all shadow-lg">
                        <?= $editMenu ? '<i class="fas fa-save mr-2"></i> Simpan' : '<i class="fas fa-plus mr-2"></i> Tambah' ?>
                    </button>
                    <?php if($editMenu): ?>
                    <a href="menus.php" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold py-3 px-5 rounded-xl transition-all text-center">
                        Batal
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Tabel Menu -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <h3 class="text-xl font-bold mb-6 flex items-center border-b pb-4">
                <i class="fas fa-bars text-slate-500 mr-3"></i> Daftar Menu Header
            </h3>
            
            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-600 text-xs font-bold uppercase tracking-wider">
                            <th class="p-4 border-b w-16 text-center">Urutan</th>
                            <th class="p-4 border-b">Detail Menu</th>
                            <th class="p-4 border-b w-32 text-center">Status</th>
                            <th class="p-4 border-b w-24 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach($menus as $m): ?>
                        <tr class="hover:bg-orange-50/50 transition-colors">
                            <td class="p-4 align-middle text-center font-bold text-slate-500">
                                <?= $m->order_num ?>
                            </td>
                            <td class="p-4 align-middle">
                                <div class="font-bold text-slate-800 text-base mb-1">
                                    <?= htmlspecialchars($m->title) ?>
                                </div>
                                <div class="text-xs text-slate-500">
                                    <i class="fas fa-link mr-1 opacity-50"></i> <?= htmlspecialchars($m->url) ?>
                                </div>
                            </td>
                            <td class="p-4 align-middle text-center">
                                <a href="menus.php?toggle=<?= $m->id ?>" class="inline-block px-3 py-1 text-xs font-bold rounded-full <?= $m->is_active ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-100 text-red-700 border border-red-200' ?> hover:opacity-80 transition-opacity">
                                    <?= $m->is_active ? 'AKTIF' : 'NONAKTIF' ?>
                                </a>
                            </td>
                            <td class="p-4 text-center align-middle whitespace-nowrap">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="menus.php?edit=<?= $m->id ?>" class="text-blue-600 hover:bg-blue-50 w-8 h-8 rounded flex items-center justify-center transition-colors" title="Edit Data">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="menus.php?delete=<?= $m->id ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus menu ini?')" class="text-red-600 hover:bg-red-50 w-8 h-8 rounded flex items-center justify-center transition-colors" title="Hapus Data">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(count($menus) == 0): ?>
                        <tr>
                            <td colspan="4" class="p-8 text-center text-slate-400 bg-slate-50">
                                Belum ada menu yang ditambahkan.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4 p-4 bg-blue-50 text-blue-800 text-sm rounded-xl border border-blue-200 flex items-start">
                <i class="fas fa-info-circle text-blue-500 mt-1 mr-3 text-lg"></i>
                <div>
                    <strong>Tips Penulisan URL:</strong>
                    <ul class="list-disc ml-5 mt-1 opacity-80">
                        <li>Gunakan <code>index.php#id-section</code> untuk menu yang mengarah ke bagian di halaman utama. (Contoh: <code>index.php#beranda</code>, <code>index.php#agenda</code>).</li>
                        <li>Gunakan nama file untuk halaman terpisah. (Contoh: <code>kontak.php</code>).</li>
                        <li>Gunakan URL lengkap untuk website luar. (Contoh: <code>https://kpu.go.id</code>).</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
<?php require 'footer.php'; ?>
