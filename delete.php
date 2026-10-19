<?php
require 'config.php';
requireLogin();
$type = $_GET['type'] ?? '';
$id = (int)($_GET['id'] ?? 0);
$isTeacher = ($_SESSION['role'] === 'teacher');
if ($type === 'file') {
    $stmt = $pdo->prepare("SELECT * FROM files WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    if (!$item) die('Файл не найден.');
    if (!$isTeacher && $item['user_id'] != $_SESSION['user_id']) die('Нет прав на удаление.');
    @unlink(__DIR__ . '/uploads/' . $item['stored_name']);
    $pdo->prepare("DELETE FROM files WHERE id = ?")->execute([$id]);
}
header('Location: index.php');
exit;
