<?php
require_once '../includes/auth.php';
if ($_SESSION["role"] != 'admin') {
    header("location: ../login.php");
    exit();
}
require_once '../includes/config.php';

$conn = connect_db();
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_doctor'])) {
    $name = trim($_POST['name']);
    $surname = trim($_POST['surname']);
    $patronymic = trim($_POST['patronymic']);
    $age = intval($_POST['age']);
    $specialization = trim($_POST['specialization']);
    $category = intval($_POST['category']);
    $username = trim($_POST['username']); 
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    $sql = "INSERT INTO Doctor (Name, Surname, Patronymic, Age, Specialization, Category, Username, Password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssisiss", $name, $surname, $patronymic, $age, $specialization, $category, $username, $password);
    if ($stmt->execute()) {
        header("location: manage_doctors.php");
        exit();
    } else {
        echo "Ошибка: " . $stmt->error;
    }
    $stmt->close();
}
$sql = "SELECT id, Name, Surname, Patronymic, Age, Specialization, Category FROM Doctor";
$result = $conn->query($sql);
?>
<link rel="stylesheet" href="../styles.css">
<?php include '../templates/header.php'; ?>
<h2>Управление Врачами</h2>

<h3>Добавить Врача</h3>
<form action="manage_doctors.php" method="post">
    <input type="hidden" name="add_doctor" value="1">
    <div>
        <label>Имя</label>
        <input type="text" name="name" required>
    </div>
    <div>
        <label>Фамилия</label>
        <input type="text" name="surname" required>
    </div>
    <div>
        <label>Отчество</label>
        <input type="text" name="patronymic">
    </div>
    <div>
        <label>Возраст</label>
        <input type="number" name="age" required>
    </div>
    <div>
        <label>Специализация</label>
        <input type="text" name="specialization" required>
    </div>
    <div>
        <label>Категория</label>
        <select name="category" required>
            <option value="1">1-я категория</option>
            <option value="2">2-я категория</option>
            <option value="3">3-я категория</option>
        </select>
    </div>
    <div>
        <label>Логин</label> <!-- Поле для ввода логина -->
        <input type="text" name="username" required>
    </div>
    <div>
        <label>Пароль</label>
        <input type="password" name="password" required>
    </div>
    <div>
        <input type="submit" value="Добавить Врача">
    </div>
</form>

<h3>Список Врачей</h3>
<table border="1">
    <tr>
        <th>Имя</th>
        <th>Фамилия</th>
        <th>Отчество</th>
        <th>Возраст</th>
        <th>Специализация</th>
        <th>Категория</th>
        <th>Действия</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['Name']); ?></td>
            <td><?php echo htmlspecialchars($row['Surname']); ?></td>
            <td><?php echo htmlspecialchars($row['Patronymic']); ?></td>
            <td><?php echo htmlspecialchars($row['Age']); ?></td>
            <td><?php echo htmlspecialchars($row['Specialization']); ?></td>
            <td><?php echo htmlspecialchars($row['Category']); ?></td>
            <td>
                <!-- Добавьте ссылки для редактирования и удаления врачей -->
                <a href="edit_doctor.php?id=<?php echo $row['id']; ?>">Редактировать</a> | 
                <a href="delete_doctor.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Вы уверены?')">Удалить</a>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

<?php include '../templates/footer.php'; ?>
