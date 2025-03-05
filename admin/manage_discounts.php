<?php
require_once '../includes/auth.php';
if ($_SESSION["role"] != 'admin') {
    header("location: ../login.php");
    exit();
}
require_once '../includes/config.php';

$conn = connect_db();

// Обработка запроса на добавление скидки
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_discount'])) {
    $size = intval($_POST['size']);
    $patient_id = intval($_POST['patient_id']);

    // Проверка, что размер скидки положительный
    if ($size > 0 && $patient_id > 0) {
        $sql = "INSERT INTO discount (Size, Date, patient_id) VALUES (?, NOW(), ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $size, $patient_id);
        
        if ($stmt->execute()) {
            header("location: manage_discounts.php");
            exit();
        } else {
            echo "Ошибка при добавлении скидки: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Обработка запроса на удаление скидки
if (isset($_GET['delete'])) {
    $discount_id = intval($_GET['delete']);
    $sql = "DELETE FROM discount WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $discount_id);
        
        if ($stmt->execute()) {
            header("location: manage_discounts.php");
            exit();
        } else {
            echo "Ошибка при удалении скидки: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Поиск пациента по имени или фамилии
$patients = [];
if (isset($_POST['search_patient'])) {
    $search_term = trim($_POST['search_term']);
    $sql = "SELECT id, Name, Surname FROM patient WHERE Name LIKE ? OR Surname LIKE ?";
    if ($stmt = $conn->prepare($sql)) {
        $like_term = "%" . $search_term . "%";
        $stmt->bind_param("ss", $like_term, $like_term);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $patients[] = $row;
        }
        
        $stmt->close();
    }
}

// Получение всех скидок
$sql = "SELECT d.id, d.Size, d.Date, p.Name, p.Surname FROM discount d JOIN patient p ON d.patient_id = p.id";
$result_discounts = $conn->query($sql);
?>
<link rel="stylesheet" href="../styles.css">
<?php include '../templates/header.php'; ?>
<h2>Управление Скидками</h2>

<h3>Выдать Скидку</h3>
<form action="manage_discounts.php" method="post">
    <div>
        <label>Поиск Пациента</label>
        <input type="text" name="search_term" required>
        <input type="submit" name="search_patient" value="Найти">
    </div>
</form>

<?php if (!empty($patients)): ?>
    <h4>Результаты поиска:</h4>
    <ul>
        <?php foreach ($patients as $patient): ?>
            <li>
                <?php echo htmlspecialchars($patient['Name'] . ' ' . $patient['Surname']); ?>
                <form action="manage_discounts.php" method="post" style="display:inline;">
                    <input type="hidden" name="patient_id" value="<?php echo htmlspecialchars($patient['id']); ?>">
                    <input type="number" name="size" placeholder="Размер скидки (%)" required min="1">
                    <input type="submit" name="add_discount" value="Выдать Скидку">
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<h3>Существующие Скидки</h3>
<table border="1">
    <tr>
        <th>ID</th>
        <th>Пациент</th>
        <th>Размер (%)</th>
        <th>Дата</th>
        <th>Действия</th>
    </tr>
    <?php while ($row_discount = $result_discounts->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row_discount['id']); ?></td>
            <td><?php echo htmlspecialchars($row_discount['Name'] . ' ' . $row_discount['Surname']); ?></td>
            <td><?php echo htmlspecialchars($row_discount['Size']); ?></td>
            <td><?php echo htmlspecialchars($row_discount['Date']); ?></td>
            <td>
                <a href="?delete=<?php echo htmlspecialchars($row_discount['id']); ?>" onclick="return confirm('Вы уверены?')">Удалить</a>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

<?php include '../templates/footer.php'; ?>
