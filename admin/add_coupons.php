<?php
require_once '../includes/auth.php';
if ($_SESSION["role"] != 'admin') {
    header("location: ../login.php");
    exit();
}
require_once '../includes/config.php';

$conn = connect_db();

if (isset($_GET['doctor'])) {
    $selected_doctor_id = intval($_GET['doctor']);
} else {
    header("location: select_doctor.php"); 
    exit();
}

// Получаем информацию о враче
$doctor_sql = "SELECT Name, Surname FROM Doctor WHERE id = ?";
$doctor_stmt = $conn->prepare($doctor_sql);
$doctor_stmt->bind_param("i", $selected_doctor_id);
$doctor_stmt->execute();
$doctor_result = $doctor_stmt->get_result();
$doctor_info = $doctor_result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_coupon'])) {
    $doctor_id = intval($_POST['doctor']);
    $date      = $_POST['date'];
    $time      = $_POST['time'];
    $office    = trim($_POST['office']); 

    // Проверяем, существует ли уже талон на эту дату и время
    $check_sql = "SELECT * FROM coupon WHERE doctor_id = ? AND Date = ? AND Time = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("iss", $doctor_id, $date, $time);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo "Талон на это время уже существует.";
    } else {
        // Проверяем интервал в 15 минут
        $interval_check_sql = "
            SELECT * FROM coupon 
            WHERE doctor_id = ? 
              AND Date = ?
              AND (
                  TIME_TO_SEC(TIMEDIFF(Time, ?)) BETWEEN 0 AND 900
                  OR TIME_TO_SEC(TIMEDIFF(?, Time)) BETWEEN 0 AND 900
              )
        ";
        $interval_check_stmt = $conn->prepare($interval_check_sql);
        $interval_check_stmt->bind_param("isss", $doctor_id, $date, $time, $time);
        $interval_check_stmt->execute();
        $interval_check_result = $interval_check_stmt->get_result();

        if ($interval_check_result->num_rows > 0) {
            echo "Талон должен иметь интервал минимум 15 минут.";
        } else {
            // Вставляем новый талон в базу данных
            $insert_sql = "INSERT INTO coupon (Date, Time, doctor_id, Office) VALUES (?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            if ($insert_stmt === false) {
                die("Ошибка подготовки запроса: " . htmlspecialchars($conn->error));
            }

            $insert_stmt->bind_param("ssis", $date, $time, $doctor_id, $office);
            if ($insert_stmt->execute()) {
                echo "Талон успешно добавлен.";
            } else {
                echo "Ошибка: " . htmlspecialchars($insert_stmt->error);
            }
        }
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['automatic'])) {
    if (!empty($_POST['start_time']) && !empty($_POST['end_time']) && !empty($_POST['office'])) {
        // Получаем значения из формы
        $start_time = $_POST['start_time'];
        $end_time   = $_POST['end_time'];
        $office_auto = trim($_POST['office']);

        // Преобразуем в объекты DateTime
        $start_datetime = new DateTime($start_time);
        $end_datetime   = new DateTime($end_time);

        // Если вдруг конечное время раньше начального, выдадим предупреждение
        if ($end_datetime <= $start_datetime) {
            echo "<p>Время окончания не может быть раньше или равно времени начала!</p>";
        } else {
            // Готовим INSERT один раз за пределами цикла
            $insert_sql = "INSERT INTO coupon (Date, Time, doctor_id, Office) VALUES (?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            if ($insert_stmt === false) {
                die("Ошибка подготовки запроса: " . htmlspecialchars($conn->error));
            }

            // Генерируем интервалы в 15 минут
            while ($start_datetime < $end_datetime) {
                $date_for_db = $start_datetime->format('Y-m-d');
                $time_for_db = $start_datetime->format('H:i:s');

                // Сначала проверяем, есть ли уже талон на это время
                $check_sql = "SELECT * FROM coupon WHERE doctor_id = ? AND Date = ? AND Time = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("iss", $selected_doctor_id, $date_for_db, $time_for_db);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();

                // Если уже существует — пропускаем
                if ($check_result->num_rows > 0) {
                    $start_datetime->modify('+15 minutes');
                    continue;
                }

                // Иначе вставляем новый талон
                // Привязываем параметры: (string Date, string Time, int doctor_id, string Office)
                $insert_stmt->bind_param("ssis", $date_for_db, $time_for_db, $selected_doctor_id, $office_auto);

                if ($insert_stmt->execute()) {
                    echo "Талон успешно добавлен: {$date_for_db} {$time_for_db} [Кабинет: {$office_auto}]<br>";
                } else {
                    echo "Ошибка: " . htmlspecialchars($insert_stmt->error) . "<br>";
                }

                // Смещаемся на +15 минут
                $start_datetime->modify('+15 minutes');
            }

            echo "<p>Автоматическое добавление талонов завершено.</p>";
        }
    } else {
        echo "<p>Пожалуйста, укажите время начала, время окончания и кабинет.</p>";
    }
}

