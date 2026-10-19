<?php
require 'config.php';

// Если не вошел — отправляем на страницу входа
if (!isset($_SESSION['user_id'])) { 
    header('Location: login.php');
    exit;
}

// Получаем ID файла из ссылки (например, download.php?id=1)
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM files WHERE id = ?");
$stmt->execute([$id]);
$file = $stmt->fetch(PDO::FETCH_ASSOC);

// Если файла нет в базе
if (!$file) { 
    die('Файл не найден в базе данных. <a href="index.php">Вернуться</a>'); 
}

// Ученик может скачать только свой файл, учитель — любой
if ($_SESSION['role'] !== 'teacher' && $file['user_id'] != $_SESSION['user_id']) {
    die('У вас нет прав на скачивание этого файла. <a href="index.php">Вернуться</a>');
}

// Путь к файлу на диске
$path = __DIR__ . '/uploads/' . $file['filename'];

if (!file_exists($path)) { 
    die('Файл отсутствует на диске. <a href="index.php">Вернуться</a>'); 
}

// Отдаем файл браузеру для скачивания
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($file['original_name']) . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
exit;
?>
