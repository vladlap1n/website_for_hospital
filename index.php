<head>
    <link rel="stylesheet" href="styles.css">
</head>

<?php
session_start();
if (isset($_SESSION['success_message'])) {
    echo '<div class="success-message">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
    unset($_SESSION['success_message']); // Удаляем сообщение после вывода
}
?>
<?php include 'templates/header.php'; ?>
<h2>Добро пожаловать на сайт поликлиники №5</h2>
<?php include 'templates/footer.php'; ?>