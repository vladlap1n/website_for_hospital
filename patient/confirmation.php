<?php
require_once '../includes/auth.php';
if ($_SESSION["role"] != 'patient') {
    header("location: ../login.php");
}

include '../templates/header.php';
?>

<h2>Подтверждение записи</h2>
<p>Ваша запись на прием успешно завершена!</p>
<a href="../patient/dashboard.php">На главную страницу</a>

<?php include '../templates/footer.php'; ?>
