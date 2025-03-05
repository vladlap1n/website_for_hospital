<?php
require_once 'includes/config.php';

if (isset($_POST['username'])) {
    $username = trim($_POST['username']);
    $conn = connect_db();
    $sql_check_username = "SELECT id FROM Patient WHERE Username = ?";
    if ($stmt_check_username = $conn->prepare($sql_check_username)) {
        $stmt_check_username->bind_param("s", $username);
        $stmt_check_username->execute();
        $stmt_check_username->store_result();
        echo json_encode($stmt_check_username->num_rows > 0);
        $stmt_check_username->close();
    }
    $conn->close();
}
?>
