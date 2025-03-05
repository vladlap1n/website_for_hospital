<link rel="stylesheet" href="../styles.css"> 
<?php
require_once '../includes/auth.php';
require_once '../includes/config.php'; 


if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$conn = connect_db();


if ($_SESSION["role"] != 'patient') {
    header("location: ../login.php");
    exit();
}

include '../templates/header.php';


if (isset($_SESSION['success_message'])) {
    echo '<p style="color: green;">' . htmlspecialchars($_SESSION['success_message']) . '</p>';
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    echo '<p style="color: red;">' . htmlspecialchars($_SESSION['error_message']) . '</p>';
    unset($_SESSION['error_message']);
}


$query = "SELECT DISTINCT Specialization FROM doctor";
$result = $conn->query($query);
$specializations = $result->fetch_all(MYSQLI_ASSOC);


$query_all_doctors = "SELECT * FROM doctor";
$result_all_doctors = $conn->query($query_all_doctors);
$all_doctors = $result_all_doctors->fetch_all(MYSQLI_ASSOC);


$selected_specialization = isset($_POST['specialization']) ? $_POST['specialization'] : '';
$doctors = [];

if ($selected_specialization) {
    $query = "SELECT * FROM doctor WHERE Specialization = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $selected_specialization); 
    $stmt->execute();
    
    $result = $stmt->get_result();
    $doctors = $result->fetch_all(MYSQLI_ASSOC);
}

if (isset($_POST['doctor']) && !isset($_POST['coupon'])) {
    $doctor_id = intval($_POST['doctor']);
    

    $coupons_query = "
        SELECT id, Date, Time 
        FROM coupon 
        WHERE doctor_id = ? 
          AND patient_id IS NULL 
          AND (Date > CURDATE() OR (Date = CURDATE() AND Time > CURRENT_TIME()))
    ";
    $coupons_stmt = $conn->prepare($coupons_query);
    $coupons_stmt->bind_param("i", $doctor_id);
    $coupons_stmt->execute();
    
    $coupons_result = $coupons_stmt->get_result();


    $doctor_sql = "SELECT Name, Surname FROM doctor WHERE id = ?";
    $doctor_stmt = $conn->prepare($doctor_sql);
    $doctor_stmt->bind_param("i", $doctor_id);
    $doctor_stmt->execute();
    $doctor_info_result = $doctor_stmt->get_result();
    $doctor_info = $doctor_info_result->fetch_assoc();

    echo '<h2>Доступные купоны для ' . htmlspecialchars($doctor_info['Name'] . ' ' . $doctor_info['Surname']) . '</h2>';
    
    if ($coupons_result->num_rows > 0) {
        echo '<form action="book_appointment.php" method="post">';
        echo '<input type="hidden" name="doctor" value="' . htmlspecialchars($doctor_id) . '">';
        
        echo '<select id="coupon" name="coupon" required>';
        echo '<option value="">Выберите время</option>';
        while ($coupon = $coupons_result->fetch_assoc()) {
            echo '<option value="' . htmlspecialchars($coupon['id']) . '">'
                 . htmlspecialchars($coupon['Date'] . ' ' . $coupon['Time']) 
                 . '</option>';
        }
        echo '</select>';
        
        echo '<input type="submit" value="Записаться на прием">';
        echo '</form>';
        echo '<form action="../patient/book_appointment.php" method="post">';
        echo '<input type="submit" value="Назад">';
        echo '</form>';
        
    } else {
        echo "<p>У этого врача нет доступных купонов.</p>";
        echo '<form action="../patient/book_appointment.php" method="post">';
        echo '<input type="submit" value="Назад">';
        echo '</form>';
    }

} elseif (isset($_POST['coupon'])) {
    if (isset($_POST['doctor']) && isset($_POST['coupon'])) {
        $selected_coupon_id = intval($_POST['coupon']);
        $patient_id = $_SESSION["id"]; 

        $conn->query("LOCK TABLES coupon WRITE");

        $check_sql = "SELECT patient_id FROM coupon WHERE id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $selected_coupon_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $check_data = $check_result->fetch_assoc();
        sleep(10);
        if ($check_data && is_null($check_data['patient_id'])) {
            $update_sql = "UPDATE coupon SET patient_id = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ii", $patient_id, $selected_coupon_id);
            $update_stmt->execute();

            if ($update_stmt->affected_rows > 0) {
            
                $_SESSION['success_message'] = "Вы успешно записаны на прием.";
            
                $conn->query("UNLOCK TABLES");
                header("location: ../index.php"); 
                exit();
            } else {
    
                $_SESSION['error_message'] = "Данное время уже занято. Пожалуйста, выберите другое.";
                $conn->query("UNLOCK TABLES");
                header("location: ../patient/book_appointment.php");
                exit();
            }
        } else {
            $_SESSION['error_message'] = "Данное время уже занято. Пожалуйста, выберите другое.";
            $conn->query("UNLOCK TABLES");
            header("location: ../patient/book_appointment.php");
            exit();
        }
    }
} else {
?>
<link rel="stylesheet" href="../styles.css"> 
    <h2>Запись на прием</h2>
    <form action="book_appointment.php" method="post">
        <div>
            <label>Специализация врача</label>
            <select id="specialization" name="specialization" required onchange="this.form.submit()">
                <option value="">Выберите специализацию</option>
                <?php
                foreach ($specializations as $spec) {
                    echo '<option value="' . htmlspecialchars($spec['Specialization']) . '"'
                         . ($selected_specialization === $spec['Specialization'] ? ' selected' : '') . '>'
                         . htmlspecialchars($spec['Specialization']) . '</option>';
                }
                ?>
            </select>
        </div>
    </form>
    
    <?php if (!empty($doctors)): ?>
    <h2>Выберите врача</h2>
    <form action="book_appointment.php" method="post">
        <input type="hidden" name="specialization" value="<?php echo htmlspecialchars($selected_specialization); ?>">
        
        <select id="doctor" name="doctor" required onchange="this.form.submit()">
            <option value="">Выберите врача</option>
            <?php foreach ($doctors as $doctor): ?>
                <option value="<?php echo htmlspecialchars($doctor['id']); ?>">
                    <?php echo htmlspecialchars($doctor['Surname'] . ' ' . $doctor['Name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
    <?php endif; ?>
    
    <h2>Все врачи и их специализации</h2>
    <table>
        <thead>
            <tr>
                <th>Фамилия</th>
                <th>Имя</th>
                <th>Специализация</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($all_doctors as $doctor) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($doctor['Surname']) . '</td>';
                echo '<td>' . htmlspecialchars($doctor['Name']) . '</td>';
                echo '<td>' . htmlspecialchars($doctor['Specialization']) . '</td>';
                echo '</tr>';
            }
            ?>
        </tbody>
    </table>
    
<?php
}
include '../templates/footer.php';
$conn->close();
?> 