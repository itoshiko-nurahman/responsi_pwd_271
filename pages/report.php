<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../index");
  exit;
}

// Jenis Laporan yang diminta
$type = $_GET['type'] ?? null;

if ($type) {
  $data = [];
  $title_report = "";

  if ($type == 'books') {
    $title_report = "Laporan Koleksi Buku";
    $data = $pdo->query("SELECT * FROM books ORDER BY title ASC")->fetchAll();
  } elseif ($type == 'loans') {
    $title_report = "Laporan Transaksi Peminjaman";
    $data = $pdo->query("SELECT loans.*, books.title as book_title FROM loans JOIN books ON loans.book_id = books.id ORDER BY loan_date DESC")->fetchAll();
  } elseif ($type == 'similarity') {
    $title_report = "Laporan Cek Similarity";
    $data = $pdo->query("SELECT * FROM similarity_requests ORDER BY created_at DESC")->fetchAll();
  } else {
    header("Location: report");
    exit;
  }
?>
  <!DOCTYPE html>
  <html lang="id">

  <head>
    <meta charset="UTF-8">
    <title>Cetak <?= $title_report ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
      @media print {
        @page {
          margin: 1cm;
          size: A4;
        }

        body {
          background: white;
          -webkit-print-color-adjust: exact;
          print-color-adjust: exact;
        }

        .no-print {
          display: none !important;
        }
      }
    </style>
  </head>

  <body class="bg-gray-100 text-gray-800 font-sans p-8" onload="window.print()">

    <a href="report" class="no-print fixed top-5 left-5 bg-gray-800 text-white px-4 py-2 rounded-lg font-bold shadow-lg hover:bg-black transition">
      ← Kembali ke Menu
    </a>

    <div class="max-w-4xl mx-auto bg-white p-10 shadow-none print:shadow-none print:p-0">
      <div class="border-b-4 border-gray-800 pb-4 mb-8 text-center">
        <h1 class="text-3xl font-bold uppercase tracking-wider">Itoshi Library System</h1>
        <p class="text-sm text-gray-500 mt-1">Universitas Ahmad Dahlan, Yogyakarta</p>
        <p class="text-xs text-gray-400">Jl. Ringroad Selatan, Tamanan, Banguntapan, Bantul, DIY</p>
      </div>

      <div class="flex justify-between items-end mb-6">
        <div>
          <h2 class="text-xl font-bold uppercase"><?= $title_report ?></h2>
          <p class="text-sm text-gray-500">Dicetak pada: <?= date('d F Y, H:i') ?></p>
        </div>
        <div class="text-right">
          <p class="text-sm font-bold">Total Data: <?= count($data) ?></p>
        </div>
      </div>

      <table class="w-full text-left border-collapse border border-gray-300 text-sm">
        <thead>
          <tr class="bg-gray-100 text-gray-700">
            <th class="border border-gray-300 px-3 py-2 text-center w-10">No</th>

            <?php if ($type == 'books'): ?>
              <th class="border border-gray-300 px-3 py-2">Judul Buku</th>
              <th class="border border-gray-300 px-3 py-2">Penulis</th>
              <th class="border border-gray-300 px-3 py-2">Kategori</th>
              <th class="border border-gray-300 px-3 py-2 text-center">Stok</th>

            <?php elseif ($type == 'loans'): ?>
              <th class="border border-gray-300 px-3 py-2">Peminjam (NIM)</th>
              <th class="border border-gray-300 px-3 py-2">Judul Buku</th>
              <th class="border border-gray-300 px-3 py-2">Tgl Pinjam</th>
              <th class="border border-gray-300 px-3 py-2">Tgl Kembali</th>
              <th class="border border-gray-300 px-3 py-2 text-center">Status</th>

            <?php elseif ($type == 'similarity'): ?>
              <th class="border border-gray-300 px-3 py-2">Tanggal Request</th>
              <th class="border border-gray-300 px-3 py-2">Email Mahasiswa</th>
              <th class="border border-gray-300 px-3 py-2">Judul Dokumen</th>
              <th class="border border-gray-300 px-3 py-2 text-center">Status</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php $no = 1;
          foreach ($data as $row): ?>
            <tr class="border border-gray-300">
              <td class="border border-gray-300 px-3 py-2 text-center"><?= $no++ ?></td>

              <?php if ($type == 'books'): ?>
                <td class="border border-gray-300 px-3 py-2 font-bold"><?= htmlspecialchars($row['title']) ?></td>
                <td class="border border-gray-300 px-3 py-2"><?= htmlspecialchars($row['author']) ?></td>
                <td class="border border-gray-300 px-3 py-2"><?= htmlspecialchars($row['category']) ?></td>
                <td class="border border-gray-300 px-3 py-2 text-center"><?= $row['stock'] ?></td>

              <?php elseif ($type == 'loans'): ?>
                <td class="border border-gray-300 px-3 py-2">
                  <strong><?= htmlspecialchars($row['student_name']) ?></strong><br>
                  <span class="text-xs text-gray-500"><?= htmlspecialchars($row['nim']) ?></span>
                </td>
                <td class="border border-gray-300 px-3 py-2"><?= htmlspecialchars($row['book_title']) ?></td>
                <td class="border border-gray-300 px-3 py-2"><?= date('d/m/Y', strtotime($row['loan_date'])) ?></td>

                <td class="border border-gray-300 px-3 py-2">
                  <?= $row['return_date'] ? date('d/m/Y', strtotime($row['return_date'])) : '-' ?>
                </td>

                <td class="border border-gray-300 px-3 py-2 text-center">
                  <?= $row['status'] == 'borrowed' ? 'Dipinjam' : 'Dikembalikan' ?>
                </td>

              <?php elseif ($type == 'similarity'): ?>
                <td class="border border-gray-300 px-3 py-2"><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
                <td class="border border-gray-300 px-3 py-2"><?= htmlspecialchars($row['student_email']) ?></td>
                <td class="border border-gray-300 px-3 py-2"><?= htmlspecialchars($row['title']) ?></td>
                <td class="border border-gray-300 px-3 py-2 text-center">
                  <?= $row['status'] == 'processed' ? 'Selesai' : 'Menunggu' ?>
                </td>
              <?php endif; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div class="mt-16 flex justify-end">
        <div class="text-center">
          <p class="mb-20">Yogyakarta, <?= date('d F Y') ?><br>Kepala Perpustakaan</p>
          <p class="font-bold underline">Itoshiko Nurahman</p>
          <p class="text-xs">NIM. 2300018271</p>
        </div>
      </div>
    </div>
  </body>

  </html>
<?php
  exit;
}

