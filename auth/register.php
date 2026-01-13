<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - Itoshi Library</title>
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

    <div class="absolute top-0 right-0 w-40 h-40 bg-blue-50 rounded-bl-full -z-0 opacity-50"></div>

    <div class="p-10 relative z-10">
      <div class="text-center mb-6">
        <h2 class="text-3xl font-bold text-primary">Buat Akun Baru</h2>
        <p class="text-gray-500 text-sm mt-1">Daftar sebagai Mahasiswa untuk meminjam buku.</p>
      </div>

      <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 text-sm mb-4 rounded-r">
          <?= $_SESSION['error'];
          unset($_SESSION['error']); ?>
        </div>
      <?php endif; ?>
      <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 text-sm mb-4 rounded-r">
          <?= $_SESSION['success'];
          unset($_SESSION['success']); ?>
        </div>
      <?php endif; ?>

      <form action="../actions/register_handler" method="POST" class="space-y-4">
        <input type="hidden" name="role" value="student">

        <div>
          <label class="block text-gray-600 text-xs font-bold mb-1 uppercase tracking-wide ml-1">Username</label>
          <input type="text" name="username" class="w-full border border-gray-300 px-4 py-3 rounded-xl focus:ring-2 focus:ring-primary outline-none transition bg-gray-50 focus:bg-white" placeholder="Buat username unik" required>
        </div>

        <div>
          <label class="block text-gray-600 text-xs font-bold mb-1 uppercase tracking-wide ml-1">NIM (Nomor Induk Mahasiswa)</label>
          <input type="text" name="nim" class="w-full border border-gray-300 px-4 py-3 rounded-xl focus:ring-2 focus:ring-primary outline-none transition bg-gray-50 focus:bg-white" placeholder="Contoh: 2300018xxx" required>
        </div>

        <div>
          <label class="block text-gray-600 text-xs font-bold mb-1 uppercase tracking-wide ml-1">Password</label>
          <input type="password" name="password" class="w-full border border-gray-300 px-4 py-3 rounded-xl focus:ring-2 focus:ring-primary outline-none transition bg-gray-50 focus:bg-white" placeholder="Buat password kuat" required>
        </div>

        <button type="submit" class="w-full bg-primary text-white font-bold py-3.5 rounded-xl hover:bg-blue-800 transition shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 mt-2">
          Daftar Akun
        </button>
      </form>

      <div class="mt-6 text-center text-sm text-gray-500">
        Sudah punya akun? <a href="login" class="text-primary font-bold hover:underline">Login disini</a>
      </div>
    </div>
  </div>
</body>

</html>