<?php 
// auth.php 
session_start(); 
 
// Устанавливаем время таймаута в секундах (2 минуты) 
$timeout_duration = 120; 
 
// Проверяем, установлена ли сессия 
if (isset($_SESSION['LAST_ACTIVITY'])) { 
    if ((time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) { 
        session_unset(); 
        session_destroy(); 
        header("Location: ../login.php?message=Ваша сессия истекла"); 
        exit; 
    } 
} 
$_SESSION['LAST_ACTIVITY'] = time(); 
?>