$coupons = [];

if (isset($_GET['filter_date']) && !empty($_GET['filter_date'])) {
    $filter_date = $_GET['filter_date'];
    // Выбираем только талоны на эту дату
    $coupon_sql = "SELECT Date, Time, Office FROM coupon WHERE doctor_id = ? AND Date = ?";
    $coupon_stmt = $conn->prepare($coupon_sql);
    $coupon_stmt->bind_param("is", $selected_doctor_id, $filter_date);
} else {
    // Без фильтра: все талоны врача
    $coupon_sql = "SELECT Date, Time, Office FROM coupon WHERE doctor_id = ?";
    $coupon_stmt = $conn->prepare($coupon_sql);
    $coupon_stmt->bind_param("i", $selected_doctor_id);
}

$coupon_stmt->execute();
$coupon_result = $coupon_stmt->get_result();

while ($row = $coupon_result->fetch_assoc()) {
    $coupons[] = $row;
}
?>

<?php include '../templates/header.php'; ?>
<link rel="stylesheet" href="../styles.css">
<h2>Добавить талон врачу: <?php echo htmlspecialchars($doctor_info['Name'] . ' ' . $doctor_info['Surname']); ?></h2>

<!-- Форма для выбора даты, чтобы показать только талоны на эту дату -->
<form action="add_coupons.php" method="get">
    <input type="hidden" name="doctor" value="<?php echo htmlspecialchars($selected_doctor_id); ?>">
    <label>Показать талоны на дату:</label>
    <input type="date" name="filter_date" value="<?php echo isset($filter_date) ? htmlspecialchars($filter_date) : ''; ?>">
    <button type="submit">Показать</button>
    <?php if (isset($filter_date) && !empty($filter_date)): ?>
        <!-- Кнопка сброса фильтра; возвращает нас к показу всех талонов -->
        <a href="add_coupons.php?doctor=<?php echo htmlspecialchars($selected_doctor_id); ?>">Показать все</a>
    <?php endif; ?>
</form>

<h3>Существующие талоны<?php if (isset($filter_date) && !empty($filter_date)) echo " на ".htmlspecialchars($filter_date); ?></h3>
<table border="1">
    <tr>
        <th>Дата</th>
        <th>Время</th>
        <th>Кабинет</th>
    </tr>
    <?php if (empty($coupons)): ?>
        <tr>
            <td colspan="3">У этого врача пока нет записей.</td>
        </tr>
    <?php else: ?>
        <?php foreach ($coupons as $coupon): ?>
            <tr>
                <td><?php echo htmlspecialchars($coupon['Date']); ?></td>
                <td><?php echo htmlspecialchars($coupon['Time']); ?></td>
                <td><?php echo htmlspecialchars($coupon['Office']); ?></td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>

<hr>

<!-- Блок для ручного добавления талона -->
<h3>Добавить новый талон (вручную)</h3>
<form action="add_coupons.php?doctor=<?php echo htmlspecialchars($selected_doctor_id); ?>" method="post">
    <input type="hidden" name="add_coupon" value="1">
    <input type="hidden" name="doctor" value="<?php echo htmlspecialchars($selected_doctor_id); ?>">
    
    <div>
        <label>Дата:</label>
        <input type="date" name="date" required>
    </div>
    
    <div>
        <label>Время:</label>
        <input type="time" name="time" required>
    </div>

    <div>
        <label>Кабинет:</label>
        <input type="text" name="office" required>
    </div>

    <div>
        <input type="submit" value="Добавить Талон">
    </div>
</form>

<hr>

<!-- Блок для автоматического создания талонов -->
<h3>Автоматическое создание талонов</h3>
<form action="" method="post">
    <input type="hidden" name="doctor" value="<?php echo htmlspecialchars($selected_doctor_id); ?>">
    
    <div>
        <label>Время начала:</label>
        <input type="datetime-local" name="start_time" required>
    </div>

    <div>
        <label>Время окончания:</label>
        <input type="datetime-local" name="end_time" required>
    </div>

    <div>
        <label>Кабинет:</label>
        <input type="text" name="office" required placeholder="Введите номер кабинета">
    </div>

    <div>
        <input type="submit" name="automatic" value="Добавить Талоны Автоматически">
    </div>
</form>

<?php include '../templates/footer.php'; ?>