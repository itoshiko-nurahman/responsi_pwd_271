<?php
session_start();

// 1. Cek Login
// Jika belum login, lempar ke folder auth/login
if (!isset($_SESSION['user_id'])) {
  header("Location: auth/login");
  exit;
}

// 2. Include Config
require 'config/db.php';

// Definisi BASE_URL jika belum ada (Opsional, untuk keamanan link)
if (!defined('BASE_URL')) {
  define('BASE_URL', 'http://localhost/RESPONSI_PWD_271/');
}

// 3. Cek Role (Admin vs Student)
$isAdmin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');

// 4. Logic Data (Search, Filter, Pagination)
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';

// Pagination Sederhana (Load More)
$current_limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 24;
$next_limit = $current_limit + 24;

// Ambil Daftar Kategori Unik untuk Filter
$cats = $pdo->query("SELECT DISTINCT category FROM books WHERE category IS NOT NULL AND category != '' ORDER BY category ASC")->fetchAll(PDO::FETCH_COLUMN);

// Build Query Utama (Pencarian & Filter)
$sql = "SELECT * FROM books WHERE (title LIKE ? OR author LIKE ?)";
$params = ["%$search%", "%$search%"];

if ($category_filter) {
  $sql .= " AND category = ?";
  $params[] = $category_filter;
}

