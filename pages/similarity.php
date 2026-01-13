<?php
session_start();
require '../config/db.php';
require '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login");
  exit;
}

$isAdmin = ($_SESSION['role'] === 'admin');

// Logic Data Request
if ($isAdmin) {
  // Admin: Lihat Semua
  $requests = $pdo->query("SELECT * FROM similarity_requests ORDER BY created_at DESC")->fetchAll();
} else {
  // User: Lihat Punya Sendiri
  $stmt = $pdo->prepare("SELECT * FROM similarity_requests WHERE user_id = ? ORDER BY created_at DESC");
  $stmt->execute([$_SESSION['user_id']]);
  $my_requests = $stmt->fetchAll();
}
?>

<div class="container mx-auto max-w-6xl py-10 px-4">

  <?php if ($isAdmin): ?>

    <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100">
      <div class="p-8 border-b border-gray-100 bg-primary text-white flex justify-between items-center">
        <div>
          <h2 class="text-2xl font-bold">Daftar Request Similarity</h2>
          <p class="text-blue-200 text-sm">Kelola permintaan cek plagiasi mahasiswa.</p>
        </div>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <thead class="bg-gray-50 text-gray-500 uppercase text-xs font-bold border-b">
            <tr>
              <th class="p-5">Tanggal</th>
              <th class="p-5">Mahasiswa</th>
              <th class="p-5">Dokumen</th>
              <th class="p-5">Status</th>
              <th class="p-5 w-1/3">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <?php foreach ($requests as $req): ?>
              <tr class="hover:bg-blue-50 transition">
                <td class="p-5 text-sm text-gray-500"><?= date('d M Y H:i', strtotime($req['created_at'])) ?></td>
                <td class="p-5">
                  <div class="font-bold text-gray-800 text-sm"><?= htmlspecialchars($req['student_email']) ?></div>
                </td>
                <td class="p-5">
                  <div class="text-sm font-medium text-gray-700 mb-1"><?= htmlspecialchars($req['title']) ?></div>
                  <a href="<?= $req['file_path'] ?>" download class="text-xs bg-gray-200 hover:bg-gray-300 text-gray-700 px-2 py-1 rounded flex items-center gap-1 w-fit transition">⬇ Download</a>
                </td>
                <td class="p-5">
                  <?php if ($req['status'] == 'processed'): ?><span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold">Selesai</span><?php else: ?><span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-bold">Menunggu</span><?php endif; ?>
                </td>
                <td class="p-5">
                  <?php if ($req['status'] == 'processed'): ?>
                    <div class="text-xs text-gray-500">
                      <p class="mb-1">✅ Dibalas: <?= date('d M Y', strtotime($req['processed_at'])) ?></p><a href="<?= $req['reply_file_path'] ?>" download class="text-blue-600 hover:underline">Lihat Balasan</a>
                    </div>
                  <?php else: ?>
                    <form action="../actions/similarity_reply" method="POST" enctype="multipart/form-data" class="flex flex-col gap-2">
                      <input type="hidden" name="request_id" value="<?= $req['id'] ?>"><input type="hidden" name="student_email" value="<?= $req['student_email'] ?>">
                      <input type="file" name="reply_file" class="block w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required>
                      <button type="submit" class="bg-primary text-white px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-blue-800 transition shadow-sm">Kirim Hasil</button>
                    </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  <?php else: ?>

    <div class="flex flex-col gap-10">

      <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100 flex flex-col md:flex-row">
        <div class="md:w-5/12 bg-gradient-to-br from-primary to-blue-800 p-10 text-white flex flex-col justify-center relative overflow-hidden">
          <div class="relative z-10">
            <h2 class="text-3xl font-bold mb-4">Cek Similaritas</h2>
            <p class="text-blue-100 mb-6 leading-relaxed">Pastikan karya tulis Anda bebas plagiasi. Proses maksimal <strong>2x24 Jam Kerja</strong>.</p>
            <div class="bg-white/10 backdrop-blur-md p-4 rounded-xl border border-white/20">
              <h4 class="font-bold text-yellow-400 mb-1 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg> Estimasi
              </h4>
              <p class="text-xs">Hasil cek akan dikirim ke email & muncul di tabel riwayat.</p>
            </div>
          </div>
          <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-white opacity-10 rounded-full blur-3xl"></div>
        </div>

        <div class="md:w-7/12 p-10 relative">
          <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-50 text-green-700 p-4 rounded-xl mb-6 border border-green-200 flex items-center gap-3">
              <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
              <div><?= $_SESSION['success'];
                    unset($_SESSION['success']); ?></div>
            </div>
          <?php endif; ?>

          <form action="../actions/similarity_handler" method="POST" enctype="multipart/form-data">
            <div class="mb-5">
              <label class="block text-gray-700 font-bold mb-2 text-sm uppercase">Email Penerima</label>
              <input type="email" name="email" class="w-full pl-4 pr-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary transition" placeholder="email@uad.ac.id" required>
            </div>
            <div class="mb-5">
              <label class="block text-gray-700 font-bold mb-2 text-sm uppercase">Judul Dokumen</label>
              <input type="text" name="title" class="w-full pl-4 pr-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary transition" placeholder="Judul Skripsi" required>
            </div>
            <div class="mb-8">
              <label class="block text-gray-700 font-bold mb-2 text-sm uppercase">Upload File (PDF/DOCX)</label>
              <input type="file" name="document" class="block w-full text-sm text-gray-500 file:mr-4 file:py-3 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-gray-300 rounded-lg cursor-pointer" accept=".pdf,.docx,.doc" required>
            </div>
            <button class="w-full bg-primary text-white font-bold py-3 rounded-xl shadow-lg hover:bg-blue-800 transition transform hover:-translate-y-0.5">🚀 Kirim Dokumen</button>
          </form>
        </div>
      </div>

      <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100">
        <div class="p-8 border-b bg-gray-50">
          <h3 class="font-bold text-gray-800 text-xl flex items-center gap-2">
            📜 Riwayat Pengajuan Saya
          </h3>
        </div>
        <div class="overflow-x-auto">
          <?php if (count($my_requests) > 0): ?>
            <table class="w-full text-left">
              <thead class="bg-white text-gray-500 uppercase text-[10px] font-bold border-b">
                <tr>
                  <th class="p-6">Tanggal</th>
                  <th class="p-6">Judul</th>
                  <th class="p-6 text-center">Status</th>
                  <th class="p-6 text-center">Hasil</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50">
                <?php foreach ($my_requests as $req): ?>
                  <tr class="hover:bg-blue-50 transition">
                    <td class="p-6 text-sm text-gray-500 font-mono"><?= date('d M Y', strtotime($req['created_at'])) ?></td>
                    <td class="p-6 font-bold text-gray-800 text-sm"><?= htmlspecialchars($req['title']) ?></td>
                    <td class="p-6 text-center">
                      <?php if ($req['status'] == 'processed'): ?>
                        <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold border border-green-200">Selesai</span>
                      <?php else: ?>
                        <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-bold border border-yellow-200">Diproses</span>
                      <?php endif; ?>
                    </td>
                    <td class="p-6 text-center">
                      <?php if ($req['status'] == 'processed' && $req['reply_file_path']): ?>
                        <a href="<?= $req['reply_file_path'] ?>" download class="bg-blue-50 text-blue-600 px-4 py-2 rounded-lg font-bold text-xs hover:bg-blue-600 hover:text-white transition shadow-sm border border-blue-100">
                          ⬇ Download Hasil
                        </a>
                      <?php else: ?>
                        <span class="text-gray-400 text-xs italic">Belum tersedia</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php else: ?>
            <div class="text-center py-10 text-gray-400">Belum ada riwayat pengajuan.</div>
          <?php endif; ?>
        </div>
      </div>

    </div>
  <?php endif; ?>

</div>
<?php require '../includes/footer.php'; ?>