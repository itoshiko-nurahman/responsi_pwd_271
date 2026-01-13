<?php
require '../config/db.php';

// Setting agar proses import tidak timeout (karena data banyak)
set_time_limit(300);

$csv_file = '../assets/books.csv';

if (!file_exists($csv_file)) {
  die("File books.csv tidak ditemukan di folder ini! Silakan upload dulu.");
}

echo "<h1>Sedang memproses import data...</h1>";
echo "<p>Mohon tunggu, jangan tutup halaman ini.</p>";

// Kategori untuk di-random
$categories = ['Fiction', 'Science', 'Technology', 'Business', 'Philosophy', 'History', 'Art', 'Biography', 'Fantasy', 'Romance'];

// Buka file CSV
if (($handle = fopen($csv_file, "r")) !== FALSE) {

  // Kosongkan tabel buku dulu (Reset)
  $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
  $pdo->exec("TRUNCATE TABLE books");
  $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

  // Skip baris header (judul kolom)
  fgetcsv($handle, 1000, ",");

  $count = 0;
  $max_import = 1000; // Batas 1000 buku

  $sql = "INSERT INTO books (title, author, rating, isbn, language, pages, stock, category, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
  $stmt = $pdo->prepare($sql);

  while (($data = fgetcsv($handle, 1000, ",")) !== FALSE && $count < $max_import) {
    // Mapping kolom sesuai struktur books.csv
    // [1] title, [2] authors, [3] rating, [4] isbn, [6] lang, [7] pages

    $title = $data[1];
    $author = $data[2];
    $rating = (float)$data[3];
    $isbn = $data[4];
    $language = $data[6];
    $pages = (int)$data[7];

    // Data Generate (Random)
    $stock = rand(0, 20); // Stok 0-20
    $category = $categories[array_rand($categories)]; // Kategori acak
    $image_url = "https://covers.openlibrary.org/b/isbn/" . $isbn . "-M.jpg"; // Cover asli dari API

    try {
      $stmt->execute([$title, $author, $rating, $isbn, $language, $pages, $stock, $category, $image_url]);
      $count++;
    } catch (Exception $e) {
      // Lanjut jika ada error (misal karakter aneh)
      continue;
    }
  }

  fclose($handle);

  echo "<h2>SUKSES!</h2>";
  echo "<p>Berhasil mengimport <b>$count</b> buku dari books.csv</p>";
  echo "<a href='index'>Kembali ke Dashboard</a>";
} else {
  echo "Gagal membaca file CSV.";
}