$sql .= " ORDER BY id DESC LIMIT $current_limit";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Data Tambahan (Statistik untuk Admin / Populer untuk User)
if ($isAdmin) {
  $total_buku = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
  // Stok Kritis = Stok 0
  $stok_habis = $pdo->query("SELECT COUNT(*) FROM books WHERE stock = 0")->fetchColumn();
} else {
  $popular_books = [];
  // Tampilkan buku populer hanya jika tidak sedang mencari/filter
  if (!$search && !$category_filter) {
    $popular_books = $pdo->query("SELECT * FROM books ORDER BY rating DESC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);
  }
}

// Hitung total data matching (untuk tombol Load More)
$sqlCount = "SELECT COUNT(*) FROM books WHERE (title LIKE ? OR author LIKE ?)";
$paramsCount = ["%$search%", "%$search%"];
if ($category_filter) {
  $sqlCount .= " AND category = ?";
  $paramsCount[] = $category_filter;
}
$stmtCount = $pdo->prepare($sqlCount);
$stmtCount->execute($paramsCount);
$total_matching_books = $stmtCount->fetchColumn();

// Include Header (Folder includes)
require 'includes/header.php';
?>

<script>
  function imgError(image) {
    image.onerror = "";
    image.src = "https://via.placeholder.com/300x450/2C5074/FFFFFF?text=No+Cover";
    return true;
  }
</script>

<?php if ($isAdmin): ?>

  <div class="bg-white rounded-2xl shadow-sm p-8 mb-8 border-l-4 border-primary flex flex-col md:flex-row justify-between items-center animate-fade-in-up">
    <div class="mb-4 md:mb-0">
      <h2 class="text-3xl font-bold text-gray-800">Admin Dashboard 👋</h2>
      <p class="text-gray-500 mt-1">Halo <span class="font-bold text-primary"><?= htmlspecialchars($_SESSION['username']) ?></span>, kelola <?= number_format($total_buku) ?> koleksi buku.</p>
    </div>
    <div class="flex gap-3">
      <a href="pages/book_form" class="bg-primary hover:bg-blue-800 text-white px-6 py-3 rounded-lg font-bold shadow transition transform hover:-translate-y-1">
        + Tambah Buku
      </a>
      <a href="pages/report" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-6 py-3 rounded-lg font-bold shadow-sm transition">
        🖨️ Pusat Laporan
      </a>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-gradient-to-r from-blue-600 to-blue-500 rounded-xl p-6 text-white shadow-lg">
      <h3 class="text-sm font-medium opacity-80 uppercase tracking-wider">Total Koleksi</h3>
      <p class="text-4xl font-bold mt-2"><?= number_format($total_buku) ?></p>
    </div>
    <div class="bg-white rounded-xl p-6 shadow-md border border-gray-100 flex justify-between items-center">
      <div>
        <h3 class="text-gray-500 text-sm font-bold uppercase tracking-wider">Stok Kritis (0)</h3>
        <p class="text-3xl font-bold text-red-500 mt-1"><?= number_format($stok_habis) ?></p>
      </div>
      <div class="bg-red-100 p-3 rounded-full text-red-500 text-2xl">⚠️</div>
    </div>
    <div class="bg-white rounded-xl p-6 shadow-md border border-gray-100 flex justify-between items-center">
      <div>
        <h3 class="text-gray-500 text-sm font-bold uppercase tracking-wider">Server Status</h3>
        <p class="text-xl font-bold text-green-600 mt-1">Online</p>
      </div>
      <div class="bg-green-100 p-3 rounded-full text-green-500 text-2xl">✅</div>
    </div>
  </div>

  <div class="bg-white rounded-xl shadow overflow-hidden border border-gray-200">

    <div class="p-5 border-b border-gray-100 bg-gray-50 flex flex-col lg:flex-row justify-between items-center gap-4">
      <h3 class="text-lg font-bold text-primary whitespace-nowrap">📚 Database Buku</h3>

      <form class="flex flex-col md:flex-row gap-3 w-full lg:w-auto">

        <select name="category" onchange="this.form.submit()" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary outline-none text-sm bg-white cursor-pointer">
          <option value="">Semua Kategori</option>
          <?php foreach ($cats as $cat): ?>
            <option value="<?= $cat ?>" <?= $category_filter == $cat ? 'selected' : '' ?>><?= $cat ?></option>
          <?php endforeach; ?>
        </select>

        <div class="relative w-full lg:w-64">
          <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
            class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary transition text-sm"
            placeholder="Cari Judul / Penulis...">
          <button type="submit" class="absolute left-3 top-2.5 text-gray-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
          </button>
        </div>
      </form>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead class="bg-white text-gray-500 uppercase text-[10px] font-bold border-b tracking-wider">
          <tr>
            <th class="p-4 pl-6">Cover</th>
            <th class="p-4">Judul Buku</th>
            <th class="p-4">Kategori</th>
            <th class="p-4 text-center">Stok</th>
            <th class="p-4 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <?php if (count($books) > 0): ?>
            <?php foreach ($books as $b): ?>
              <tr class="hover:bg-blue-50 transition duration-150 group">
                <td class="p-4 pl-6">
                  <?php $img = !empty($b['image_url']) ? $b['image_url'] : "invalid"; ?>
                  <img src="<?= $img ?>" class="w-10 h-14 object-cover rounded shadow-sm border border-gray-200" onerror="imgError(this);">
                </td>

                <td class="p-4">
                  <div class="font-bold text-gray-800 text-sm line-clamp-1" title="<?= htmlspecialchars($b['title']) ?>">
                    <?= htmlspecialchars($b['title']) ?>
                  </div>
                  <div class="text-xs text-gray-500 mt-0.5">Penulis: <?= htmlspecialchars($b['author']) ?></div>
                  <div class="text-[10px] text-gray-400 font-mono mt-0.5">ISBN: <?= htmlspecialchars($b['isbn']) ?></div>
                </td>

                <td class="p-4">
                  <span class="bg-blue-50 text-blue-700 text-xs px-2 py-1 rounded-md font-semibold border border-blue-100">
                    <?= htmlspecialchars($b['category'] ?? '-') ?>
                  </span>
                </td>

                <td class="p-4 text-center">
                  <?php if ($b['stock'] > 0): ?>
                    <span class="text-green-600 font-bold bg-green-50 px-2 py-1 rounded text-xs border border-green-100"><?= $b['stock'] ?></span>
                  <?php else: ?>
                    <span class="text-red-600 font-bold bg-red-50 px-2 py-1 rounded text-xs border border-red-100">Habis</span>
                  <?php endif; ?>
                </td>

                <td class="p-4 text-center">
                  <div class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                    <a href="pages/book_form?id=<?= $b['id'] ?>" class="text-blue-500 hover:text-blue-700 bg-blue-50 p-1.5 rounded-lg hover:bg-blue-100 transition" title="Edit">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                      </svg>
                    </a>
                    <a href="actions/book_handler?delete=<?= $b['id'] ?>"
                      onclick="return confirm('Yakin ingin menghapus buku ini?')"
                      class="text-red-500 hover:text-red-700 bg-red-50 p-1.5 rounded-lg hover:bg-red-100 transition" title="Hapus">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                      </svg>
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" class="p-10 text-center text-gray-400 italic bg-gray-50">Tidak ada buku yang ditemukan.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php if ($total_matching_books > count($books)): ?>
      <div class="p-4 text-center border-t bg-gray-50">
        <a href="?search=<?= urlencode($search) ?>&category=<?= urlencode($category_filter) ?>&limit=<?= $next_limit ?>" class="text-primary font-bold text-sm hover:underline">
          Muat Lebih Banyak...
        </a>
      </div>
    <?php endif; ?>
  </div>

<?php else: ?>

  <div class="bg-gradient-to-r from-primary to-blue-800 rounded-3xl p-10 text-center text-white mb-10 shadow-2xl relative overflow-hidden">
    <div class="relative z-10">
      <h1 class="text-3xl md:text-5xl font-bold mb-4 tracking-tight">Temukan Jendela Duniamu</h1>
      <p class="text-blue-100 mb-8 text-lg font-light">Jelajahi ribuan koleksi buku digital eksklusif.</p>
      <form class="max-w-2xl mx-auto relative group">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="w-full py-4 pl-6 pr-14 rounded-full text-gray-800 focus:outline-none focus:ring-4 focus:ring-blue-400/50 shadow-xl text-lg transition" placeholder="Cari judul buku...">
        <button type="submit" class="absolute right-2 top-2 bottom-2 bg-primary text-white w-12 h-12 rounded-full flex items-center justify-center hover:bg-blue-900 transition shadow-md"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
          </svg></button>
      </form>
    </div>
    <div class="absolute top-0 left-0 w-64 h-64 bg-white opacity-5 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-white opacity-10 rounded-full blur-3xl translate-x-1/3 translate-y-1/3"></div>
  </div>

  <div class="flex justify-center gap-3 overflow-x-auto p-4 mb-4 scrollbar-hide -mx-4 md:mx-0">
    <a href="index" class="px-6 py-2 rounded-full whitespace-nowrap text-sm font-bold transition shadow-md hover:shadow-lg <?= $category_filter == '' ? 'bg-primary text-white ring-2 ring-offset-2 ring-primary' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' ?>">Semua</a>
    <?php foreach ($cats as $cat): if ($cat): ?>
        <a href="index?category=<?= urlencode($cat) ?>" class="px-6 py-2 rounded-full whitespace-nowrap text-sm font-bold transition shadow-md hover:shadow-lg <?= $category_filter == $cat ? 'bg-primary text-white ring-2 ring-offset-2 ring-primary' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' ?>"><?= htmlspecialchars($cat) ?></a>
    <?php endif;
    endforeach; ?>
  </div>

  <?php if (!empty($popular_books)): ?>
    <div class="mb-12 animate-fade-in">
      <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-2"><span class="text-2xl">🔥</span> Paling Populer</h2>
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
        <?php foreach ($popular_books as $pb): ?>
          <a href="pages/detail?id=<?= $pb['id'] ?>" class="bg-white rounded-xl shadow-sm hover:shadow-xl hover:-translate-y-1 transition duration-300 overflow-hidden group cursor-pointer border border-gray-100 flex flex-col h-full relative">
            <div class="h-48 bg-gray-200 relative overflow-hidden">
              <?php $img = !empty($pb['image_url']) ? $pb['image_url'] : "invalid"; ?>
              <img src="<?= htmlspecialchars($img) ?>" class="w-full h-full object-cover opacity-90 group-hover:scale-110 transition duration-500" onerror="imgError(this);">
              <div class="absolute top-2 right-2 bg-yellow-400 text-yellow-900 text-[10px] font-bold px-2 py-1 rounded shadow-md">★ <?= $pb['rating'] ?></div>
            </div>
            <div class="p-3 flex flex-col flex-grow">
              <h3 class="font-bold text-gray-800 text-sm leading-tight line-clamp-2 mb-1"><?= htmlspecialchars($pb['title']) ?></h3>
              <p class="text-xs text-gray-500 mt-auto truncate"><?= htmlspecialchars($pb['author']) ?></p>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <div class="animate-fade-in-up">
    <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-2">
      <?= $search ? '🔍 Hasil: "' . htmlspecialchars($search) . '"' : ($category_filter ? '📂 Kategori: ' . htmlspecialchars($category_filter) : '📚 Rekomendasi Untukmu') ?>
    </h2>

    <?php if (count($books) > 0): ?>
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
        <?php foreach ($books as $b): ?>
          <a href="pages/detail?id=<?= $b['id'] ?>" class="bg-white rounded-2xl shadow-sm hover:shadow-2xl hover:-translate-y-1 transition duration-300 border border-gray-100 flex flex-col h-full group relative overflow-hidden cursor-pointer">
            <div class="h-64 w-full bg-gray-100 relative overflow-hidden">
              <?php $imgUrl = !empty($b['image_url']) ? $b['image_url'] : "invalid"; ?>
              <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($b['title']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" onerror="imgError(this);">
              <span class="absolute top-3 left-3 bg-white/90 backdrop-blur-sm text-gray-800 text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wide shadow-sm border border-gray-100"><?= htmlspecialchars($b['category'] ?? 'General') ?></span>
            </div>
            <div class="p-4 flex flex-col flex-grow">
              <h3 class="font-bold text-gray-800 text-sm leading-snug mb-1 line-clamp-2 min-h-[40px]"><?= htmlspecialchars($b['title']) ?></h3>
              <p class="text-xs text-gray-500 mb-3 truncate">by <?= htmlspecialchars($b['author']) ?></p>
              <div class="mt-auto flex justify-between items-center border-t border-gray-50 pt-3">
                <div class="flex items-center text-yellow-500 text-xs font-bold gap-1"><span>⭐ <?= $b['rating'] ?></span></div>
                <div class="text-[10px] font-bold uppercase tracking-wider <?= $b['stock'] > 0 ? 'text-green-600 bg-green-50 px-2 py-1 rounded-md' : 'text-red-500 bg-red-50 px-2 py-1 rounded-md' ?>"><?= $b['stock'] > 0 ? $b['stock'] . ' Stok' : 'Habis' ?></div>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>

      <?php if ($total_matching_books > count($books)): ?>
        <div class="mt-12 text-center">
          <p class="text-gray-400 text-sm mb-4">Menampilkan <?= count($books) ?> dari <?= $total_matching_books ?> buku</p>
          <a href="?search=<?= urlencode($search) ?>&category=<?= urlencode($category_filter) ?>&limit=<?= $next_limit ?>" class="inline-block bg-white border border-gray-300 text-gray-600 px-8 py-3 rounded-full hover:bg-gray-50 transition shadow-sm font-medium text-sm">
            Muat Lebih Banyak...
          </a>
        </div>
      <?php endif; ?>

    <?php else: ?>
      <div class="text-center py-20 bg-gray-50 rounded-2xl border border-dashed border-gray-300">
        <div class="text-4xl mb-4">📚❌</div>
        <p class="text-gray-500">Ops, buku tidak ditemukan.</p><a href="index" class="bg-primary text-white px-6 py-2 rounded-lg font-bold shadow hover:bg-blue-800 transition mt-4 inline-block">Reset Filter</a>
      </div>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>