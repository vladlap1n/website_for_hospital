<?php

require_once '../includes/auth.php';
if($_SESSION["role"] != 'doctor'){
    header("location: ../login.php");
    exit();
}
require_once '../includes/config.php';

if(!isset($_GET['id'])){
    header("location: dashboard.php");
    exit();
}

$coupon_id = intval($_GET['id']);
$conn = connect_db();
$sql = "SELECT Patient.id, Patient.Name, Patient.Surname, Patient.Patronymic, Coupon.Date, Coupon.Time, Coupon.Purpose, Medical_card.Description, Medical_card.Type, Medical_card.Diagnosis, Medical_card.Amount FROM Coupon JOIN Patient ON Coupon.patient_id = Patient.id LEFT JOIN Medical_card ON Coupon.id = Medical_card.id WHERE Coupon.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $coupon_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows != 1){
    echo "Запись не найдена.";
    exit();
}

$appointment = $result->fetch_assoc();
$stmt->close();
$diagnosis_err = "";
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['diagnose'])){
    $diagnosis = trim($_POST['diagnosis']);
    $amount = floatval($_POST['amount']);
    $sql_update = "UPDATE Medical_card SET Diagnosis = ?, Amount = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("sdi", $diagnosis, $amount, $coupon_id);
    if($stmt_update->execute()){
        header("location: dashboard.php");
        exit();
    } else{
        $diagnosis_err = "Ошибка при обновлении диагноза.";
    }
    $stmt_update->close();
}
$conn->close();
?>

<?php include '../templates/header.php'; ?>
<head>
    <link rel="stylesheet" href="styles.css">
</head>
<h2>Информация о Пациенте</h2>
<p><strong>Имя:</strong> <?php echo htmlspecialchars($appointment['Name'] . ' ' . $appointment['Surname'] . ' ' . $appointment['Patronymic']); ?></p>
<p><strong>Дата приема:</strong> <?php echo htmlspecialchars($appointment['Date']); ?></p>
<p><strong>Время приема:</strong> <?php echo htmlspecialchars($appointment['Time']); ?></p>
<p><strong>Цель посещения:</strong> <?php echo htmlspecialchars($appointment['Purpose']); ?></p>

<?php if($appointment['Diagnosis']): ?>
    <h3>Диагноз</h3>
    <p><?php echo htmlspecialchars($appointment['Diagnosis']); ?></p>
    <p><strong>Сумма за прием:</strong> <?php echo htmlspecialchars($appointment['Amount']); ?> руб.</p>
<?php else: ?>
    <h3>Поставить диагноз</h3>
    <?php 
    if(!empty($diagnosis_err)){
        echo '<div class="error">' . htmlspecialchars($diagnosis_err) . '</div>';
    }
    ?>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . '?id=' . $coupon_id); ?>" method="post">
        <div>
            <label>Диагноз</label>
            <input type="text" name="diagnosis" required>
        </div>
        <div>
            <label>Сумма за прием (руб.)</label>
            <input type="number" step="0.01" name="amount" required>
        </div>
        <div>
            <input type="submit" name="diagnose" value="Сохранить Диагноз">
        </div>
    </form>
<?php endif; ?>
<?php include '../templates/footer.php'; ?>