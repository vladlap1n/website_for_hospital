<?php
require_once '../includes/auth.php';
if ($_SESSION["role"] != 'doctor') {
    header("location: ../login.php");
    exit();
}
require_once '../includes/config.php';

// Подключение к базе данных
$conn = connect_db();

// Получение ID купона из GET или POST
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $coupon_id = intval($_GET['id']);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['coupon_id'])) {
    $coupon_id = intval($_POST['coupon_id']);
} else {
    header("location: dashboard.php");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Обработка формы
    if (isset($_POST['diagnosis'], $_POST['amount'], $_POST['type'], $_POST['description'])) {
        $diagnosis = trim($_POST['diagnosis']);
        $amount = floatval($_POST['amount']);
        $type = trim($_POST['type']);
        $description = trim($_POST['description']);

        if (empty($diagnosis) || $amount <= 0 || empty($type) || empty($description)) {
            $error = "Пожалуйста, заполните все поля корректно.";
        } else {
            // Начало транзакции
            $conn->begin_transaction();

            try {
                // Получение данных купона с блокировкой для предотвращения одновременного доступа
                $sql_coupon = "SELECT Date, patient_id, doctor_id FROM coupon WHERE id = ? FOR UPDATE";
                $stmt_coupon = $conn->prepare($sql_coupon);
                if (!$stmt_coupon) {
                    throw new Exception("Ошибка подготовки запроса: " . $conn->error);
                }
                $stmt_coupon->bind_param("i", $coupon_id);
                $stmt_coupon->execute();
                $result_coupon = $stmt_coupon->get_result();

                if ($result_coupon->num_rows === 0) {
                    throw new Exception("Купон не найден.");
                }

                $coupon = $result_coupon->fetch_assoc();
                $patient_id = $coupon['patient_id'];
                $doctor_id = $coupon['doctor_id'];
                $date = $coupon['Date'];

                if (is_null($patient_id)) {
                    throw new Exception("Купон не назначен пациенту.");
                }

                // Проверка, что медицинская карта еще не существует
                $sql_check = "SELECT id FROM medical_card WHERE coupon_id = ?";
                $stmt_check = $conn->prepare($sql_check);
                if (!$stmt_check) {
                    throw new Exception("Ошибка подготовки запроса проверки: " . $conn->error);
                }
                $stmt_check->bind_param("i", $coupon_id);
                $stmt_check->execute();
                $result_check = $stmt_check->get_result();

                if ($result_check->num_rows > 0) {
                    throw new Exception("Диагноз и оплата для этого приема уже установлены.");
                }

                // Вставка в medical_card
                // Обратите внимание на порядок и количество параметров
                $sql_insert = "INSERT INTO medical_card (coupon_id, Date, Diagnosis, Amount, patient_id, payment_made, Description, Type) VALUES (?, ?, ?, ?, ?, 'нет', ?, ?)";
                $stmt_insert = $conn->prepare($sql_insert);
                
                if (!$stmt_insert) {
                    throw new Exception("Ошибка подготовки запроса вставки: " . htmlspecialchars($conn->error));
                }
                
                // Привязка параметров к запросу
                // Убедитесь, что типы соответствуют передаваемым переменным
                // Параметры: coupon_id (int), date (string), diagnosis (string), amount (float), patient_id (int), description (string), type (string)
                // Привязка параметров к запросу
                if (!$stmt_insert->bind_param("issdiss", 
                                              $coupon_id, 
                                              $date, 
                                              $diagnosis, 
                                              $amount, 
                                              $patient_id,
                                              $description,
                                              $type)) {
                    throw new Exception("Ошибка привязки параметров: " . htmlspecialchars($stmt_insert->error));
                }

                // Вставка данных
                if (!$stmt_insert->execute()) {
                    throw new Exception("Не удалось добавить запись в медицинскую карту: " . htmlspecialchars($stmt_insert->error));
                }

                // Фиксация транзакции
                $conn->commit();

                $_SESSION['success_message'] = "Диагноз и сумма успешно установлены.";
                header("location: dashboard.php");
                exit();
            } catch (Exception $e) {
                // Откат транзакции при ошибке
                $conn->rollback();
                $error = htmlspecialchars($e->getMessage());
            }
        }
    } else {
        // Если форма отправлена некорректно
        $error = "Некорректная отправка формы.";
    }
}

// Получение данных для отображения формы
$sql = "SELECT c.id, p.Name AS patient_name, p.Surname AS patient_surname, c.Date 
        FROM coupon c
        JOIN patient p ON c.patient_id = p.id
        WHERE c.id = ? AND c.doctor_id = ? 
        AND (c.Date < CURDATE() OR (c.Date = CURDATE() AND c.Time <= CURRENT_TIME()))
        AND NOT EXISTS (
            SELECT 1 FROM medical_card mc WHERE mc.coupon_id = c.id
        )";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Ошибка подготовки запроса: " . htmlspecialchars($conn->error));
}
$stmt->bind_param("ii", $coupon_id, $_SESSION["id"]);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = "Запись не найдена или уже обработана.";
    header("location: dashboard.php");
    exit();
}

$appointment = $result->fetch_assoc();
?>
<link rel="stylesheet" href="../styles.css">
<?php include '../templates/header.php'; ?>

<h2>Диагноз</h2>

<?php
if (isset($error)) {
    echo '<p style="color: red;">' . htmlspecialchars($error) . '</p>';
}
?>

<p><strong>Пациент:</strong> <?php echo htmlspecialchars($appointment['patient_name'] . ' ' . htmlspecialchars($appointment['patient_surname'])); ?></p>
<p><strong>Дата:</strong> <?php echo htmlspecialchars($appointment['Date']); ?></p>

<form action="diagnosis_payment.php" method="post">
    <input type="hidden" name="coupon_id" value="<?php echo htmlspecialchars($coupon_id); ?>">
    <div>
        <label for="diagnosis">Диагноз:</label>
        <input type="text" id="diagnosis" name="diagnosis" required>
    </div>
    <div>
        <label for="amount">Сумма за прием (&#8381;):</label>
        <input type="number" id="amount" name="amount" step="0.01" min="0" required>
    </div>
    <div>
        <label for="description">Описание:</label>
        <input type="text" id="description" name="description" required>
    </div>
    <div>
        <label for="type">Тип:</label>
        <input type="text" id="type" name="type" required>
    </div>
    <div>
        <input type="submit" value="Сохранить">
    </div>
</form>

<?php include '../templates/footer.php'; ?>

<?php
$conn->close();
?>