require '../includes/header.php';
?>

<div class="container mx-auto max-w-5xl py-12 px-4">

  <div class="text-center mb-12">
    <h1 class="text-4xl font-bold text-primary mb-3">Pusat Laporan</h1>
    <p class="text-gray-500">Pilih jenis laporan yang ingin Anda cetak atau ekspor.</p>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

    <a href="?type=books" target="_blank" class="group bg-white p-8 rounded-3xl shadow-lg border border-gray-100 hover:shadow-2xl hover:-translate-y-2 transition duration-300 flex flex-col items-center text-center">
      <div class="w-20 h-20 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mb-6 group-hover:bg-blue-600 group-hover:text-white transition">
        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
        </svg>
      </div>
      <h3 class="text-xl font-bold text-gray-800 mb-2">Laporan Buku</h3>
      <p class="text-sm text-gray-500 mb-6">Data seluruh koleksi buku, kategori, dan status stok saat ini.</p>
      <span class="text-blue-600 font-bold text-sm group-hover:underline">Cetak Laporan →</span>
    </a>

    <a href="?type=loans" target="_blank" class="group bg-white p-8 rounded-3xl shadow-lg border border-gray-100 hover:shadow-2xl hover:-translate-y-2 transition duration-300 flex flex-col items-center text-center">
      <div class="w-20 h-20 bg-amber-50 text-amber-600 rounded-full flex items-center justify-center mb-6 group-hover:bg-amber-600 group-hover:text-white transition">
        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
        </svg>
      </div>
      <h3 class="text-xl font-bold text-gray-800 mb-2">Laporan Peminjaman</h3>
      <p class="text-sm text-gray-500 mb-6">Rekap transaksi peminjaman dan pengembalian oleh mahasiswa.</p>
      <span class="text-amber-600 font-bold text-sm group-hover:underline">Cetak Laporan →</span>
    </a>

    <a href="?type=similarity" target="_blank" class="group bg-white p-8 rounded-3xl shadow-lg border border-gray-100 hover:shadow-2xl hover:-translate-y-2 transition duration-300 flex flex-col items-center text-center">
      <div class="w-20 h-20 bg-green-50 text-green-600 rounded-full flex items-center justify-center mb-6 group-hover:bg-green-600 group-hover:text-white transition">
        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
      </div>
      <h3 class="text-xl font-bold text-gray-800 mb-2">Laporan Similarity</h3>
      <p class="text-sm text-gray-500 mb-6">Data permintaan cek plagiasi (Turnitin) yang masuk dan diproses.</p>
      <span class="text-green-600 font-bold text-sm group-hover:underline">Cetak Laporan →</span>
    </a>

  </div>

  <div class="mt-12 text-center">
    <a href="../index" class="text-gray-400 hover:text-primary transition text-sm">← Kembali ke Dashboard Admin</a>
  </div>

</div>

<?php require '../includes/footer.php'; ?>