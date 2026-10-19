<?php
require 'config.php';
requireTeacher();
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['task'])) { header('Location: index.php'); exit; }
$title = trim($_POST['title'] ?? '');
$file = $_FILES['task'];
if ($title === '') die('Укажите название задания.');
if ($file['error'] !== UPLOAD_ERR_OK) die('Ошибка загрузки файла.');
if ($file['size'] > 50 * 1024 * 1024) die('Файл больше 50 МБ.');
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$storedName = uniqid('task_', true) . '.' . $ext;
$dest = __DIR__ . '/tasks/' . $storedName;
if (move_uploaded_file($file['tmp_name'], $dest)) {
    $stmt = $pdo->prepare("INSERT INTO tasks (teacher_id, title, original_name, stored_name) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $title, $file['name'], $storedName]);
    header('Location: index.php'); exit;
} else { die('Не удалось сохранить файл задания.'); }
