<?php
session_start();
require '../config/db.php';
require '../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: index");
  exit;
}

$book = null;
if (isset($_GET['id'])) {
  $stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
  $stmt->execute([$_GET['id']]);
  $book = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="container mx-auto max-w-4xl py-10 px-4">

  <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">

    <div class="bg-primary p-8 text-white flex justify-between items-center">
      <div>
        <h2 class="text-2xl font-bold"><?= $book ? 'Edit Data Buku' : 'Tambah Buku Baru' ?></h2>
        <p class="text-blue-200 text-sm mt-1">Lengkapi informasi buku di bawah ini.</p>
      </div>
      <div class="bg-white/10 p-3 rounded-full">
        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
        </svg>
      </div>
    </div>

    <form action="../actions/book_handler" method="POST" class="p-8 space-y-6">
      <input type="hidden" name="id" value="<?= $book['id'] ?? '' ?>">

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <div class="md:col-span-2">
          <label class="block text-sm font-bold text-gray-700 mb-2">Judul Buku</label>
          <input type="text" name="title" value="<?= htmlspecialchars($book['title'] ?? '') ?>" class="w-full border border-gray-300 px-4 py-3 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary outline-none transition" placeholder="Masukkan judul lengkap" required>
        </div>

        <div>
          <label class="block text-sm font-bold text-gray-700 mb-2">Penulis</label>
          <input type="text" name="author" value="<?= htmlspecialchars($book['author'] ?? '') ?>" class="w-full border border-gray-300 px-4 py-3 rounded-xl focus:ring-2 focus:ring-primary outline-none transition" placeholder="Nama penulis" required>
        </div>

        <div>
          <label class="block text-sm font-bold text-gray-700 mb-2">Kategori</label>
          <input type="text" name="category" value="<?= htmlspecialchars($book['category'] ?? '') ?>" class="w-full border border-gray-300 px-4 py-3 rounded-xl focus:ring-2 focus:ring-primary outline-none transition" placeholder="Contoh: Fiksi, Sains" required>
        </div>

        <div>
          <label class="block text-sm font-bold text-gray-700 mb-2">ISBN</label>
          <input type="text" name="isbn" value="<?= htmlspecialchars($book['isbn'] ?? '') ?>" class="w-full border border-gray-300 px-4 py-3 rounded-xl focus:ring-2 focus:ring-primary outline-none transition" placeholder="Nomor ISBN" required>
        </div>

        <div>
          <label class="block text-sm font-bold text-gray-700 mb-2">Bahasa</label>
          <select name="language" class="w-full border border-gray-300 px-4 py-3 rounded-xl focus:ring-2 focus:ring-primary outline-none bg-white">
            <option value="eng" <?= ($book['language'] ?? '') == 'eng' ? 'selected' : '' ?>>Inggris (eng)</option>
            <option value="ind" <?= ($book['language'] ?? '') == 'ind' ? 'selected' : '' ?>>Indonesia (ind)</option>
            <option value="jpn" <?= ($book['language'] ?? '') == 'jpn' ? 'selected' : '' ?>>Jepang (jpn)</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-bold text-gray-700 mb-2">Rating (0-5)</label>
          <input type="number" step="0.01" name="rating" value="<?= htmlspecialchars($book['rating'] ?? '') ?>" class="w-full border border-gray-300 px-4 py-3 rounded-xl focus:ring-2 focus:ring-primary outline-none transition" placeholder="4.5">
        </div>

        <div>
          <label class="block text-sm font-bold text-gray-700 mb-2">Jumlah Halaman</label>
          <input type="number" name="pages" value="<?= htmlspecialchars($book['pages'] ?? '') ?>" class="w-full border border-gray-300 px-4 py-3 rounded-xl focus:ring-2 focus:ring-primary outline-none transition" placeholder="Total halaman">
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm font-bold text-gray-700 mb-2">Stok Buku</label>
          <input type="number" name="stock" value="<?= htmlspecialchars($book['stock'] ?? 5) ?>" class="w-full border border-gray-300 px-4 py-3 rounded-xl focus:ring-2 focus:ring-primary outline-none transition" required>
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm font-bold text-gray-700 mb-2">URL Cover Gambar (Opsional)</label>
          <input type="text" name="image_url" value="<?= htmlspecialchars($book['image_url'] ?? '') ?>" class="w-full border border-gray-300 px-4 py-3 rounded-xl focus:ring-2 focus:ring-primary outline-none transition" placeholder="https://...">
          <p class="text-xs text-gray-400 mt-1">Biarkan kosong untuk menggunakan cover otomatis dari OpenLibrary.</p>
        </div>
      </div>

      <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-100">
        <a href="index" class="px-6 py-3 rounded-xl font-bold text-gray-500 hover:bg-gray-100 transition">Batal</a>
        <button type="submit" class="bg-primary text-white px-8 py-3 rounded-xl font-bold shadow-lg hover:bg-blue-800 transition transform hover:-translate-y-0.5">
          Simpan Data
        </button>
      </div>
    </form>
  </div>
</div>

<?php require '../includes/footer.php'; ?>