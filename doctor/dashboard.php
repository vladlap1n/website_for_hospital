<?php
require_once '../includes/auth.php';
if ($_SESSION["role"] != 'doctor') {
    header("location: ../login.php");
    exit();
}
require_once '../includes/config.php';

// Подключение к базе данных
$conn = connect_db();

// Получение личных данных врача
$sql = "SELECT Name, Surname, Patronymic, Specialization, Category FROM Doctor WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION["id"]);
$stmt->execute();
$stmt->bind_result($name, $surname, $patronymic, $specialization, $category);
$stmt->fetch();
$stmt->close();

// Получение будущих записей на прием с номером кабинета
$sql_upcoming_appointments = "SELECT Coupon.id, Patient.Name AS patient_name, Patient.Surname AS patient_surname, Coupon.Date, Coupon.Time, Coupon.Office 
                               FROM Coupon 
                               JOIN Patient ON Coupon.patient_id = Patient.id 
                               WHERE Coupon.doctor_id = ? AND (Coupon.Date > NOW()) 
                               ORDER BY Coupon.Date ASC";
$stmt_upcoming = $conn->prepare($sql_upcoming_appointments);
$stmt_upcoming->bind_param("i", $_SESSION["id"]);
$stmt_upcoming->execute();
$result_upcoming_appointments = $stmt_upcoming->get_result();

// Получение прошедших записей на прием, для которых еще не добавлена медицинская карта
$sql_past_appointments = "SELECT Coupon.id, Patient.Name AS patient_name, Patient.Surname AS patient_surname, Coupon.Date, Coupon.Time
                          FROM Coupon 
                          JOIN Patient ON Coupon.patient_id = Patient.id 
                          WHERE Coupon.doctor_id = ? 
                          AND (Coupon.Date < NOW())
                          AND NOT EXISTS (
                              SELECT 1 FROM medical_card WHERE medical_card.Date = Coupon.Date AND medical_card.patient_id = Coupon.patient_id
                          )
                          ORDER BY Coupon.Date DESC";
$stmt_past = $conn->prepare($sql_past_appointments);
$stmt_past->bind_param("i", $_SESSION["id"]);
$stmt_past->execute();
$result_past_appointments = $stmt_past->get_result();
?>

<?php include '../templates/header.php'; ?>
<h2>Панель Врача</h2>

<h3>Личные данные</h3>
<p><strong>Имя:</strong> <?php echo htmlspecialchars($name . ' ' . $surname . ' ' . $patronymic); ?></p>
<p><strong>Специализация:</strong> <?php echo htmlspecialchars($specialization); ?></p>
<p><strong>Категория:</strong> <?php echo htmlspecialchars($category); ?></p>
<link rel="stylesheet" href="../styles.css">
<h3>Действия</h3>
<ul>
    <li><a href="diagnosis_list.php">Просмотреть диагнозы</a></li> <!-- Кнопка для просмотра диагнозов -->
    <li><a href="view_medical_card.php">Посмотреть медицинскую карту пациента</a></li> <!-- Кнопка для просмотра медицинских карт -->
</ul>

<h3>Будущие записи на прием</h3>
<table border="1">
    <tr>
        <th>Пациент</th>
        <th>Дата и время</th>
        <th>Кабинет</th> 
    </tr>
    <?php if ($result_upcoming_appointments->num_rows > 0): ?>
        <?php while ($row = $result_upcoming_appointments->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['patient_name'] . ' ' . $row['patient_surname']); ?></td>
                <td><?php echo htmlspecialchars($row['Date'] . ' ' . $row['Time']); ?></td>
                <td><?php echo htmlspecialchars($row['Office']); ?></td> <!-- Отображение номера кабинета -->
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="4">Нет будущих записей на прием.</td>
        </tr>
    <?php endif; ?>
</table>

<h3>Прошедшие записи с непоставленным диагнозом</h3>
<table border="1">
    <tr>
        <th>Пациент</th>
        <th>Дата и время</th>
        <th>Действия</th>
    </tr>
    <?php if ($result_past_appointments->num_rows > 0): ?>
        <?php while ($row = $result_past_appointments->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['patient_name'] . ' ' . $row['patient_surname']); ?></td>
                <td><?php echo htmlspecialchars($row['Date'] . ' ' . $row['Time']); ?></td>
                <td>
                    <a href="diagnosis_payment.php?id=<?php echo htmlspecialchars($row['id']); ?>">Поставить диагноз</a>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="3">Нет прошедших записей на прием.</td>
        </tr>
    <?php endif; ?>
</table>

<?php include '../templates/footer.php'; ?>
<?php
$conn->close();
?>
