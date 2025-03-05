<?php
require_once '../includes/auth.php';
if ($_SESSION["role"] != 'doctor') {
    header("location: ../login.php");
    exit();
}
require_once '../includes/config.php';

// Подключение к базе данных
$conn = connect_db();

// Получение ID пациента из URL
$patient_id = isset($_GET['patient_id']) ? intval($_GET['patient_id']) : 0;

// Получение медицинской карты пациента с информацией о враче
$sql_medical_card = "
    SELECT mc.*, d.Name AS doctor_name, d.Surname AS doctor_surname, d.Patronymic AS doctor_patronymic, d.Specialization AS doctor_specialization, d.Category AS doctor_category, c.Time 
    FROM medical_card mc
    JOIN Coupon c ON mc.coupon_id = c.id
    JOIN Doctor d ON c.doctor_id = d.id
    WHERE mc.patient_id = ?
";
$stmt_medical_card = $conn->prepare($sql_medical_card);
$stmt_medical_card->bind_param("i", $patient_id);
$stmt_medical_card->execute();
$result_medical_card = $stmt_medical_card->get_result();
?>

<?php include '../templates/header.php'; ?>
<link rel="stylesheet" href="../styles.css"> <!-- Подключаем CSS -->
<style>
.table-container {
    max-width: 100%; /* Максимальная ширина контейнера */
    overflow-x: auto; /* Прокрутка по оси X при необходимости */
    margin: 20px 0; /* Отступы сверху и снизу */
}

table {
    width: 100%; /* Ширина таблицы на 100% от контейнера */
    border-collapse: collapse; /* Убирает двойные границы */
}

th, td {
    padding: 10px; /* Отступы внутри ячеек */
    text-align: left; /* Выравнивание текста по левому краю */
    border: 1px solid #ccc; /* Граница ячеек */
}

th {
    background-color: #4cae4c; /* Цвет фона заголовков */
}

@media (max-width: 600px) {
    th, td {
        font-size: 14px; /* Уменьшение размера шрифта на маленьких экранах */
    }
}
</style>

<h2>Медицинская карта пациента</h2>

<?php if ($result_medical_card->num_rows > 0): ?>
    <div class="table-container">
        <table>
            <tr>
                <th>Дата</th>
                <th>Время приема</th>
                <th>Диагноз</th>
                <th>Описание</th>
                <th>Тип</th>
                <th>ФИО врача</th>
                <th>Специализация врача</th>
                <th>Категория врача</th>
                <th>Сумма за прием</th>
            </tr>
            <?php while ($row = $result_medical_card->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['Date']); ?></td>
                    <td><?php echo htmlspecialchars($row['Time']); ?></td>
                    <td><?php echo htmlspecialchars($row['Diagnosis']); ?></td>
                    <td><?php echo htmlspecialchars($row['Description']); ?></td>
                    <td><?php echo htmlspecialchars($row['Type']); ?></td>
                    <td><?php echo htmlspecialchars($row['doctor_surname'] . ' ' . $row['doctor_name']  . ' ' . $row['doctor_patronymic']); ?></td>
                    <td><?php echo htmlspecialchars($row['doctor_specialization']); ?></td>
                    <td><?php echo htmlspecialchars($row['doctor_category']); ?></td>
                    <td><?php echo htmlspecialchars($row['Amount']); ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

<?php else: ?>
    <p>Нет медицинских карт для этого пациента.</p>
<?php endif; ?>

<?php include '../templates/footer.php'; ?>
<?php
$conn->close();
?>
