<!-- header.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Поликлиника</title>
    <link rel="stylesheet" href="/polyclinic/css/styles.css">
    <div class="container">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="/polyclinic/js/scripts.js"></script>
</head>
<body>
    <header>
        <h1>Поликлиника</h1>
        <nav>
            <a href="/polyclinic/index.php">Главная</a>
            <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                <?php if($_SESSION["role"] == 'patient'): ?>
                    <a href="/polyclinic/patient/dashboard.php">Панель Пациента</a>
                <?php elseif($_SESSION["role"] == 'admin'): ?>
                    <a href="/polyclinic/admin/dashboard.php">Панель Администратора</a>
                <?php elseif($_SESSION["role"] == 'doctor'): ?>
                    <a href="/polyclinic/doctor/dashboard.php">Панель Врача</a>
                <?php endif; ?>
                <a href="/polyclinic/logout.php">Выйти</a>
            <?php else: ?>
                <a href="/polyclinic/login.php">Вход</a>
                <a href="/polyclinic/register.php">Регистрация</a>
            <?php endif; ?>
        </nav>
    </header>
    <div class="container">