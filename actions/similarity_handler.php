<?php
session_start();
require '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $email = htmlspecialchars($_POST['email']);
  $title = htmlspecialchars($_POST['title']);
  $user_id = $_SESSION['user_id'] ?? null;

  // Upload Logic
  $target_dir = "../uploads/";
  if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
  }

  $file_name = time() . "_" . basename($_FILES["document"]["name"]);
  $target_file = $target_dir . $file_name;
  $ext = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

  if (!in_array($ext, ['pdf', 'doc', 'docx'])) {
    $_SESSION['error'] = "Format file harus PDF atau DOCX.";
    header("Location: ../pages/similarity");
    exit;
  }

  if ($_FILES["document"]["size"] > 10000000) {
    $_SESSION['error'] = "Ukuran file terlalu besar (Maks 10MB).";
    header("Location: ../pages/similarity");
    exit;
  }

  if (move_uploaded_file($_FILES["document"]["tmp_name"], $target_file)) {

    // Log DB (Update: Masukkan user_id)
    $stmt = $pdo->prepare("INSERT INTO similarity_requests (user_id, student_email, title, file_path) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $email, $title, $target_file]);

    // Email Logic
    $to = "2300018271@webmail.uad.ac.id";
    $subject = "Request Cek Similarity: " . $title;

    $_SESSION['success'] = "Dokumen berhasil dikirim! Mohon tunggu 2x24 Jam Kerja. Cek status di tabel riwayat.";
  } else {
    $_SESSION['error'] = "Gagal mengupload file ke server.";
  }

  header("Location: ../pages/similarity");
  exit;
}
