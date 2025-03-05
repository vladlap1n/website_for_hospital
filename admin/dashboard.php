<?php
require_once '../includes/auth.php';
if($_SESSION["role"] != 'admin'){
    header("location: ../login.php");
    exit();
}
?>

<?php include '../templates/header.php'; ?>
<link rel="stylesheet" href="../styles.css">
<h2>Панель Администратора</h2>
<ul>
    <li><a href="manage_doctors.php">Управление Врачами</a></li>
    <li><a href="select_doctor.php">Управление Талонами</a></li>
    <li><a href="manage_discounts.php">Управление Скидками</a></li>
    <li><a href="unpaid_appointments.php">Неоплаченные Приемы</a></li>
    <!-- Добавьте другие функции по необходимости -->
</ul>
<?php include '../templates/footer.php'; ?>
