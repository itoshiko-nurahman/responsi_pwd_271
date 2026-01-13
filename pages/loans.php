<?php
session_start();
require '../config/db.php';
require '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login");
  exit;
}

$isAdmin = ($_SESSION['role'] === 'admin');

if ($isAdmin) {
  // Admin: Ambil semua data + Statistik
  $loans = $pdo->query("SELECT loans.*, books.title, books.image_url FROM loans JOIN books ON loans.book_id = books.id ORDER BY status ASC, loan_date DESC")->fetchAll();

  // Statistik
  $total_pinjam = count($loans);
  $active_loans = 0;
  $returned_loans = 0;
  foreach ($loans as $l) {
    if ($l['status'] == 'borrowed') $active_loans++;
    else $returned_loans++;
  }
} else {
  // User: Ambil data sendiri
  $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
  $stmt->execute([$_SESSION['user_id']]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  $stmt = $pdo->prepare("SELECT loans.*, books.title, books.image_url FROM loans JOIN books ON loans.book_id = books.id WHERE student_name = ? ORDER BY status ASC, loan_date DESC");
  $stmt->execute([$user['full_name'] ?? $user['username']]);
  $loans = $stmt->fetchAll();

  // Data buku untuk dropdown user
  $books = $pdo->query("SELECT * FROM books WHERE stock > 0 ORDER BY title ASC")->fetchAll();
  $selected_book_id = $_GET['book_id'] ?? null;
}
?>

<div class="container mx-auto max-w-7xl py-10 px-4">

  <?php if ($isAdmin): ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
      <div class="bg-white p-6 rounded-2xl shadow border border-gray-100 flex items-center gap-4">
        <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold text-xl">📊</div>
        <div>
          <p class="text-sm text-gray-500">Total Transaksi</p>
          <h3 class="text-2xl font-bold"><?= $total_pinjam ?></h3>
        </div>
      </div>
      <div class="bg-white p-6 rounded-2xl shadow border border-gray-100 flex items-center gap-4">
        <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center font-bold text-xl">⏳</div>
        <div>
          <p class="text-sm text-gray-500">Sedang Dipinjam</p>
          <h3 class="text-2xl font-bold"><?= $active_loans ?></h3>
        </div>
      </div>
      <div class="bg-white p-6 rounded-2xl shadow border border-gray-100 flex items-center gap-4">
        <div class="w-12 h-12 bg-green-100 text-green-600 rounded-full flex items-center justify-center font-bold text-xl">✅</div>
        <div>
          <p class="text-sm text-gray-500">Dikembalikan</p>
          <h3 class="text-2xl font-bold"><?= $returned_loans ?></h3>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
      <div class="p-8 border-b border-gray-100 bg-gray-50">
        <h3 class="font-bold text-gray-800 text-xl">📋 Data Peminjaman Lengkap</h3>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <thead class="bg-white text-gray-500 uppercase text-[10px] tracking-wider font-bold border-b">
            <tr>
              <th class="p-6">Peminjam</th>
              <th class="p-6">Buku</th>
              <th class="p-6">Tgl Pinjam</th>
              <th class="p-6">Tgl Kembali</th>
              <th class="p-6 text-center">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <?php foreach ($loans as $l): ?>
              <tr class="hover:bg-blue-50 transition">
                <td class="p-6 font-bold text-sm text-gray-800"><?= htmlspecialchars($l['student_name']) ?><br><span class="text-xs font-normal text-gray-500"><?= htmlspecialchars($l['nim']) ?></span></td>
                <td class="p-6 text-sm text-gray-600"><?= htmlspecialchars($l['title']) ?></td>
                <td class="p-6 text-sm font-mono"><?= date('d M Y', strtotime($l['loan_date'])) ?></td>
                <td class="p-6 text-sm font-mono"><?= $l['return_date'] ? date('d M Y', strtotime($l['return_date'])) : '-' ?></td>
                <td class="p-6 text-center">
                  <span class="px-3 py-1 rounded-full text-xs font-bold <?= $l['status'] == 'borrowed' ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700' ?>">
                    <?= $l['status'] == 'borrowed' ? 'Dipinjam' : 'Selesai' ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  <?php else: ?>
    <div class="flex flex-col lg:flex-row gap-8">
      <div class="w-full lg:w-1/3">
        <div class="bg-white p-8 rounded-3xl shadow-xl border border-gray-100 sticky top-24">
          <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-100">
            <div class="w-12 h-12 bg-blue-100 text-primary rounded-full flex items-center justify-center">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
              </svg>
            </div>
            <div>
              <h2 class="text-xl font-bold text-gray-800">Ajukan Peminjaman</h2>
              <p class="text-xs text-gray-500">Isi formulir untuk meminjam buku</p>
            </div>
          </div>

          <?php if (empty($user['nim'])): ?>
            <div class="bg-amber-50 text-amber-800 p-4 rounded-xl mb-4 text-sm border border-amber-200 text-center">NIM Belum Diatur. <a href="profile" class="font-bold underline">Lengkapi Profil</a>.</div>
          <?php else: ?>
            <form action="../actions/loan_handler" method="POST" class="space-y-5">
              <input type="hidden" name="action" value="borrow">
              <div><label class="font-bold text-xs uppercase text-gray-600">Nama</label><input type="text" name="student_name" value="<?= htmlspecialchars($user['full_name'] ?? $user['username']) ?>" class="w-full bg-gray-50 border p-3 rounded-xl" readonly></div>
              <div><label class="font-bold text-xs uppercase text-gray-600">NIM</label><input type="text" name="nim" value="<?= htmlspecialchars($user['nim']) ?>" class="w-full bg-gray-50 border p-3 rounded-xl" readonly></div>
              <div>
                <label class="font-bold text-xs uppercase text-gray-600">Buku</label>
                <select name="book_id" class="w-full border p-3 rounded-xl">
                  <option value="">-- Cari Buku --</option>
                  <?php foreach ($books as $b): ?>
                    <option value="<?= $b['id'] ?>" <?= ($selected_book_id == $b['id']) ? 'selected' : '' ?>><?= substr($b['title'], 0, 30) ?>...</option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div><label class="font-bold text-xs uppercase text-gray-600">Tgl Pinjam</label><input type="date" name="loan_date" value="<?= date('Y-m-d') ?>" class="w-full border p-3 rounded-xl"></div>
              <button class="w-full bg-primary text-white font-bold py-3 rounded-xl shadow-lg">🚀 Konfirmasi</button>
            </form>
          <?php endif; ?>
        </div>
      </div>

      <div class="w-full lg:w-2/3">
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden min-h-[400px]">
          <div class="p-8 border-b bg-gray-50">
            <h3 class="font-bold text-xl text-gray-800">⏳ Riwayat Saya</h3>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-left">
              <thead class="bg-white text-gray-500 uppercase text-[10px] font-bold border-b">
                <tr>
                  <th class="p-6">Buku</th>
                  <th class="p-6">Tgl Pinjam</th>
                  <th class="p-6">Tgl Kembali</th>
                  <th class="p-6 text-center">Status</th>
                  <th class="p-6 text-center">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50">
                <?php foreach ($loans as $l): ?>
                  <tr class="hover:bg-blue-50 transition">
                    <td class="p-6 text-sm font-medium text-gray-700"><?= htmlspecialchars($l['title']) ?></td>
                    <td class="p-6 text-sm font-mono text-gray-500"><?= date('d M Y', strtotime($l['loan_date'])) ?></td>
                    <td class="p-6 text-sm font-mono text-gray-500"><?= $l['return_date'] ? date('d M Y', strtotime($l['return_date'])) : '-' ?></td>
                    <td class="p-6 text-center"><span class="px-3 py-1 rounded-full text-xs font-bold <?= $l['status'] == 'borrowed' ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700' ?>"><?= $l['status'] == 'borrowed' ? 'Dipinjam' : 'Selesai' ?></span></td>
                    <td class="p-6 text-center">
                      <?php if ($l['status'] == 'borrowed'): ?>
                        <form action="../actions/loan_handler" method="POST"><input type="hidden" name="action" value="return"><input type="hidden" name="loan_id" value="<?= $l['id'] ?>"><input type="hidden" name="book_id" value="<?= $l['book_id'] ?>"><button class="text-xs bg-blue-50 text-blue-600 px-3 py-1 rounded-lg border border-blue-200 font-bold hover:bg-blue-600 hover:text-white transition">Return</button></form>
                      <?php else: ?><span>-</span><?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

</div>
<?php require '../includes/footer.php'; ?>