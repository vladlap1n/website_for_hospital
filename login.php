<?php
require_once 'includes/config.php';
session_start();

// Инициализация переменных
$username = $password = "";
$username_err = $password_err = $login_err = "";

// Проверка наличия cookie (для автоподстановки логина)
if (isset($_COOKIE['username'])) {
    $username = $_COOKIE['username'];
}

// Обработка POST-запроса
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- СЕРВЕРНАЯ проверка на пустые поля ---
    if (empty(trim($_POST["username"]))) {
        $username_err = "Пожалуйста, введите логин (сервер).";
    } else {
        $username = trim($_POST["username"]);
    }

    if (empty(trim($_POST["password"]))) {
        $password_err = "Пожалуйста, введите пароль (сервер).";
    } else {
        $password = trim($_POST["password"]);
    }

    // Если серверная проверка не нашла ошибок в заполнении
    if (empty($username_err) && empty($password_err)) {
        $conn = connect_db();

        $roles = ['Patient', 'Admin', 'Doctor'];
        foreach ($roles as $role) {
            $sql = "SELECT id, Password FROM $role WHERE Username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                $stmt->bind_result($id, $stored_password);
                if ($stmt->fetch()) {
                    // Проверка пароля
                    if (password_verify($password, $stored_password)) {
                        // Успешный вход
                        $_SESSION["loggedin"] = true;
                        $_SESSION["id"] = $id;
                        $_SESSION["username"] = $username;    
                        $_SESSION["role"] = strtolower($role); 

                        // Ставим cookie на сутки
                        setcookie("username", htmlspecialchars($username), time() + 86400, "/");

                        header("location: " . strtolower($role) . "/dashboard.php");
                        exit();
                    } else {
                        // Ошибка при неверном пароле
                        $login_err = "Неверный пароль (сервер).";
                    }
                }
                break; 
            }
        }
        
        // Если не был найден ни один пользователь (и нет ошибки про пароль)
        if (empty($login_err)) {
            $login_err = "Нет пользователя с таким логином (сервер).";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <title>Вход</title>
    <link rel="stylesheet" href="styles.css">

    <!-- Клиентская валидация -->
    <script>
        function validateForm() {
            // Считываем введённые значения
            const username = document.forms["loginForm"]["username"].value;
            const password = document.forms["loginForm"]["password"].value;
            
            // Сброс ранее показанных сообщений (именно клиентских)
            document.getElementById("username_err_client").innerText = "";
            document.getElementById("password_err_client").innerText = "";

            let valid = true;

            // Проверка логина (клиент)
            if (username.trim() === "") {
                document.getElementById("username_err_client").innerText = "Пожалуйста, введите логин (клиент).";
                valid = false;
            }

            // Проверка пароля (клиент)
            if (password.trim() === "") {
                document.getElementById("password_err_client").innerText = "Пожалуйста, введите пароль (клиент).";
                valid = false;
            }

            // Если valid == false, форма не отправится
            return valid;
        }
    </script>
</head>
<body>
<?php include 'templates/header.php'; ?>

<h2>Вход</h2>

<!-- Отображаем серверные ошибки (если есть) -->
<?php 
if (!empty($login_err)) {
    echo '<div class="error">' . htmlspecialchars($login_err) . '</div>';
}
?>

<form id="loginForm" name="loginForm"
      action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"
      method="post"
      onsubmit="return validateForm();">
    
    <div>
        <label>Логин</label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>">
        
        <!-- Сообщения от сервера -->
        <span class="error">
            <?php echo htmlspecialchars($username_err); ?>
        </span>
        
        <!-- Сообщения от клиента -->
        <span class="error" id="username_err_client" style="color: blue;"></span>
    </div>

    <div>
        <label>Пароль</label>
        <input type="password" name="password">
        
        <!-- Сообщения от сервера -->
        <span class="error">
            <?php echo htmlspecialchars($password_err); ?>
        </span>
        
        <!-- Сообщения от клиента -->
        <span class="error" id="password_err_client" style="color: blue;"></span>
    </div>

    <div>
        <input type="submit" value="Войти">
    </div>
    <p>Нет аккаунта? <a href="register.php">Зарегистрироваться</a>.</p>
</form>

<?php include 'templates/footer.php'; ?>
</body>
</html>