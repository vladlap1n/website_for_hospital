<?php
require_once '../includes/auth.php';
if ($_SESSION["role"] != 'doctor') {
    header("location: ../login.php");
    exit();
}
require_once '../includes/config.php';

// Подключение к базе данных
$conn = connect_db();

// Обработка поиска пациента
$search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';

// Получение списка пациентов по ФИО
$sql_patients = "SELECT id, Name, Surname FROM Patient WHERE CONCAT(Name, ' ', Surname) LIKE ?";
$stmt_patients = $conn->prepare($sql_patients);
$search_param = "%$search_name%";
$stmt_patients->bind_param("s", $search_param);
$stmt_patients->execute();
$result_patients = $stmt_patients->get_result();
?>

<?php include '../templates/header.php'; ?>
<link rel="stylesheet" href="../styles.css"> <!-- Подключаем CSS -->
<h2>Посмотреть медицинскую карту пациента</h2>

<form method="GET" action="">
    <label for="search_name">Введите ФИО пациента:</label>
    <input type="text" name="search_name" id="search_name" value="<?php echo htmlspecialchars($search_name); ?>" required>
    <input type="submit" value="Поиск">
</form>

<?php if ($result_patients->num_rows > 0): ?>
    <h3>Результаты поиска:</h3>
    <ul>
        <?php while ($row = $result_patients->fetch_assoc()): ?>
            <li>
                <a href="view_medical_card_details.php?patient_id=<?php echo htmlspecialchars($row['id']); ?>">
                    <?php echo htmlspecialchars($row['Name'] . ' ' . $row['Surname']); ?>
                </a>
            </li>
        <?php endwhile; ?>
    </ul>
<?php else: ?>
    <p>Пациенты не найдены.</p>
<?php endif; ?>

<?php include '../templates/footer.php'; ?>
<?php
$conn->close();
?>
