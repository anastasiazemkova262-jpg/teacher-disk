<?php
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    die('Доступ только для учителя. <a href="index.php">На главную</a>');
}

$link = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentName = trim($_POST['student_name']);
    if ($studentName !== '') {
        $baseUrl = "http://" . $_SERVER['HTTP_HOST'];
        $link = $baseUrl . "/register.php?invite=" . urlencode($studentName);
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8"><title>Создать доступ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
    <div class="container" style="max-width: 600px;">
        <h3>Генератор ссылок для учеников</h3>
        <p class="text-muted">Введите имя ученика, чтобы создать для него ссылку на регистрацию.</p>
        
        <form method="post" class="mb-4">
            <div class="input-group">
                <input type="text" name="student_name" class="form-control" placeholder="Например: Иванов Иван" required>
                <button class="btn btn-warning" type="submit">Создать ссылку</button>
            </div>
        </form>

        <?php if ($link): ?>
            <div class="alert alert-success">
                <strong>Ссылка готова!</strong> Скопируйте её и отправьте ученику:<br><br>
                <input type="text" class="form-control" value="<?= $link ?>" readonly onclick="this.select()">
            </div>
        <?php endif; ?>
        
        <a href="index.php" class="btn btn-outline-secondary mt-3">Вернуться на Диск</a>
    </div>
</body>
</html>