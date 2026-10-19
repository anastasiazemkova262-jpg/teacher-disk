<?php
require 'config.php';

$inviteName = $_GET['invite'] ?? '';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($username === '' || $password === '') {
        $error = 'Заполните все поля.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, 'student')");
            $stmt->execute([$username, $hash]);
            $success = 'Регистрация успешна! Теперь вы можете <a href="login.php">войти</a>.';
        } catch (PDOException $e) {
            $error = 'Этот логин уже занят.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head><meta charset="UTF-8"><title>Регистрация</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">
  <div class="card p-4 shadow-sm" style="width: 350px;">
    <h3 class="text-center mb-3">Регистрация ученика</h3>
    <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>
    
    <form method="post">
      <div class="mb-3">
          <label>Ваше имя (Фамилия)</label>
          <input name="username" class="form-control" value="<?= htmlspecialchars($inviteName) ?>" required>
      </div>
      <div class="mb-3">
          <label>Придумайте пароль</label>
          <input name="password" type="password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-warning w-100">Зарегистрироваться</button>
    </form>
  </div>
</body>
</html>