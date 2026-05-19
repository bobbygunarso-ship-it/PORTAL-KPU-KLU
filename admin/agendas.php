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
    $stmt = $pdo->prepare("DELETE FROM agendas WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: agendas.php?msg=deleted");
    exit;
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $location = $_POST['location'];
    $status = $_POST['status'] ?? 'open';
    
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Update
        $stmt = $pdo->prepare("UPDATE agendas SET title=?, description=?, start_date=?, end_date=?, location=?, status=? WHERE id=?");
        $stmt->execute([$title, $description, $start_date, $end_date, $location, $status, $_POST['id']]);
        header("Location: agendas.php?msg=updated");
        exit;
    } else {
        // Insert
        $stmt = $pdo->prepare("INSERT INTO agendas (title, description, start_date, end_date, location, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $start_date, $end_date, $location, $status]);
        header("Location: agendas.php?msg=added");
        exit;
    }
}

// Fetch all agendas
$stmt = $pdo->query("SELECT * FROM agendas ORDER BY start_date DESC");
$agendas = $stmt->fetchAll();

// Edit Mode Check
$editAgenda = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM agendas WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editAgenda = $stmt->fetch();
}

require 'header.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <!-- Form Add/Edit -->
    <div class="lg:col-span-1 bg-white p-6 rounded-2xl shadow-sm border border-slate-200 h-fit">
        <h3 class="text-xl font-bold mb-6 flex items-center border-b pb-4">
            <i class="fas <?= $editAgenda ? 'fa-edit text-blue-500' : 'fa-calendar-plus text-orange-500' ?> mr-3"></i>
            <?= $editAgenda ? 'Edit Agenda' : 'Tambah Agenda' ?>
        </h3>
        
        <?php if (isset($_GET['msg'])): ?>
            <div class="bg-green-50 text-green-700 p-4 rounded-xl mb-6 text-sm font-medium border border-green-200 flex items-center">
                <i class="fas fa-check-circle mr-2 text-green-500 text-lg"></i> Data agenda berhasil diperbarui.
            </div>
        <?php endif; ?>

        <form action="agendas.php" method="POST" class="space-y-4">
            <?php if ($editAgenda): ?>
                <input type="hidden" name="id" value="<?= $editAgenda->id ?>">
            <?php endif; ?>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Judul Kegiatan</label>
                <input type="text" name="title" value="<?= $editAgenda ? htmlspecialchars($editAgenda->title) : '' ?>" required class="w-full border border-slate-300 bg-slate-50 rounded-xl p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all" placeholder="Contoh: Rapat Pleno...">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Waktu Mulai</label>
                <input type="datetime-local" name="start_date" value="<?= $editAgenda ? date('Y-m-d\TH:i', strtotime($editAgenda->start_date)) : '' ?>" required class="w-full border border-slate-300 bg-slate-50 rounded-xl p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Waktu Selesai</label>
                <input type="datetime-local" name="end_date" value="<?= $editAgenda ? date('Y-m-d\TH:i', strtotime($editAgenda->end_date)) : '' ?>" required class="w-full border border-slate-300 bg-slate-50 rounded-xl p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Lokasi / Tempat</label>
                <input type="text" name="location" value="<?= $editAgenda ? htmlspecialchars($editAgenda->location) : '' ?>" required class="w-full border border-slate-300 bg-slate-50 rounded-xl p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all" placeholder="Contoh: Aula KPU...">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Deskripsi (Opsional)</label>
                <textarea name="description" rows="3" class="w-full border border-slate-300 bg-slate-50 rounded-xl p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all" placeholder="Keterangan singkat kegiatan..."><?= $editAgenda ? htmlspecialchars($editAgenda->description) : '' ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Status</label>
                <select name="status" class="w-full border border-slate-300 bg-slate-50 rounded-xl p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all">
                    <option value="open" <?= ($editAgenda && $editAgenda->status == 'open') ? 'selected' : '' ?>>Akan Datang / Berjalan (Open)</option>
                    <option value="closed" <?= ($editAgenda && $editAgenda->status == 'closed') ? 'selected' : '' ?>>Selesai (Closed)</option>
                    <option value="cancelled" <?= ($editAgenda && $editAgenda->status == 'cancelled') ? 'selected' : '' ?>>Dibatalkan (Cancelled)</option>
                </select>
            </div>

            <div class="flex space-x-3 pt-4">
                <button type="submit" class="bg-orange-600 text-white px-5 py-3 rounded-xl hover:bg-orange-500 transition-all font-bold w-full shadow-md flex items-center justify-center">
                    <i class="fas <?= $editAgenda ? 'fa-save' : 'fa-plus' ?> mr-2"></i> <?= $editAgenda ? 'Update' : 'Simpan' ?>
                </button>
                <?php if ($editAgenda): ?>
                    <a href="agendas.php" class="bg-slate-200 text-slate-700 px-5 py-3 rounded-xl hover:bg-slate-300 transition-all font-bold text-center flex items-center justify-center">Batal</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Tabel Agenda -->
    <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <h3 class="text-xl font-bold mb-6 flex items-center border-b pb-4">
            <i class="fas fa-list text-slate-500 mr-3"></i> Daftar Agenda Kegiatan
        </h3>
        
        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 text-xs font-bold uppercase tracking-wider">
                        <th class="p-4 border-b">Detail Kegiatan</th>
                        <th class="p-4 border-b w-40 text-center">Waktu</th>
                        <th class="p-4 border-b w-24 text-center">Status</th>
                        <th class="p-4 border-b w-24 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach($agendas as $a): ?>
                    <tr class="hover:bg-orange-50/50 transition-colors group">
                        <td class="p-4 align-top">
                            <div class="font-bold text-slate-800 text-base mb-1">
                                <?= htmlspecialchars($a->title) ?>
                            </div>
                            <div class="text-xs text-slate-500 mb-2 flex items-center">
                                <i class="fas fa-map-marker-alt text-orange-500 mr-1.5"></i> <?= htmlspecialchars($a->location) ?>
                            </div>
                            <div class="text-sm text-slate-600 line-clamp-2"><?= nl2br(htmlspecialchars($a->description)) ?></div>
                        </td>
                        <td class="p-4 align-top text-xs text-slate-600 text-center">
                            <div class="mb-1 font-semibold"><?= date('d M Y', strtotime($a->start_date)) ?></div>
                            <div class="text-[10px] bg-slate-100 px-2 py-1 rounded inline-block">
                                <?= date('H:i', strtotime($a->start_date)) ?> - <?= date('H:i', strtotime($a->end_date)) ?>
                            </div>
                        </td>
                        <td class="p-4 align-top text-center">
                            <?php if($a->status == 'open'): ?>
                                <span class="bg-green-100 text-green-700 text-[10px] px-2 py-1 rounded-full font-bold uppercase border border-green-200">Open</span>
                            <?php elseif($a->status == 'closed'): ?>
                                <span class="bg-slate-100 text-slate-600 text-[10px] px-2 py-1 rounded-full font-bold uppercase border border-slate-200">Closed</span>
                            <?php else: ?>
                                <span class="bg-red-100 text-red-700 text-[10px] px-2 py-1 rounded-full font-bold uppercase border border-red-200">Batal</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-4 text-center align-top whitespace-nowrap">
                            <div class="flex items-center justify-center space-x-2">
                                <a href="agendas.php?edit=<?= $a->id ?>" class="text-blue-600 hover:bg-blue-50 w-8 h-8 rounded flex items-center justify-center transition-colors" title="Edit Data">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="agendas.php?delete=<?= $a->id ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus agenda ini?')" class="text-red-600 hover:bg-red-50 w-8 h-8 rounded flex items-center justify-center transition-colors" title="Hapus Data">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if(count($agendas) == 0): ?>
                    <tr>
                        <td colspan="4" class="p-8 text-center text-slate-400 bg-slate-50">
                            <i class="fas fa-calendar-times text-4xl mb-3 block opacity-30"></i>
                            Belum ada agenda kegiatan yang dijadwalkkan.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require 'footer.php'; ?>
