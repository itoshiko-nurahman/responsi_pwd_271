<?php
session_start();
require '../config/db.php';

if (!defined('BASE_URL')) {
  define('BASE_URL', '../');
}

if (isset($_SESSION['user_id'])) {
  header("Location: " . BASE_URL . "index");
  exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Itoshi Library</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

  <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><rect width=%22100%22 height=%22100%22 rx=%2220%22 fill=%22%232C5074%22/><text x=%2250%22 y=%2270%22 font-family=%22Arial, sans-serif%22 font-size=%2255%22 font-weight=%22bold%22 fill=%22white%22 text-anchor=%22middle%22>IL</text></svg>" type="image/svg+xml">

  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['Poppins', 'sans-serif']
          },
          colors: {
            primary: '#2C5074'
          }
        }
      }
    }
  </script>
</head>

<body class="bg-gradient-to-br from-gray-900 to-[#2C5074] min-h-screen flex items-center justify-center p-4 text-gray-800">

  <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden relative">

    <div class="absolute top-0 left-0 w-32 h-32 bg-blue-50 rounded-br-full -z-0 opacity-50"></div>
    <div class="absolute bottom-0 right-0 w-32 h-32 bg-blue-50 rounded-tl-full -z-0 opacity-50"></div>

    <div class="p-10 relative z-10">
      <div class="text-center mb-8">
        <div class="w-16 h-16 bg-primary text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg transform rotate-3">
          IL
        </div>
        <h2 class="text-3xl font-bold text-gray-800">Selamat Datang</h2>
        <p class="text-gray-500 text-sm mt-1">Silakan login untuk mengakses perpustakaan.</p>
      </div>

      <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r text-sm flex items-center gap-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <?= $_SESSION['error'];
          unset($_SESSION['error']); ?>
        </div>
      <?php endif; ?>

      <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-r text-sm flex items-center gap-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
          <?= $_SESSION['success'];
          unset($_SESSION['success']); ?>
        </div>
      <?php endif; ?>

      <form action="../actions/login_handler" method="POST" class="space-y-5">

        <div>
          <label class="block text-gray-600 text-xs font-bold mb-1 uppercase tracking-wide ml-1">Username</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
              </svg>
            </span>
            <input type="text" name="username" class="w-full border border-gray-300 pl-10 pr-4 py-3 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary outline-none transition bg-gray-50 focus:bg-white" placeholder="Masukkan username" required>
          </div>
        </div>

        <div>
          <label class="block text-gray-600 text-xs font-bold mb-1 uppercase tracking-wide ml-1">Password</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
              </svg>
            </span>
            <input type="password" name="password" class="w-full border border-gray-300 pl-10 pr-4 py-3 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary outline-none transition bg-gray-50 focus:bg-white" placeholder="••••••••" required>
          </div>
        </div>

        <button type="submit" class="w-full bg-primary text-white font-bold py-3.5 rounded-xl hover:bg-blue-800 transition shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex justify-center items-center gap-2">
          Masuk Sekarang
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
          </svg>
        </button>
      </form>

      <div class="mt-8 text-center text-sm text-gray-500">
        Belum punya akun?
        <a href="register" class="text-primary font-bold hover:underline transition">Daftar Mahasiswa</a>
      </div>
    </div>
  </div>
</body>

</html>