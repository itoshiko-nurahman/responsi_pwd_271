<?php
// Pastikan session dimulai
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// GANTI 'RESPONSI_PWD_271' sesuai nama folder project Anda di htdocs jika perlu
if (!defined('BASE_URL')) {
  define('BASE_URL', 'http://localhost/RESPONSI_PWD_271/');
}

// Logic ambil data user (hanya jika $pdo tersedia dari config/db.php)
$user_logged = null;
if (isset($_SESSION['user_id']) && isset($pdo)) {
  $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
  $stmt->execute([$_SESSION['user_id']]);
  $user_logged = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Itoshi Library</title>

  <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><rect width=%22100%22 height=%22100%22 rx=%2220%22 fill=%22%232C5074%22/><text x=%2250%22 y=%2270%22 font-family=%22Arial, sans-serif%22 font-size=%2255%22 font-weight=%22bold%22 fill=%22white%22 text-anchor=%22middle%22>IL</text></svg>" type="image/svg+xml">

  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['Poppins', 'sans-serif']
          },
          colors: {
            primary: '#2C5074',
            secondary: '#4A7C9D',
            accent: '#EAB308'
          }
        }
      }
    }
  </script>
  <style>
    .scrollbar-hide::-webkit-scrollbar {
      display: none;
    }

    .scrollbar-hide {
      -ms-overflow-style: none;
      scrollbar-width: none;
    }

    #mobile-menu {
      transition: max-height 0.3s ease-in-out;
    }
  </style>
</head>

<body class="bg-[#F3F4F6] flex flex-col min-h-screen text-gray-800 font-sans">

  <?php if (isset($_SESSION['user_id'])): ?>
    <nav class="bg-primary text-white shadow-lg sticky top-0 z-50">
      <div class="container mx-auto px-6 py-4">
        <div class="flex justify-between items-center">

          <a href="<?= BASE_URL ?>index" class="flex items-center gap-3 group">
            <div class="bg-white text-primary p-2 rounded-lg font-bold text-xl h-10 w-10 flex items-center justify-center group-hover:rotate-12 transition shadow-md">
              IL
            </div>
            <div>
              <h1 class="text-xl font-bold tracking-wide">Itoshi Library</h1>
              <p class="text-[10px] text-blue-200 uppercase tracking-widest hidden sm:block">Campus Digital System</p>
            </div>
          </a>

          <div class="hidden md:flex items-center gap-6 text-sm font-medium ml-auto">

            <a href="<?= BASE_URL ?>index" class="hover:text-accent transition flex items-center gap-1">🏠 Dashboard</a>

            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
              <a href="<?= BASE_URL ?>pages/book_form" class="hover:text-accent transition">Tambah Buku</a>
            <?php endif; ?>

            <a href="<?= BASE_URL ?>pages/similarity" class="hover:text-accent transition">Cek Similarity</a>
            <a href="<?= BASE_URL ?>pages/loans" class="hover:text-accent transition">Peminjaman</a>
            <a href="<?= BASE_URL ?>pages/profile" class="flex items-center gap-2 hover:text-accent transition bg-blue-800 px-4 py-2 rounded-full border border-blue-400">
              <div class="w-6 h-6 bg-white text-primary rounded-full flex items-center justify-center font-bold text-xs">
                <?= substr($_SESSION['username'], 0, 1) ?>
              </div>
              <span>Profil Saya</span>
            </a>
          </div>

          <button id="menu-btn" class="md:hidden text-white focus:outline-none">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
            </svg>
          </button>
        </div>

        <div id="mobile-menu" class="hidden md:hidden mt-4 bg-blue-900 rounded-xl p-4 space-y-3 shadow-inner border-t border-blue-800">
          <a href="<?= BASE_URL ?>index" class="block py-2 px-4 rounded hover:bg-primary transition">🏠 Dashboard</a>

          <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="<?= BASE_URL ?>pages/book_form" class="block py-2 px-4 rounded hover:bg-primary transition">Tambah Buku</a>
          <?php endif; ?>

          <a href="<?= BASE_URL ?>pages/similarity" class="block py-2 px-4 rounded hover:bg-primary transition">Cek Similarity</a>
          <a href="<?= BASE_URL ?>pages/loans" class="block py-2 px-4 rounded hover:bg-primary transition">Peminjaman</a>
          <a href="<?= BASE_URL ?>pages/profile" class="block py-2 px-4 rounded bg-blue-800 hover:bg-primary transition border border-blue-600 font-bold">👤 Profil & Logout</a>
        </div>
      </div>
    </nav>

    <script>
      const btn = document.getElementById('menu-btn');
      const menu = document.getElementById('mobile-menu');
      btn.addEventListener('click', () => {
        menu.classList.toggle('hidden');
      });
    </script>
  <?php endif; ?>

  <main class="flex-grow container mx-auto px-4 md:px-6 py-6 md:py-8">