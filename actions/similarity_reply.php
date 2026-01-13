<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../index");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $req_id = $_POST['request_id'];
  $student_email = $_POST['student_email'];

  // Upload Logic
  $target_dir = "../uploads/";
  $file_name = "REPLY_" . time() . "_" . basename($_FILES["reply_file"]["name"]);
  $target_file = $target_dir . $file_name;

  if (move_uploaded_file($_FILES["reply_file"]["tmp_name"], $target_file)) {

    // Update Database
    $sql = "UPDATE similarity_requests SET reply_file_path = ?, status = 'processed', processed_at = NOW() WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$target_file, $req_id]);

    $_SESSION['success'] = "File balasan berhasil dikirim!";
  } else {
    $_SESSION['error'] = "Gagal upload file balasan.";
  }

  header("Location: ../pages/similarity");
  exit;
}
