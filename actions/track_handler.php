<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: ../login");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user_id = $_SESSION['user_id'];
  $book_id = $_POST['book_id'];
  $type = $_POST['type']; // 'favorite' atau 'read'

  // Cek apakah data interaksi sudah ada
  $check = $pdo->prepare("SELECT id, is_favorite, is_read FROM user_interactions WHERE user_id = ? AND book_id = ?");
  $check->execute([$user_id, $book_id]);
  $data = $check->fetch(PDO::FETCH_ASSOC);

  if ($data) {
    // Jika data ada, kita TOGGLE
    if ($type === 'favorite') {
      $new_val = $data['is_favorite'] ? 0 : 1;
      $sql = "UPDATE user_interactions SET is_favorite = ? WHERE id = ?";
    } else {
      $new_val = $data['is_read'] ? 0 : 1;
      $sql = "UPDATE user_interactions SET is_read = ? WHERE id = ?";
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$new_val, $data['id']]);
  } else {
    // Jika data belum ada, Insert baru
    if ($type === 'favorite') {
      $sql = "INSERT INTO user_interactions (user_id, book_id, is_favorite) VALUES (?, ?, 1)";
    } else {
      $sql = "INSERT INTO user_interactions (user_id, book_id, is_read) VALUES (?, ?, 1)";
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $book_id]);
  }

  // Redirect kembali ke halaman detail buku
  header("Location: ../pages/detail?id=" . $book_id);
  exit;
}
