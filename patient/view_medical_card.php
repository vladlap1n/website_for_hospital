<?php
require_once '../includes/auth.php';
if ($_SESSION["role"] != 'patient') {
    header("location: ../login.php");
    exit();
}
require_once '../includes/config.php';

$conn = connect_db();

// Получаем медицинскую карту...
$sql = "
SELECT 
    mc.Description, 
    mc.Type, 
    mc.Date, 
    mc.Diagnosis, 
    mc.Amount AS original_amount,
    d.Name AS doctor_name,
    d.Surname AS doctor_surname,
    c.Date AS appointment_date,
    c.Time AS appointment_time,
    mc.id AS medical_card_id
FROM Medical_card mc
JOIN coupon c ON mc.coupon_id = c.id
JOIN doctor d ON c.doctor_id = d.id
WHERE mc.patient_id = ?
ORDER BY c.Date DESC, c.Time DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION["id"]);
$stmt->execute();
$result_medical = $stmt->get_result();

// Получаем размер скидки
$sql_discount = "SELECT Size FROM Discount WHERE patient_id = ?";
$stmt_discount = $conn->prepare($sql_discount);
$stmt_discount->bind_param("i", $_SESSION["id"]);
$stmt_discount->execute();
$stmt_discount->bind_result($discount_size);
$stmt_discount->fetch();
$stmt_discount->close();
?>

<link rel="stylesheet" href="../styles.css">

<?php include '../templates/header.php'; ?>

<h2>Медицинская карта</h2>

<!-- Блок для вывода возможных уведомлений AJAX -->
<div id="ajax-message" style="color: red; margin-bottom: 10px;"></div>

<table border="1" id="medical-cards-table">
    <tr>
        <th>Описание</th>
        <th>Тип</th>
        <th>Дата и время приема</th>
        <th>Диагноз</th>
        <th>Сумма</th>
        <th>Врач</th>
        <th>Действия</th>
    </tr>
    <?php while ($row = $result_medical->fetch_assoc()): ?>
        <?php
            // Вычисляем цену со скидкой
            $original_amount = $row['original_amount'];
            $discounted_amount = (isset($discount_size) && $discount_size > 0)
                ? $original_amount * (1 - ($discount_size / 100))
                : $original_amount;

            // Проверяем, была ли уже произведена оплата
            $payment_check_sql = "SELECT COUNT(*) FROM Payment WHERE medical_card_id = ?";
            $payment_check_stmt = $conn->prepare($payment_check_sql);
            $payment_check_stmt->bind_param("i", $row['medical_card_id']);
            $payment_check_stmt->execute();
            $payment_check_stmt->bind_result($payment_count);
            $payment_check_stmt->fetch();
            $payment_check_stmt->close();

            $isPaid = ($payment_count > 0);
        ?>
        <tr id="row-<?php echo $row['medical_card_id']; ?>">
            <td><?php echo htmlspecialchars($row['Description']); ?></td>
            <td><?php echo htmlspecialchars($row['Type']); ?></td>
            <td><?php echo htmlspecialchars($row['appointment_date'] . ' ' . $row['appointment_time']); ?></td>
            <td><?php echo htmlspecialchars($row['Diagnosis']); ?></td>
            
            <td>
                <?php if ($isPaid): ?>
                    <!-- Если оплачено, показываем только исходную сумму -->
                    <span id="amount-<?php echo $row['medical_card_id']; ?>">
                        <?php echo number_format($original_amount, 2); ?>
                    </span>
                <?php else: ?>
                    <?php if (isset($discount_size) && $discount_size > 0): ?>
                        <span id="amount-<?php echo $row['medical_card_id']; ?>">
                            <?php 
                            // Показываем и оригинальную сумму, и скидочную
                            echo number_format($original_amount, 2) . 
                                 " (со скидкой {$discount_size}%: " . 
                                 number_format($discounted_amount, 2) . ")";
                            ?>
                        </span>
                    <?php else: ?>
                        <span id="amount-<?php echo $row['medical_card_id']; ?>">
                            <?php echo number_format($original_amount, 2); ?>
                        </span>
                    <?php endif; ?>
                <?php endif; ?>
            </td>
            
            <td><?php echo htmlspecialchars($row['doctor_name'] . ' ' . $row['doctor_surname']); ?></td>
            
            <td id="action-<?php echo $row['medical_card_id']; ?>">
                <?php if ($isPaid): ?>
                    Оплачено
                <?php else: ?>
                    <!-- Ссылка/кнопка с классом pay-btn и data-id -->
                    <a 
                      href="#" 
                      class="pay-btn" 
                      data-id="<?php echo $row['medical_card_id']; ?>"
                    >
                      Оплатить
                    </a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

<script>
// Функция для вывода сообщений пользователю
function showMessage(msg, isError = true) {
    const msgDiv = document.getElementById('ajax-message');
    msgDiv.style.color = isError ? 'red' : 'green';
    msgDiv.textContent = msg;
}

document.addEventListener('DOMContentLoaded', function() {
    const payButtons = document.querySelectorAll('.pay-btn');
    payButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault();

            const medicalCardId = this.getAttribute('data-id');

            fetch('pay_for_appointment.php?medical_card_id=' + medicalCardId)
                .then(response => response.json())
                .then(data => {
                    // Обрабатываем ответ JSON
                    if (data.status === 'success') {
                        
                        const amountSpan = document.getElementById('amount-' + medicalCardId);
                        if (amountSpan) {
                            amountSpan.textContent = data.discounted_amount;
                        }
                        // Обновляем ячейку "Действия"
                        const actionTd = document.getElementById('action-' + medicalCardId);
                        if (actionTd) {
                            actionTd.textContent = 'Оплачено';
                        }
                        // Выводим сообщение об успехе
                        showMessage(data.message, false);
                    } else {
                        // Если ошибка
                        showMessage(data.message);
                    }
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                    showMessage('Произошла ошибка при оплате.');
                });
        });
    });
});
</script>

<?php include '../templates/footer.php'; ?>