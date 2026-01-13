<?php
session_start();
require '../config/db.php';
require '../includes/header.php';

if (!isset($_GET['id'])) {
  echo "<script>window.location='index';</script>";
  exit;
}

$book_id = $_GET['id'];
$user_id = $_SESSION['user_id'] ?? 0;

// 1. Ambil Detail Buku
$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$book_id]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$book) {
  echo "<div class='container mx-auto p-10 text-center'>Buku tidak ditemukan. <a href='../index' class='text-primary font-bold'>Kembali</a></div>";
  require '../includes/footer.php';
  exit;
}

// 2. Ambil Status Interaksi User (Favorit/Read)
$interaction = ['is_favorite' => 0, 'is_read' => 0];
if ($user_id) {
  $stmt = $pdo->prepare("SELECT * FROM user_interactions WHERE user_id = ? AND book_id = ?");
  $stmt->execute([$user_id, $book_id]);
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($result) $interaction = $result;
}

// 3. Setup Gambar
$imgUrl = !empty($book['image_url']) ? $book['image_url'] : "https://covers.openlibrary.org/b/isbn/{$book['isbn']}-M.jpg";
?>

<div class="max-w-5xl mx-auto bg-white rounded-3xl shadow-xl overflow-hidden my-8 border border-gray-100">
  <div class="md:flex">

    <div class="md:w-1/3 bg-gray-100 relative group min-h-[500px]">
      <img src="<?= htmlspecialchars($imgUrl) ?>"
        class="w-full h-full object-cover object-center absolute inset-0"
        onerror="this.src='https://via.placeholder.com/400x600?text=No+Cover'">

      <span class="absolute top-4 left-4 bg-white/90 backdrop-blur text-gray-800 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide shadow-sm z-10">
        <?= htmlspecialchars($book['category'] ?? 'General') ?>
      </span>
    </div>

    <div class="md:w-2/3 p-8 md:p-12 flex flex-col relative">

      <a href="../index" class="text-gray-400 hover:text-primary mb-6 flex items-center gap-2 text-sm font-bold transition w-fit">
        ← Kembali ke Dashboard
      </a>

      <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-2 leading-tight">
        <?= htmlspecialchars($book['title']) ?>
      </h1>
      <p class="text-lg text-gray-500 mb-6 font-medium">
        Penulis: <span class="text-primary"><?= htmlspecialchars($book['author']) ?></span>
      </p>

      <div class="flex gap-6 border-y border-gray-100 py-6 mb-8">
        <div>
          <span class="block text-xs text-gray-400 uppercase tracking-wider font-bold">Rating</span>
          <span class="text-xl font-bold text-yellow-500 flex items-center gap-1">
            ★ <?= $book['rating'] ?>
          </span>
        </div>
        <div>
          <span class="block text-xs text-gray-400 uppercase tracking-wider font-bold">Halaman</span>
          <span class="text-xl font-bold text-gray-700"><?= $book['pages'] ?> Hal</span>
        </div>
        <div>
          <span class="block text-xs text-gray-400 uppercase tracking-wider font-bold">ISBN</span>
          <span class="text-xl font-bold text-gray-700 font-mono text-base"><?= $book['isbn'] ?></span>
        </div>
        <div>
          <span class="block text-xs text-gray-400 uppercase tracking-wider font-bold">Stok</span>
          <span class="text-xl font-bold <?= $book['stock'] > 0 ? 'text-green-600' : 'text-red-500' ?>">
            <?= $book['stock'] ?>
          </span>
        </div>
      </div>

      <div class="mb-8 flex-grow">
        <h3 class="font-bold text-gray-800 mb-2">Sinopsis</h3>
        <p class="text-gray-600 leading-relaxed text-justify">
          <?= nl2br(htmlspecialchars($book['description'] ?? 'Belum ada deskripsi untuk buku ini. Buku ini merupakan salah satu koleksi terbaik di perpustakaan kami.')) ?>
        </p>
      </div>

      <div class="mt-auto flex flex-col md:flex-row gap-4 w-full">

        <div class="flex-1">
          <?php if ($book['stock'] > 0): ?>
            <form action="loans" method="GET" class="h-full">
              <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
              <button type="submit" class="w-full h-full bg-primary text-white px-4 py-4 rounded-xl font-bold hover:bg-blue-800 transition shadow-lg flex items-center justify-center gap-2">
                <span>📖</span> Pinjam
              </button>
            </form>
          <?php else: ?>
            <button disabled class="w-full h-full bg-gray-200 text-gray-400 px-4 py-4 rounded-xl font-bold cursor-not-allowed">
              Stok Habis
            </button>
          <?php endif; ?>
        </div>

        <div class="flex-1">
          <form action="<?= BASE_URL ?>actions/track_handler" method="POST" class="h-full">
            <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
            <input type="hidden" name="type" value="favorite">
            <button type="submit" class="w-full h-full px-4 py-4 rounded-xl font-bold border-2 transition flex items-center gap-2 justify-center <?= $interaction['is_favorite'] ? 'border-pink-500 text-pink-600 bg-pink-50' : 'border-gray-200 text-gray-500 hover:border-pink-300 hover:text-pink-400' ?>">
              <?= $interaction['is_favorite'] ? '❤️ Disukai' : '🤍 Suka' ?>
            </button>
          </form>
        </div>

        <div class="flex-1">
          <form action="<?= BASE_URL ?>actions/track_handler" method="POST" class="h-full">
            <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
            <input type="hidden" name="type" value="read">
            <button type="submit" class="w-full h-full px-4 py-4 rounded-xl font-bold border-2 transition flex items-center gap-2 justify-center <?= $interaction['is_read'] ? 'border-green-500 text-green-600 bg-green-50' : 'border-gray-200 text-gray-500 hover:border-green-300 hover:text-green-400' ?>">
              <?= $interaction['is_read'] ? '✅ Selesai' : '📚 Baca' ?>
            </button>
          </form>
        </div>

      </div>

    </div>
  </div>
</div>

<?php require '../includes/footer.php'; ?>