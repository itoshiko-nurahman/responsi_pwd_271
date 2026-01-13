<?php
session_start();
require '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $action = $_POST['action'];

  if ($action == 'borrow') {
    $book_id = $_POST['book_id'];
    $name = $_POST['student_name'];
    $nim = $_POST['nim'];
    $date = $_POST['loan_date'];

    // Cek Stok
    $stmt = $pdo->prepare("SELECT stock FROM books WHERE id = ?");
    $stmt->execute([$book_id]);
    $book = $stmt->fetch();

    if ($book['stock'] > 0) {
      $pdo->prepare("UPDATE books SET stock = stock - 1 WHERE id = ?")->execute([$book_id]);
      $pdo->prepare("INSERT INTO loans (book_id, student_name, nim, loan_date, status) VALUES (?, ?, ?, ?, 'borrowed')")
        ->execute([$book_id, $name, $nim, $date]);
    }
  } elseif ($action == 'return') {
    $loan_id = $_POST['loan_id'];
    $book_id = $_POST['book_id'];
    $ret_date = date('Y-m-d');

    $pdo->prepare("UPDATE loans SET status = 'returned', return_date = ? WHERE id = ?")->execute([$ret_date, $loan_id]);
    $pdo->prepare("UPDATE books SET stock = stock + 1 WHERE id = ?")->execute([$book_id]);
  }
}
header("Location: ../pages/loans");
exit;
