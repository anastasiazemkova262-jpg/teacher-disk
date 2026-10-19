<?php require 'config.php'; ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Классный Диск</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f5f5f5; font-family: 'Segoe UI', sans-serif; }
        .sidebar { width: 250px; background: white; height: 100vh; position: fixed; padding: 20px; border-right: 1px solid #e0e0e0; }
        .sidebar .nav-link { color: #333; font-weight: 500; border-radius: 8px; margin-bottom: 5px; padding: 10px 15px; text-decoration: none; display: block; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: #f0f0f0; }
        .main-content { margin-left: 250px; padding: 30px; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn-yandex { background-color: #ffdb4d; color: #000; font-weight: 600; border: none; border-radius: 8px; padding: 10px 20px; }
        .btn-yandex:hover { background-color: #ffcc00; }
        
        /* Стили для папок */
        .folder-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; }
        .folder-card { background: white; border-radius: 12px; padding: 20px; cursor: pointer; transition: 0.2s; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: block; text-decoration: none; color: black;}
        .folder-card:hover { transform: translateY(-3px); box-shadow: 0 4px 10px rgba(0,0,0,0.1); color: black;}
        .folder-icon { font-size: 50px; color: #ffdb4d; margin-bottom: 10px; }
        
        /* Стили для файлов */
        .file-card { background: white; border-radius: 12px; padding: 15px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .file-card .icon { font-size: 40px; color: #ffdb4d; margin-bottom: 10px; }
        .file-card .filename { font-size: 14px; font-weight: 500; color: #333; text-decoration: none; display: block; word-break: break-all; }
        
        .user-profile { display: flex; align-items: center; gap: 10px; }
        .avatar { width: 35px; height: 35px; background: #ffdb4d; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; }
    </style>
</head>
<body>

<?php if (!isset($_SESSION['user_id'])): ?>
    <!-- Экран приветствия, если не вошел -->
    <div class="d-flex justify-content-center align-items-center vh-100">
        <div class="text-center">
            <h2>Добро пожаловать в Классный Диск</h2>
            <a href="login.php" class="btn btn-yandex mt-3">Войти</a>
        </div>
    </div>
<?php else: ?>
    <!-- ЛЕВОЕ МЕНЮ -->
    <div class="sidebar">
        <div class="d-flex align-items-center mb-4">
            <i class="fa-solid fa-hard-drive fa-2x text-warning me-2"></i>
            <h4 class="mb-0 fw-bold">Диск</h4>
        </div>
        
        <form action="upload.php" method="post" enctype="multipart/form-data" class="mb-4">
            <label class="btn btn-yandex w-100 mb-2 cursor-pointer">
                <i class="fa-solid fa-arrow-up-from-bracket"></i> Загрузить
                <input type="file" name="work" required style="display: none;" onchange="this.form.submit()">
            </label>
        </form>

        <nav class="nav flex-column">
            <a class="nav-link active" href="index.php"><i class="fa-solid fa-folder-open"></i> Все папки</a>
            <?php if ($_SESSION['role'] === 'teacher'): ?>
                <a class="nav-link" href="generate_link.php"><i class="fa-solid fa-link"></i> Создать доступ</a>
            <?php endif; ?>
        </nav>
    </div>

    <!-- ОСНОВНОЙ КОНТЕНТ -->
    <div class="main-content">
        <div class="top-bar">
            <h4 class="fw-bold">
                <?= isset($_GET['folder']) ? 'Папка: ' . htmlspecialchars($_GET['folder']) : 'Все папки' ?>
            </h4>
            <div class="user-profile">
                <div class="avatar"><?= mb_strtoupper(mb_substr($_SESSION['username'], 0, 1)) ?></div>
                <div>
                    <div class="fw-bold"><?= htmlspecialchars($_SESSION['username']) ?></div>
                    <div class="text-muted" style="font-size: 12px;">
                        <?= $_SESSION['role'] === 'teacher' ? 'Учитель' : 'Ученик' ?> | 
                        <a href="logout.php" class="text-decoration-none text-danger">Выйти</a>
                    </div>
                </div>
            </div>
        </div>

        <div id="content-area">
            <?php
            // Если в ссылке есть ?folder=Имя, значит мы зашли внутрь папки
            if (isset($_GET['folder'])) {
                $folderName = $_GET['folder'];
                
                // Кнопка "Назад"
                echo '<a href="index.php" class="btn btn-sm btn-outline-secondary mb-4"><i class="fa-solid fa-arrow-left"></i> Назад ко всем папкам</a>';

                // Достаем файлы ученика
                $stmt = $pdo->prepare("
                    SELECT f.id, f.original_name 
                    FROM files f 
                    JOIN users u ON f.user_id = u.id 
                    WHERE u.username = ?
                    ORDER BY f.uploaded_at DESC
                ");
                $stmt->execute([$folderName]);
                $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (!$files) {
                    echo '<p class="text-muted">В этой папке пока нет загруженных работ.</p>';
                } else {
                    echo '<div class="row g-3">';
                    foreach ($files as $file) {
                        // Выбираем иконку в зависимости от формата
                        $icon = 'fa-file';
                        if (str_ends_with($file['original_name'], '.pdf')) $icon = 'fa-file-pdf text-danger';
                        elseif (preg_match('/\.(doc|docx)$/', $file['original_name'])) $icon = 'fa-file-word text-primary';
                        elseif (preg_match('/\.(jpg|jpeg|png)$/', $file['original_name'])) $icon = 'fa-file-image text-success';

                        echo '
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="file-card">
                                <i class="fa-solid ' . $icon . ' icon"></i>
                                <a href="download.php?id=' . $file['id'] . '" class="filename" title="' . htmlspecialchars($file['original_name']) . '">' . htmlspecialchars($file['original_name']) . '</a>
                            </div>
                        </div>';
                    }
                    echo '</div>';
                }
            } else {
                // Если мы на главной странице, показываем СПИСОК ПАПОК
                if ($_SESSION['role'] === 'teacher') {
                    // Учитель видит всех учеников
                    $stmt = $pdo->query("SELECT username FROM users WHERE role = 'student' ORDER BY username ASC");
                } else {
                    // Ученик видит только себя
                    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                }
                $folders = $stmt->fetchAll(PDO::FETCH_COLUMN);

                if (!$folders) {
                    echo '<p class="text-muted">Учеников пока нет.</p>';
                } else {
                    echo '<div class="folder-grid">';
                    foreach ($folders as $fName) {
                        // Считаем количество файлов в папке ученика
                        $countStmt = $pdo->prepare("
                            SELECT COUNT(*) FROM files f 
                            JOIN users u ON f.user_id = u.id 
                            WHERE u.username = ?
                        ");
                        $countStmt->execute([$fName]);
                        $fileCount = $countStmt->fetchColumn();

                        echo '
                        <a href="index.php?folder=' . urlencode($fName) . '" class="folder-card">
                            <i class="fa-solid fa-folder folder-icon"></i>
                            <div class="fw-bold">' . htmlspecialchars($fName) . '</div>
                            <div class="text-muted small mt-2">' . $fileCount . ' работ</div>
                        </a>';
                    }
                    echo '</div>';
                }
            }
            ?>
        </div>
    </div>
<?php endif; ?>
</body>
</html>