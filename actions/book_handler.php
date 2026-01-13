<?php
// actions/book_handler.php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: ../login");
  exit;
}

//Cek Role Admin
if ($_SESSION['role'] !== 'admin') {
  die("Akses Ditolak: Anda bukan Admin.");
}

// DELETE
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  $pdo->prepare("DELETE FROM books WHERE id = ?")->execute([$id]);
  header("Location: ../index");
  exit;
}

// CREATE / UPDATE
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $id = $_POST['id'];
  $title = $_POST['title'];
  $author = $_POST['author'];
  $isbn = $_POST['isbn'];
  $rating = $_POST['rating'];
  $pages = $_POST['pages'];
  $lang = $_POST['language'];
  $stock = $_POST['stock'];

  if ($id) {
    $sql = "UPDATE books SET title=?, author=?, isbn=?, rating=?, pages=?, language=?, stock=? WHERE id=?";
    $pdo->prepare($sql)->execute([$title, $author, $isbn, $rating, $pages, $lang, $stock, $id]);
  } else {
    $sql = "INSERT INTO books (title, author, isbn, rating, pages, language, stock) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $pdo->prepare($sql)->execute([$title, $author, $isbn, $rating, $pages, $lang, $stock]);
  }
  header("Location: ../index");
  exit;
}
