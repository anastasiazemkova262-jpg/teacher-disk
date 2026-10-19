<?php
require 'config.php';

// Проверка, что пользователь вошел в систему
if (!isset($_SESSION['user_id'])) { 
    header('Location: login.php');
    exit;
}

$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

// Если файл не был отправлен
if (!isset($_FILES['work'])) {
    die('Файл не выбран. <a href="index.php">Вернуться</a>');
}

$allowed = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'txt'];
$original = basename($_FILES['work']['name']);
$ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));

if (!in_array($ext, $allowed)) {
    die('Недопустимый тип файла. <a href="index.php">Вернуться</a>');
}

$safeName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $original);
$targetFile = $uploadDir . $safeName;

if (move_uploaded_file($_FILES['work']['tmp_name'], $targetFile)) {
    // Обратите внимание: мы убрали столбец 'type' из этого запроса!
    $stmt = $pdo->prepare("INSERT INTO files (user_id, filename, original_name) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $safeName, $original]);
    
    // После успешной загрузки возвращаем на главную
    header('Location: index.php');
    exit;
} else {
    echo "Ошибка загрузки файла. <a href='index.php'>Вернуться</a>";
}
?>