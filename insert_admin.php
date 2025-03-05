<?php
require_once 'includes/config.php';

$conn = connect_db();

// Данные для нового администратора
$param_name = "Джордж"; // Замените на нужное имя
$param_surname = "Лукас"; // Замените на нужную фамилию
$param_username = "ptr1"; // Замените на нужное имя пользователя
$param_password = "123456"; // Замените на нужный пароль

// Хэшируем пароль
$password_hashed = password_hash($param_password, PASSWORD_DEFAULT);

// SQL запрос для вставки нового администратора
$sql_insert = "INSERT INTO admin (Name, Surname, Username, Password) VALUES (?, ?, ?, ?)";

if ($stmt_insert = $conn->prepare($sql_insert)) {
    // Привязываем параметры
    $stmt_insert->bind_param("ssss", $param_name, $param_surname, $param_username, $password_hashed);
    
    // Выполняем запрос
    if ($stmt_insert->execute()) {
        echo "Администратор успешно добавлен.";
    } else {
        echo "Ошибка при добавлении администратора: " . $stmt_insert->error;
    }

    // Закрываем подготовленный запрос
    $stmt_insert->close();
} else {
    echo "Ошибка при подготовке запроса: " . $conn->error;
}

// Закрываем соединение
$conn->close();
?>
