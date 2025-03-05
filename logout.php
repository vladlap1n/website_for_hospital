<?php
session_start();
$_SESSION = array();
session_destroy();
header("location: login.php?message=Вы успешно вышли из системы.");
exit();
?>