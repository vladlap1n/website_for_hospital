<?php
require_once '../includes/auth.php';
if ($_SESSION["role"] != 'doctor') {
    header("location: ../login.php");
    exit();
}
require_once '../includes/config.php';

// Подключение к базе данных
$conn = connect_db();

// Получение диагноза по ID
if (isset($_GET['id'])) {
    $diagnosis_id = intval($_GET['id']);
    $sql = "SELECT Diagnosis, Amount, Description, Type FROM medical_card WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $diagnosis_id);
    $stmt->execute();
    $stmt->bind_result($diagnosis, $amount, $description, $type);
    $stmt->fetch();
    $stmt->close();
} else {
    header("Location: diagnosis_list.php");
    exit();
}

// Обработка формы редактирования
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_diagnosis = trim($_POST['diagnosis']);
    $new_description = trim($_POST['description']);
    $new_type = trim($_POST['type']);

    // Проверка на заполненность полей
    if (empty($new_diagnosis) || empty($new_description) || empty($new_type)) {
        $error = "Пожалуйста, заполните все поля корректно.";
    } else {
        $update_sql = "UPDATE medical_card SET Diagnosis = ?, Description = ?, Type = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        if (!$update_stmt) {
            echo "Ошибка подготовки запроса обновления: " . htmlspecialchars($conn->error);
            exit();
        }
        
        // Привязка параметров к запросу
        $update_stmt->bind_param("sssi", $new_diagnosis, $new_description, $new_type, $diagnosis_id);
        
        if ($update_stmt->execute()) {
            header("Location: diagnosis_list.php?success=1");
            exit();
        } else {
            echo "Ошибка обновления записи.";
        }
    }
}
?>

<?php include '../templates/header.php'; ?>
<link rel="stylesheet" href="../styles.css"> <!-- Подключаем CSS -->
<h2>Редактирование диагноза</h2>

<?php
if (isset($error)) {
    echo '<p style="color: red;">' . htmlspecialchars($error) . '</p>';
}
?>

<form method="post" action="">
    <label for="diagnosis">Диагноз:</label><br>
    <input type="text" id="diagnosis" name="diagnosis" value="<?php echo htmlspecialchars($diagnosis); ?>" required><br><br>

    <label for="amount">Цена:</label><br>
    <input type="text" id="amount" name="amount" value="<?php echo htmlspecialchars($amount); ?>" disabled><br><br>
    
    <label for="description">Описание:</label><br>
    <input type="text" id="description" name="description" value="<?php echo htmlspecialchars($description); ?>" required><br><br>

    <label for="type">Тип:</label><br>
    <input type="text" id="type" name="type" value="<?php echo htmlspecialchars($type); ?>" required><br><br>

    <input type="submit" value="Сохранить изменения">
</form>

<?php include '../templates/footer.php'; ?>
<?php
$conn->close();
?>
