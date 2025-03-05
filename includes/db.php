<?php
// db.php

require_once 'config.php';

// Пример функции для получения всех врачей по специализации
function get_doctors_by_specialization($specialization) {
    $conn = connect_db();
    $sql = "SELECT id, Name, Surname, Category FROM Doctor WHERE Specialization = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $specialization);
    $stmt->execute();
    $result = $stmt->get_result();
    $doctors = [];
    while($row = $result->fetch_assoc()) {
        $doctors[] = $row;
    }
    $stmt->close();
    $conn->close();
    return $doctors;
}

// Другие функции для работы с БД можно добавлять здесь
?>