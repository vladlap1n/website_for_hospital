<?php
require_once '../includes/auth.php';
if ($_SESSION["role"] != 'admin') {
    header("location: ../login.php");
    exit();
}
require_once '../includes/config.php';

$conn = connect_db();

// Check if the doctor ID is provided
if (isset($_GET['id'])) {
    $doctor_id = intval($_GET['id']);
    
    // Fetch the doctor's current data
    $sql = "SELECT * FROM Doctor WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $doctor = $result->fetch_assoc();
    } else {
        echo "Доктор не найден.";
        exit();
    }
} else {
    echo "ID врача не указан.";
    exit();
}

// Update doctor information
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_doctor'])) {
    $name = trim($_POST['name']);
    $surname = trim($_POST['surname']);
    $patronymic = trim($_POST['patronymic']);
    $age = intval($_POST['age']);
    $specialization = trim($_POST['specialization']);
    $category = intval($_POST['category']);
    $username = trim($_POST['username']); 
   
    // Prepare update statement
    $sql_update = "UPDATE Doctor SET Name=?, Surname=?, Patronymic=?, Age=?, Specialization=?, Category=?, Username=? WHERE id=?";
    
    if ($stmt_update = $conn->prepare($sql_update)) {
        $stmt_update->bind_param("sssisisi", $name, $surname, $patronymic, $age, $specialization, $category, $username, $doctor_id);
        
        if ($stmt_update->execute()) {
            header("location: manage_doctors.php");
            exit();
        } else {
            echo "Ошибка: " . $stmt_update->error;
        }
        
        $stmt_update->close();
    }
}

$conn->close();
?>
<link rel="stylesheet" href="../styles.css">
<?php include '../templates/header.php'; ?>
<h2>Редактировать Врача</h2>
<form action="edit_doctor.php?id=<?php echo htmlspecialchars($doctor_id); ?>" method="post">
    <div>
        <label>Имя</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($doctor['Name']); ?>" required>
    </div>
    <div>
        <label>Фамилия</label>
        <input type="text" name="surname" value="<?php echo htmlspecialchars($doctor['Surname']); ?>" required>
    </div>
    <div>
        <label>Отчество</label>
        <input type="text" name="patronymic" value="<?php echo htmlspecialchars($doctor['Patronymic']); ?>">
    </div>
    <div>
        <label>Возраст</label>
        <input type="number" name="age" value="<?php echo htmlspecialchars($doctor['Age']); ?>" required>
    </div>
    <div>
        <label>Специализация</label>
        <input type="text" name="specialization" value="<?php echo htmlspecialchars($doctor['Specialization']); ?>" required>
    </div>
    <div>
        <label>Категория</label>
        <select name="category" required>
            <option value="1" <?php if ($doctor['Category'] == 1) echo 'selected'; ?>>1-я категория</option>
            <option value="2" <?php if ($doctor['Category'] == 2) echo 'selected'; ?>>2-я категория</option>
            <option value="3" <?php if ($doctor['Category'] == 3) echo 'selected'; ?>>3-я категория</option>
        </select>
    </div>
    <div>
        <label>Логин</label> <!-- Поле для ввода логина -->
        <input type="text" name="username" value="<?php echo htmlspecialchars($doctor['Username']); ?>" required>
    </div>
    
    <div>
        <input type="hidden" name="update_doctor" value="1">
        <input type="submit" value="Обновить Врача">
    </div>
</form>

<?php include '../templates/footer.php'; ?>
