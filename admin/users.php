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
    
    // Prevent deleting self
    if ($id == $_SESSION['user_id']) {
        header("Location: users.php?err=self_delete");
        exit;
    }
    
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: users.php?msg=deleted");
    exit;
}

// Handle Add / Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Validasi username exist (kecuali saat update diri sendiri dengan username sama)
    $is_edit = isset($_POST['id']) && !empty($_POST['id']);
    $id = $is_edit ? $_POST['id'] : null;
    
    $check = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $check->execute([$username, $id ? $id : 0]);
    
    if ($check->rowCount() > 0) {
        header("Location: users.php?err=exists");
        exit;
    }
    
    if ($is_edit) {
        // Update user
        if (!empty($password)) {
            // Update with new password
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
            $stmt->execute([$username, $hashed, $id]);
        } else {
            // Update username only
            $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
            $stmt->execute([$username, $id]);
        }
        
        // If updating self, update session
        if ($id == $_SESSION['user_id']) {
            $_SESSION['username'] = $username;
        }
        
        header("Location: users.php?msg=updated");
        exit;
    } else {
        // Insert new user
        if (empty($password)) {
            header("Location: users.php?err=nopass");
            exit;
        }
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $hashed]);
        header("Location: users.php?msg=added");
        exit;
    }
}

// Fetch all users
$stmt = $pdo->query("SELECT id, username, created_at FROM users ORDER BY id ASC");
$users = $stmt->fetchAll();

// Edit Mode
$editUser = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editUser = $stmt->fetch();
}

require 'header.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <!-- Form Add/Edit -->
    <div class="lg:col-span-1 bg-white p-6 rounded-2xl shadow-sm border border-slate-200 h-fit">
        <h3 class="text-xl font-bold mb-6 flex items-center border-b pb-4">
            <i class="fas <?= $editUser ? 'fa-user-edit text-blue-500' : 'fa-user-plus text-orange-500' ?> mr-3"></i>
            <?= $editUser ? 'Edit Pengguna' : 'Tambah Pengguna' ?>
        </h3>
        
        <?php if (isset($_GET['msg'])): ?>
            <div class="bg-green-50 text-green-700 p-4 rounded-xl mb-6 text-sm font-medium border border-green-200 flex items-center">
                <i class="fas fa-check-circle mr-2 text-green-500 text-lg"></i> Data pengguna berhasil diperbarui.
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['err'])): ?>
            <div class="bg-red-50 text-red-700 p-4 rounded-xl mb-6 text-sm font-medium border border-red-200 flex items-center">
                <i class="fas fa-exclamation-circle mr-2 text-red-500 text-lg"></i> 
                <?php 
                    if($_GET['err'] == 'exists') echo 'Username sudah digunakan, silakan pilih yang lain.';
                    else if($_GET['err'] == 'nopass') echo 'Password wajib diisi untuk pengguna baru.';
                    else if($_GET['err'] == 'self_delete') echo 'Anda tidak dapat menghapus akun Anda sendiri saat sedang login.';
                ?>
            </div>
        <?php endif; ?>

        <form action="users.php" method="POST" class="space-y-5">
            <?php if ($editUser): ?>
                <input type="hidden" name="id" value="<?= $editUser->id ?>">
            <?php endif; ?>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Username</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fas fa-user"></i>
                    </div>
                    <input type="text" name="username" value="<?= $editUser ? htmlspecialchars($editUser->username) : '' ?>" required class="w-full border border-slate-300 bg-slate-50 rounded-xl pl-10 p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all" placeholder="admin2">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Password <?= $editUser ? '<span class="text-xs text-slate-400 font-normal">(Kosongkan jika tidak ingin diubah)</span>' : '' ?></label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fas fa-lock"></i>
                    </div>
                    <input type="password" name="password" id="password" <?= $editUser ? '' : 'required' ?> class="w-full border border-slate-300 bg-slate-50 rounded-xl pl-10 pr-10 p-3 focus:bg-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 outline-none transition-all" placeholder="••••••••">
                    <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-orange-500" onclick="togglePassword()">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <div class="flex space-x-3 pt-4">
                <button type="submit" class="bg-orange-600 text-white px-5 py-3 rounded-xl hover:bg-orange-500 transition-all font-bold w-full shadow-md hover:shadow-lg hover:-translate-y-0.5 flex items-center justify-center">
                    <i class="fas <?= $editUser ? 'fa-save' : 'fa-plus' ?> mr-2"></i> <?= $editUser ? 'Update Akun' : 'Simpan Akun' ?>
                </button>
                <?php if ($editUser): ?>
                    <a href="users.php" class="bg-slate-200 text-slate-700 px-5 py-3 rounded-xl hover:bg-slate-300 transition-all font-bold text-center flex items-center justify-center">Batal</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Tabel User -->
    <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-slate-200 h-fit">
        <h3 class="text-xl font-bold mb-6 flex items-center border-b pb-4">
            <i class="fas fa-users text-slate-500 mr-3"></i> Daftar Pengguna (Admin)
        </h3>
        
        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 text-xs font-bold uppercase tracking-wider">
                        <th class="p-4 border-b">ID</th>
                        <th class="p-4 border-b">Username</th>
                        <th class="p-4 border-b">Tgl Dibuat</th>
                        <th class="p-4 border-b text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach($users as $u): ?>
                    <tr class="hover:bg-orange-50/50 transition-colors group">
                        <td class="p-4 text-sm font-semibold text-slate-500">
                            #<?= $u->id ?>
                        </td>
                        <td class="p-4">
                            <div class="font-bold text-slate-800 flex items-center">
                                <?= htmlspecialchars($u->username) ?>
                                <?php if($u->id == $_SESSION['user_id']): ?>
                                    <span class="ml-2 bg-green-100 text-green-700 text-[10px] px-2 py-0.5 rounded-full uppercase tracking-wider font-bold">Anda</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="p-4 text-sm text-slate-500">
                            <?= date('d M Y, H:i', strtotime($u->created_at)) ?>
                        </td>
                        <td class="p-4 text-center align-top whitespace-nowrap">
                            <div class="flex items-center justify-center space-x-2">
                                <a href="users.php?edit=<?= $u->id ?>" class="text-blue-600 hover:text-white hover:bg-blue-600 w-8 h-8 rounded flex items-center justify-center transition-colors shadow-sm bg-blue-50" title="Edit Password">
                                    <i class="fas fa-key"></i>
                                </a>
                                <?php if($u->id != $_SESSION['user_id']): ?>
                                <a href="users.php?delete=<?= $u->id ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus akun ini?')" class="text-red-600 hover:text-white hover:bg-red-600 w-8 h-8 rounded flex items-center justify-center transition-colors shadow-sm bg-red-50" title="Hapus Akun">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                                <?php else: ?>
                                <div class="w-8 h-8 rounded flex items-center justify-center bg-slate-100 text-slate-300 cursor-not-allowed" title="Tidak dapat menghapus diri sendiri">
                                    <i class="fas fa-trash-alt"></i>
                                </div>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function togglePassword() {
        const input = document.getElementById('password');
        const icon = document.getElementById('eyeIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>

<?php require 'footer.php'; ?>
