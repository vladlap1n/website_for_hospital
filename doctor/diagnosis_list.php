<?php
require_once '../includes/auth.php';
if ($_SESSION["role"] != 'doctor') {
    header("location: ../login.php");
    exit();
}
require_once '../includes/config.php';

// Подключение к базе данных
$conn = connect_db();

// Получение значения поиска из формы
$search_query = isset($_POST['search']) ? trim($_POST['search']) : '';

// Получение диагнозов и цен за прием с учетом поиска
$sql_diagnoses = "SELECT medical_card.id, Patient.Name AS patient_name, Patient.Surname AS patient_surname, 
                  medical_card.Date, Coupon.Time, medical_card.Diagnosis, medical_card.Amount, 
                  medical_card.Description, medical_card.Type 
                  FROM medical_card 
                  JOIN Coupon ON medical_card.coupon_id = Coupon.id 
                  JOIN Patient ON Coupon.patient_id = Patient.id 
                  WHERE Coupon.doctor_id = ?";

if (!empty($search_query)) {
    $sql_diagnoses .= " AND (Patient.Name LIKE ? OR Patient.Surname LIKE ?)";
}

$sql_diagnoses .= " ORDER BY medical_card.Date DESC";

$stmt_diagnoses = $conn->prepare($sql_diagnoses);

// Определяем параметры для bind_param
if (!empty($search_query)) {
    $search_param = '%' . $search_query . '%'; // Подготовка параметра для LIKE
    $stmt_diagnoses->bind_param("iss", $_SESSION["id"], $search_param, $search_param);
} else {
    // Если нет поиска, только один параметр
    $stmt_diagnoses->bind_param("i", $_SESSION["id"]);
}

$stmt_diagnoses->execute();
$result_diagnoses = $stmt_diagnoses->get_result();
?>

<?php include '../templates/header.php'; ?>
<link rel="stylesheet" href="../styles.css">
<h2>Список диагнозов и цен</h2>

<!-- Форма поиска -->
<form method="post" action="">
    <input type="text" name="search" placeholder="Введите ФИО пациента" value="<?php echo htmlspecialchars($search_query); ?>">
    <input type="submit" value="Поиск">
</form>

<table border="1">
    <tr>
        <th>Пациент</th>
        <th>Дата</th>
        <th>Время</th> <!-- Новый столбец для времени -->
        <th>Диагноз</th>
        <th>Цена</th>
        <th>Описание</th>
        <th>Тип</th>
        <th>Действия</th>
    </tr>
    <?php if ($result_diagnoses->num_rows > 0): ?>
        <?php while ($row = $result_diagnoses->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['patient_name'] . ' ' . $row['patient_surname']); ?></td>
                <td><?php echo htmlspecialchars($row['Date']); ?></td>
                <td><?php echo htmlspecialchars($row['Time']); ?></td> <!-- Отображение времени -->
                <td><?php echo htmlspecialchars($row['Diagnosis']); ?></td>
                <td><?php echo htmlspecialchars($row['Amount']); ?></td>
                <td><?php echo htmlspecialchars($row['Description']); ?></td>
                <td><?php echo htmlspecialchars($row['Type']); ?></td>
                <td>
                    <a href="edit_diagnosis.php?id=<?php echo htmlspecialchars($row['id']); ?>">Изменить</a>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="8">Нет записанных диагнозов.</td> <!-- Изменено на 8 для соответствия количеству столбцов -->
        </tr>
    <?php endif; ?>
</table>

<?php include '../templates/footer.php'; ?>
<?php
$conn->close();
?>
