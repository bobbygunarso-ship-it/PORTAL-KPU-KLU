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
    $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: messages.php?msg=deleted");
    exit;
}

// Handle Mark as Read
if (isset($_GET['read'])) {
    $id = $_GET['read'];
    $stmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: messages.php");
    exit;
}

// Fetch all messages
$stmt = $pdo->query("SELECT * FROM messages ORDER BY id DESC");
$messages = $stmt->fetchAll();

// Fetch specific message for view
$viewMessage = null;
if (isset($_GET['view'])) {
    $id = $_GET['view'];
    $stmt = $pdo->prepare("SELECT * FROM messages WHERE id = ?");
    $stmt->execute([$id]);
    $viewMessage = $stmt->fetch();
    
    // Mark as read when opened
    if($viewMessage && $viewMessage->is_read == 0) {
        $stmtU = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE id = ?");
        $stmtU->execute([$id]);
        $viewMessage->is_read = 1;
    }
}

require 'header.php';
?>

<div class="grid grid-cols-1 <?= $viewMessage ? 'lg:grid-cols-3' : '' ?> gap-8">
    
    <!-- Tabel Pesan -->
    <div class="<?= $viewMessage ? 'lg:col-span-2' : '' ?> bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <h3 class="text-xl font-bold mb-6 flex items-center border-b pb-4">
            <i class="fas fa-envelope text-orange-500 mr-3"></i> Kotak Masuk Pengaduan
        </h3>
        
        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div class="bg-green-50 text-green-700 p-4 rounded-xl mb-6 text-sm font-medium border border-green-200 flex items-center">
                <i class="fas fa-check-circle mr-2 text-green-500 text-lg"></i> Pesan berhasil dihapus.
            </div>
        <?php endif; ?>

        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 text-xs font-bold uppercase tracking-wider">
                        <th class="p-4 border-b">Pengirim</th>
                        <th class="p-4 border-b">Subjek</th>
                        <th class="p-4 border-b w-32 text-center">Tanggal</th>
                        <th class="p-4 border-b w-24 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach($messages as $m): ?>
                    <tr class="hover:bg-orange-50/50 transition-colors group <?= $m->is_read == 0 ? 'bg-orange-50/30' : '' ?>">
                        <td class="p-4 align-top">
                            <div class="font-bold text-slate-800 text-base mb-1 <?= $m->is_read == 0 ? 'text-orange-600' : '' ?>">
                                <?= htmlspecialchars($m->name) ?>
                                <?php if($m->is_read == 0): ?>
                                    <span class="inline-block bg-orange-500 text-white text-[10px] px-2 py-0.5 rounded-full ml-2 align-middle">BARU</span>
                                <?php endif; ?>
                            </div>
                            <div class="text-xs text-slate-500 flex items-center">
                                <i class="fab fa-whatsapp text-slate-400 mr-1.5"></i> <?= htmlspecialchars($m->whatsapp) ?>
                            </div>
                        </td>
                        <td class="p-4 align-top">
                            <div class="text-sm font-semibold text-slate-700 mb-1"><?= htmlspecialchars($m->subject) ?></div>
                            <div class="text-xs text-slate-500 line-clamp-1"><?= htmlspecialchars($m->message) ?></div>
                        </td>
                        <td class="p-4 align-top text-xs text-slate-600 text-center">
                            <div class="mb-1"><?= date('d M Y', strtotime($m->created_at)) ?></div>
                            <div class="text-[10px] text-slate-400"><?= date('H:i', strtotime($m->created_at)) ?></div>
                        </td>
                        <td class="p-4 text-center align-top whitespace-nowrap">
                            <div class="flex items-center justify-center space-x-2">
                                <a href="messages.php?view=<?= $m->id ?>" class="text-blue-600 hover:bg-blue-50 w-8 h-8 rounded flex items-center justify-center transition-colors" title="Baca Pesan">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="messages.php?delete=<?= $m->id ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus pesan ini?')" class="text-red-600 hover:bg-red-50 w-8 h-8 rounded flex items-center justify-center transition-colors" title="Hapus Data">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if(count($messages) == 0): ?>
                    <tr>
                        <td colspan="4" class="p-8 text-center text-slate-400 bg-slate-50">
                            <i class="fas fa-inbox text-4xl mb-3 block opacity-30"></i>
                            Kotak masuk kosong. Belum ada pesan dari masyarakat.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Baca Pesan -->
    <?php if($viewMessage): ?>
    <div class="lg:col-span-1 bg-white p-6 rounded-2xl shadow-sm border border-slate-200 h-fit sticky top-6">
        <div class="flex justify-between items-center border-b pb-4 mb-4">
            <h3 class="text-lg font-bold flex items-center text-slate-800">
                <i class="fas fa-envelope-open-text text-orange-500 mr-2"></i> Baca Pesan
            </h3>
            <a href="messages.php" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times"></i></a>
        </div>
        
        <div class="space-y-4">
            <div>
                <div class="text-xs text-slate-500 font-semibold uppercase tracking-wider mb-1">Dari</div>
                <div class="font-bold text-slate-800"><?= htmlspecialchars($viewMessage->name) ?></div>
                <a href="https://wa.me/<?= htmlspecialchars(preg_replace('/[^0-9]/', '', $viewMessage->whatsapp)) ?>" target="_blank" class="text-sm text-green-600 hover:underline"><i class="fab fa-whatsapp"></i> <?= htmlspecialchars($viewMessage->whatsapp) ?></a>
            </div>
            
            <div>
                <div class="text-xs text-slate-500 font-semibold uppercase tracking-wider mb-1">Subjek</div>
                <div class="font-semibold text-slate-800 bg-slate-50 p-2 border border-slate-200 rounded-lg text-sm"><?= htmlspecialchars($viewMessage->subject) ?></div>
            </div>
            
            <div>
                <div class="text-xs text-slate-500 font-semibold uppercase tracking-wider mb-1">Isi Pesan</div>
                <div class="text-sm text-slate-700 bg-orange-50/50 p-4 border border-orange-100 rounded-xl leading-relaxed whitespace-pre-wrap"><?= htmlspecialchars($viewMessage->message) ?></div>
            </div>
            
            <div class="pt-4 border-t border-slate-100 flex justify-between items-center text-xs text-slate-500">
                <span>Diterima: <?= date('d M Y H:i', strtotime($viewMessage->created_at)) ?></span>
                <a href="https://wa.me/<?= htmlspecialchars(preg_replace('/[^0-9]/', '', $viewMessage->whatsapp)) ?>?text=Halo%20<?= rawurlencode($viewMessage->name) ?>,%20Terkait%20aduan%20Anda:%20<?= rawurlencode($viewMessage->subject) ?>" target="_blank" class="bg-green-100 text-green-700 px-3 py-1.5 rounded-lg hover:bg-green-200 transition-colors font-semibold">
                    <i class="fab fa-whatsapp mr-1"></i> Balas via WA
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php require 'footer.php'; ?>
