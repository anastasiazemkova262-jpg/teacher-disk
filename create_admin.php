<?php
require 'config.php';

// Данные для входа
$username = 'teacher';
$password = 'admin123';

// Хешируем пароль
$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    // Пытаемся добавить учителя
    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, 'teacher')");
    $stmt->execute([$username, $hash]);
    echo "<h3>Учитель успешно создан!</h3>";
    echo "<p>Логин: <b>teacher</b><br>Пароль: <b>admin123</b></p>";
    echo "<a href='login.php'>Перейти на страницу входа</a>";
} catch (PDOException $e) {
    // Если учитель уже есть, принудительно меняем ему пароль
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, role = 'teacher' WHERE username = ?");
    $stmt->execute([$hash, $username]);
    echo "<h3>Учитель уже существует. Пароль сброшен!</h3>";
    echo "<p>Логин: <b>teacher</b><br>Пароль: <b>admin123</b></p>";
    echo "<a href='login.php'>Перейти на страницу входа</a>";
}
?>