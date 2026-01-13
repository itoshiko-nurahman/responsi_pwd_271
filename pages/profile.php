<?php
session_start();
require '../config/db.php';
require '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login");
  exit;
}

$isAdmin = ($_SESSION['role'] === 'admin');

// Ambil Data User
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle Update Profil
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $full_name = htmlspecialchars($_POST['full_name']);
  $nim = htmlspecialchars($_POST['nim']);
  $upd = $pdo->prepare("UPDATE users SET full_name = ?, nim = ? WHERE id = ?");
  if ($upd->execute([$full_name, $nim, $_SESSION['user_id']])) {
    $msg = "<div class='bg-green-100 text-green-700 p-3 rounded-lg mb-4 text-sm font-bold flex items-center gap-2'>✅ Profil berhasil diperbarui!</div>";
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
  }
}

// Hanya ambil data Favorit/Baca jika BUKAN Admin
$favorites = [];
$reads = [];
if (!$isAdmin) {
  $stmtFav = $pdo->prepare("SELECT books.* FROM books JOIN user_interactions ON books.id = user_interactions.book_id WHERE user_interactions.user_id = ? AND user_interactions.is_favorite = 1 ORDER BY user_interactions.created_at DESC");
  $stmtFav->execute([$_SESSION['user_id']]);
  $favorites = $stmtFav->fetchAll(PDO::FETCH_ASSOC);

  $stmtRead = $pdo->prepare("SELECT books.* FROM books JOIN user_interactions ON books.id = user_interactions.book_id WHERE user_interactions.user_id = ? AND user_interactions.is_read = 1 ORDER BY user_interactions.created_at DESC");
  $stmtRead->execute([$_SESSION['user_id']]);
  $reads = $stmtRead->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="container mx-auto max-w-7xl py-8 px-4">

  <div class="<?= $isAdmin ? 'max-w-2xl mx-auto' : 'flex flex-col lg:flex-row gap-8' ?>">

    <div class="<?= $isAdmin ? 'w-full' : 'w-full lg:w-1/3' ?>">
      <div class="bg-white p-8 rounded-2xl shadow-lg border border-gray-100 sticky top-24">
        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-100">
          <div class="w-12 h-12 bg-primary text-white rounded-full flex items-center justify-center font-bold text-xl">
            <?= substr($user['username'], 0, 1) ?>
          </div>
          <div>
            <h2 class="text-xl font-bold text-gray-800">Edit Profil <?= $isAdmin ? 'Admin' : '' ?></h2>
            <p class="text-xs text-gray-500">Perbarui data akun</p>
          </div>
        </div>

        <?= $msg ?>

        <form method="POST">
          <div class="mb-4">
            <label class="block text-gray-600 text-xs font-bold mb-2 uppercase tracking-wide">Username</label>
            <input type="text" value="<?= htmlspecialchars($user['username']) ?>" class="w-full bg-gray-50 border border-gray-200 p-3 rounded-xl text-gray-500 text-sm font-medium" readonly>
          </div>
          <div class="mb-4">
            <label class="block text-gray-600 text-xs font-bold mb-2 uppercase tracking-wide">Nama Lengkap</label>
            <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" class="w-full border border-gray-300 p-3 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary outline-none transition" required>
          </div>
          <div class="mb-6">
            <label class="block text-gray-600 text-xs font-bold mb-2 uppercase tracking-wide"><?= $isAdmin ? 'ID Admin / NIP' : 'NIM' ?></label>
            <input type="text" name="nim" value="<?= htmlspecialchars($user['nim'] ?? '') ?>" class="w-full border border-gray-300 p-3 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary outline-none transition" required>
          </div>
          <button type="submit" class="w-full bg-primary text-white font-bold py-3.5 rounded-xl hover:bg-blue-800 transition shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
            Simpan Perubahan
          </button>
        </form>

        <div class="mt-6 pt-6 border-t border-gray-100">
          <a href="../actions/logout" onclick="return confirm('Yakin ingin keluar?')" class="flex items-center justify-center gap-2 w-full text-center bg-red-50 text-red-600 border border-red-200 font-bold py-3 rounded-xl hover:bg-red-600 hover:text-white transition">
            🚪 Logout
          </a>
        </div>
      </div>
    </div>

    <?php if (!$isAdmin): ?>
      <div class="w-full lg:w-2/3 space-y-8">

        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
          <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">❤️ Favorit Saya</h3>
            <span class="bg-pink-100 text-pink-600 text-xs font-bold px-3 py-1 rounded-full"><?= count($favorites) ?> Buku</span>
          </div>
          <div class="p-6 max-h-[400px] overflow-y-auto custom-scrollbar bg-white">
            <?php if (count($favorites) > 0): ?>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <?php foreach ($favorites as $b): ?>
                  <a href="detail?id=<?= $b['id'] ?>" class="flex gap-4 p-3 rounded-xl border border-gray-100 hover:border-pink-300 hover:shadow-md transition bg-white">
                    <img src="<?= $b['image_url'] ?>" class="w-16 h-24 object-cover rounded-lg shadow-sm" onerror="this.src='https://via.placeholder.com/150?text=NoCover'">
                    <div class="flex flex-col justify-center overflow-hidden">
                      <h4 class="font-bold text-gray-800 text-sm truncate"><?= htmlspecialchars($b['title']) ?></h4>
                      <p class="text-xs text-gray-500 truncate mb-2"><?= htmlspecialchars($b['author']) ?></p>
                      <span class="text-[10px] bg-pink-50 text-pink-600 px-2 py-0.5 rounded w-fit font-bold">Favorit</span>
                    </div>
                  </a>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div class="text-center py-12 text-gray-400">
                <p class="text-sm">Belum ada buku favorit.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
          <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">✅ Telah Dibaca</h3>
            <span class="bg-green-100 text-green-600 text-xs font-bold px-3 py-1 rounded-full"><?= count($reads) ?> Buku</span>
          </div>
          <div class="p-6 max-h-[400px] overflow-y-auto custom-scrollbar bg-white">
            <?php if (count($reads) > 0): ?>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <?php foreach ($reads as $b): ?>
                  <a href="detail?id=<?= $b['id'] ?>" class="flex gap-4 p-3 rounded-xl border border-gray-100 hover:border-green-300 hover:shadow-md transition bg-white opacity-90 hover:opacity-100">
                    <img src="<?= $b['image_url'] ?>" class="w-16 h-24 object-cover rounded-lg shadow-sm grayscale group-hover:grayscale-0 transition" onerror="this.src='https://via.placeholder.com/150?text=NoCover'">
                    <div class="flex flex-col justify-center overflow-hidden">
                      <h4 class="font-bold text-gray-800 text-sm truncate"><?= htmlspecialchars($b['title']) ?></h4>
                      <p class="text-xs text-gray-500 truncate mb-2"><?= htmlspecialchars($b['author']) ?></p>
                      <span class="text-[10px] bg-green-50 text-green-600 px-2 py-0.5 rounded w-fit font-bold">Selesai</span>
                    </div>
                  </a>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div class="text-center py-12 text-gray-400">
                <p class="text-sm">Belum ada riwayat bacaan.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>

  </div>
</div>

<style>
  .custom-scrollbar::-webkit-scrollbar {
    width: 6px;
  }

  .custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
  }
</style>
<?php require '../includes/footer.php'; ?>