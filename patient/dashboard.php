<?php
require_once '../includes/auth.php';
if ($_SESSION["role"] != 'patient') {
    header("location: ../login.php");
    exit();
}
require_once '../includes/config.php';

$conn = connect_db();

// Получаем личные данные пациента
$sql = "SELECT Name, Surname, Patronymic, Age, PhoneNumber, Address 
        FROM Patient 
        WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION["id"]);
$stmt->execute();
$stmt->bind_result($name, $surname, $patronymic, $age, $phone, $address);
$stmt->fetch();
$stmt->close();

// Получаем размер скидки
$sql_discount = "SELECT Size FROM Discount WHERE patient_id = ?";
$stmt = $conn->prepare($sql_discount);
$stmt->bind_param("i", $_SESSION["id"]);
$stmt->execute();
$stmt->bind_result($discount_size);
$stmt->fetch();
$stmt->close();

$sql_appointments = "
    SELECT c.Date, c.Time, c.Office, d.Name AS doc_name, d.Surname AS doc_surname, d.Specialization AS doc_specialization
    FROM coupon c
    JOIN doctor d ON c.doctor_id = d.id
    WHERE c.patient_id = ?
      AND c.Date >= CURDATE()
    ORDER BY c.Date ASC, c.Time ASC
";
$stmt_appointments = $conn->prepare($sql_appointments);
$stmt_appointments->bind_param("i", $_SESSION["id"]);
$stmt_appointments->execute();
$result_appointments = $stmt_appointments->get_result();
?>

<?php include '../templates/header.php'; ?>
<link rel="stylesheet" href="../styles.css"> <!-- Подключаем CSS -->

    <h2>Панель Пациента</h2>
    
    <h3>Личные данные</h3>
    <p><strong>ФИО:</strong> 
       <?php echo htmlspecialchars($surname . ' ' . $name . ' ' . $patronymic); ?>
    </p>
    <p><strong>Возраст:</strong> <?php echo htmlspecialchars($age); ?></p>
    <p><strong>Телефон:</strong> <?php echo htmlspecialchars($phone); ?></p>
    <p><strong>Адрес:</strong> <?php echo htmlspecialchars($address); ?></p>
    <p><strong>Скидка:</strong> 
       <?php echo isset($discount_size) ? htmlspecialchars($discount_size . '%') : 'Нет'; ?>
    </p>

    <hr>

    <!-- Блок с будущими (ещё не посещёнными) талонами -->
    <h3>Мои предстоящие визиты</h3>
    <?php if ($result_appointments->num_rows > 0): ?>
        <table border="1">
            <tr>
                <th>Дата</th>
                <th>Время</th>
                <th>Врач</th>
                <th>Специализация</th> <!-- Новый заголовок для специализации -->
                <th>Кабинет</th>
            </tr>
            <?php while($row = $result_appointments->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['Date']); ?></td>
                    <td><?php echo htmlspecialchars($row['Time']); ?></td>
                    <td>
                        <?php 
                          // Имя + Фамилия врача
                          echo htmlspecialchars($row['doc_name']) 
                               . ' ' 
                               . htmlspecialchars($row['doc_surname']); 
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['doc_specialization']); ?></td> <!-- Новая ячейка для специализации -->
                    <td><?php echo htmlspecialchars($row['Office']); ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>На данный момент у вас нет предстоящих визитов.</p>
    <?php endif; ?>

    <hr>

    <h2>Действия</h2>
    <ul>
        <li><a href="book_appointment.php">Записаться на прием</a></li>
        <li><a href="view_medical_card.php">Посмотреть медицинскую карту</a></li>
    </ul>
    <?php include '../templates/footer.php'; ?>
</div>
