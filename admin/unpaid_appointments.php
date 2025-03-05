<?php
require_once '../includes/auth.php';
if ($_SESSION["role"] != 'admin') {
    header("location: ../login.php");
    exit();
}
require_once '../includes/config.php';

$conn = connect_db();

// Получаем неоплаченные приемы
$sql = "
SELECT 
    mc.id AS medical_card_id,
    mc.Description,
    mc.Type,
    c.Date AS appointment_date,
    c.Time AS appointment_time,  -- Добавлено время
    d.Name AS doctor_name,
    d.Surname AS doctor_surname,
    p.Name AS patient_name,
    p.Surname AS patient_surname,
    p.PhoneNumber
FROM Medical_card mc
JOIN coupon c ON mc.coupon_id = c.id
JOIN doctor d ON c.doctor_id = d.id
JOIN Patient p ON mc.patient_id = p.id
LEFT JOIN Payment pay ON mc.id = pay.medical_card_id
WHERE pay.medical_card_id IS NULL  -- Только неоплаченные приемы
ORDER BY mc.Date DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result_unpaid = $stmt->get_result();
?>
<link rel="stylesheet" href="../styles.css">
<?php include '../templates/header.php'; ?>
<h2>Неоплаченные Приемы</h2>
<table border="1">
    <tr>
        <th>Дата и время приема</th> <!-- Обновлено заголовок -->
        <th>Врач</th>
        <th>Пациент</th>
        <th>Телефон пациента</th>
        <th>Действия</th>
    </tr>
    <?php while ($row = $result_unpaid->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['appointment_date'] . ' ' . $row['appointment_time']); ?></td> <!-- Обновлено отображение -->
            <td><?php echo htmlspecialchars($row['doctor_name'] . ' ' . $row['doctor_surname']); ?></td>
            <td><?php echo htmlspecialchars($row['patient_name'] . ' ' . $row['patient_surname']); ?></td>
            <td><?php echo htmlspecialchars($row['PhoneNumber']); ?></td>
            <td><a href="view_appointment_details.php?medical_card_id=<?php echo $row['medical_card_id']; ?>">Просмотреть детали</a></td> <!-- Ссылка на детали -->
        </tr>
    <?php endwhile; ?>
</table>

<?php include '../templates/footer.php'; ?>
