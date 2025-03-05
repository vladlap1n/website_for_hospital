<?php
require_once '../includes/auth.php';
if ($_SESSION["role"] != 'admin') {
    header("location: ../login.php");
    exit();
}
require_once '../includes/config.php';

$conn = connect_db();

if (isset($_GET['medical_card_id'])) {
    $medical_card_id = $_GET['medical_card_id'];

    $sql = "
    SELECT 
        mc.Description,
        mc.Type,
        c.Date AS appointment_date,
        c.Time AS appointment_time,
        mc.Diagnosis,
        mc.Amount,
        d.Name AS doctor_name,
        d.Surname AS doctor_surname,
        p.Name AS patient_name,
        p.Surname AS patient_surname,
        p.PhoneNumber,
        p.Address
    FROM Medical_card mc
    JOIN coupon c ON mc.coupon_id = c.id
    JOIN doctor d ON c.doctor_id = d.id
    JOIN Patient p ON mc.patient_id = p.id
    WHERE mc.id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $medical_card_id);
    $stmt->execute();
    $result_details = $stmt->get_result();

    if ($result_details->num_rows > 0) {
        $details = $result_details->fetch_assoc();
    } else {
        echo "Прием не найден.";
        exit();
    }
} else {
    echo "Некорректный запрос.";
    exit();
}
?>
<link rel="stylesheet" href="../styles.css">
<?php include '../templates/header.php'; ?>
<h2>Детали Приема</h2>
<p><strong>Описание:</strong> <?php echo htmlspecialchars($details['Description']); ?></p>
<p><strong>Тип приема:</strong> <?php echo htmlspecialchars($details['Type']); ?></p>
<p><strong>Дата и время приема:</strong> <?php echo htmlspecialchars($details['appointment_date'] . ' ' . $details['appointment_time']); ?></p> <!-- Обновлено отображение -->
<p><strong>Диагноз:</strong> <?php echo htmlspecialchars($details['Diagnosis']); ?></p>
<p><strong>Сумма:</strong> <?php echo htmlspecialchars($details['Amount']); ?></p>
<p><strong>Врач:</strong> <?php echo htmlspecialchars($details['doctor_name'] . ' ' . $details['doctor_surname']); ?></p>
<p><strong>Пациент:</strong> <?php echo htmlspecialchars($details['patient_name'] . ' ' . $details['patient_surname']); ?></p>
<p><strong>Телефон пациента:</strong> <?php echo htmlspecialchars($details['PhoneNumber']); ?></p>
<p><strong>Адрес пациента:</strong> <?php echo htmlspecialchars($details['Address']); ?></p>

<?php include '../templates/footer.php'; ?>
