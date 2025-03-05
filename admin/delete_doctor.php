<?php
require_once '../includes/auth.php';
if ($_SESSION["role"] != 'admin') {
    header("location: ../login.php");
    exit();
}
require_once '../includes/config.php';

$conn = connect_db();

// Проверяем, передан ли идентификатор врача
if (isset($_GET['id'])) {
    $doctor_id = intval($_GET['id']);

    // Подготовка SQL-запроса для удаления врача
    $sql = "DELETE FROM Doctor WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $doctor_id);
        
        if ($stmt->execute()) {
            // Успешное удаление, перенаправляем на страницу управления врачами
            header("location: manage_doctors.php");
            exit();
        } else {
            echo "Ошибка при удалении врача: " . $stmt->error;
        }

        $stmt->close();
    }
} else {
    echo "ID врача не указан.";
}

$conn->close();
?>
