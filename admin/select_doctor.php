<?php
require_once '../includes/auth.php';
if ($_SESSION["role"] != 'admin') {
    header("location: ../login.php");
    exit();
}
require_once '../includes/config.php';

$conn = connect_db();
$sql = "SELECT id, Name, Surname, Patronymic, Specialization, Category FROM Doctor";
$result_doctors = $conn->query($sql);
?>

<?php include '../templates/header.php'; ?>
<link rel="stylesheet" href="../styles.css">
<h2>Выберите Врача для Добавления Талонов</h2>

<table border="1">
    <tr>
        <th>Имя</th>
        <th>Фамилия</th>
        <th>Отчество</th>
        <th>Специализация</th>
        <th>Категория</th>
        <th>Действия</th>
    </tr>
    <?php while ($doctor = $result_doctors->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($doctor['Name']); ?></td>
            <td><?php echo htmlspecialchars($doctor['Surname']); ?></td>
            <td><?php echo htmlspecialchars($doctor['Patronymic']); ?></td>
            <td><?php echo htmlspecialchars($doctor['Specialization']); ?></td>
            <td><?php echo htmlspecialchars($doctor['Category']); ?></td>
            <td>
                <a href="add_coupons.php?doctor=<?php echo htmlspecialchars($doctor['id']); ?>">Добавить Талон</a>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

<?php include '../templates/footer.php'; ?>
