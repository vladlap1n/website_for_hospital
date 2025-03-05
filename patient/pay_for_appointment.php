<?php
require_once '../includes/auth.php';
if ($_SESSION["role"] != 'patient') {
    header("location: ../login.php");
    exit();
}
require_once '../includes/config.php';

header('Content-Type: application/json; charset=utf-8'); 

$conn = connect_db();

$response = [
    'status' => 'error',
    'message' => 'Некорректный запрос.',
];

if (isset($_GET['medical_card_id'])) {
    $medical_card_id = $_GET['medical_card_id'];

    $sql_check = "SELECT COUNT(*) FROM Payment WHERE medical_card_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $medical_card_id);
    $stmt_check->execute();
    $stmt_check->bind_result($payment_count);
    $stmt_check->fetch();
    $stmt_check->close();

    if ($payment_count > 0) {
        $response['status'] = 'error';
        $response['message'] = 'Оплата уже была произведена для этой медицинской карты.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    $sql_amount = "SELECT Amount FROM Medical_card WHERE id = ?";
    $stmt_amount = $conn->prepare($sql_amount);
    $stmt_amount->bind_param("i", $medical_card_id);
    $stmt_amount->execute();
    $stmt_amount->bind_result($original_amount);
    $stmt_amount->fetch();
    $stmt_amount->close();


    $sql_discount = "SELECT Size FROM Discount WHERE patient_id = ?";
    $stmt_discount = $conn->prepare($sql_discount);
    $stmt_discount->bind_param("i", $_SESSION["id"]);
    $stmt_discount->execute();
    $stmt_discount->bind_result($discount_size);
    $stmt_discount->fetch();
    $stmt_discount->close();

    if (!empty($discount_size)) {
        $discounted_amount = $original_amount * (1 - ($discount_size / 100));
    } else {
        $discounted_amount = $original_amount;
    }

    $sql_payment = "INSERT INTO Payment (Amount, medical_card_id) VALUES (?, ?)";
    $stmt_payment = $conn->prepare($sql_payment);
    $stmt_payment->bind_param("di", $discounted_amount, $medical_card_id);
    
    if ($stmt_payment->execute()) {

        $sql_update_medical_card = "UPDATE Medical_card SET Amount = ? WHERE id = ?";
        $stmt_update_medical_card = $conn->prepare($sql_update_medical_card);
        $stmt_update_medical_card->bind_param("di", $discounted_amount, $medical_card_id);
        $stmt_update_medical_card->execute();
        $stmt_update_medical_card->close();

        $response['status'] = 'success';
        $response['message'] = 'Оплата успешно выполнена.';
        $response['discounted_amount'] = number_format($discounted_amount, 2);
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Ошибка при оплате.';
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit();
} else {
    // Если medical_card_id не передан, вернём ошибку
    $response['status'] = 'error';
    $response['message'] = 'Отсутствует параметр medical_card_id.';
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit();
}
?>