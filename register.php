<?php
require_once 'includes/config.php';

$name = $surname = $patronymic = $age = $phone = $address = $username = $password = $confirm_password = "";
$name_err = $surname_err = $username_err = $password_err = $confirm_password_err = $phone_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate name
    if (empty(trim($_POST["name"]))) {
        $name_err = "Пожалуйста, введите имя.";
    } else {
        $name = trim($_POST["name"]);
    }

    // Validate surname
    if (empty(trim($_POST["surname"]))) {
        $surname_err = "Пожалуйста, введите фамилию.";
    } else {
        $surname = trim($_POST["surname"]);
    }

    // Validate username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Пожалуйста, введите логин.";
    } else {
        $username = trim($_POST["username"]);
        
        // Check if username already exists in patient, admin, or doctor tables
        $conn = connect_db();
        $sql_check_username = "SELECT id FROM Patient WHERE Username = ? 
                                UNION 
                                SELECT id FROM admin WHERE Username = ? 
                                UNION 
                                SELECT id FROM doctor WHERE Username = ?";
        
        if ($stmt_check_username = $conn->prepare($sql_check_username)) {
            // Bind parameters for all three checks
            $stmt_check_username->bind_param("sss", $username, $username, $username);
            $stmt_check_username->execute();
            $stmt_check_username->store_result();

            if ($stmt_check_username->num_rows > 0) {
                $username_err = "Данный логин занят. Попробуйте другой.";
            }
            $stmt_check_username->close();
        }
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Пожалуйста, введите пароль.";     
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Пароль должен содержать не менее 6 символов.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Пожалуйста, подтвердите пароль.";     
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password !== $confirm_password)) {
            $confirm_password_err = "Пароли не совпадают.";
        }
    }

    // Validate phone number
    if (empty(trim($_POST["phone"]))) {
        $phone_err = "Пожалуйста, введите номер телефона.";
    } else {
        $phone = trim($_POST["phone"]);
        
        // Check if phone number already exists
        $sql_check_phone = "SELECT id FROM Patient WHERE PhoneNumber = ?";
        if ($stmt_check_phone = $conn->prepare($sql_check_phone)) {
            $stmt_check_phone->bind_param("s", $phone);
            $stmt_check_phone->execute();
            $stmt_check_phone->store_result();

            if ($stmt_check_phone->num_rows > 0) {
                $phone_err = "Данный номер телефона уже зарегистрирован. Попробуйте другой.";
            }
            $stmt_check_phone->close();
        }
    }

    // If no errors, proceed to insert into database
    if (empty($name_err) && empty($surname_err) && empty($username_err) && empty($password_err) && empty($confirm_password_err) && empty($phone_err)) {

        // Insert new user into Patient table
        $sql_insert = "INSERT INTO Patient (Name, Surname, Patronymic, Age, PhoneNumber, Address, Username, Password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        if ($stmt_insert = $conn->prepare($sql_insert)) {
            // Hash the password
            $password_hashed = password_hash($password, PASSWORD_DEFAULT);

            // Bind parameters
            $stmt_insert->bind_param("sssissss", 
                $param_name, 
                $param_surname, 
                $param_patronymic,
                $param_age,
                $param_phone,
                $param_address,
                $param_username,
                $param_password
            );

            // Set parameters
            $param_name=$name;
            $param_surname=$surname;
            $param_patronymic=trim($_POST["patronymic"]);
            
            if (!empty(trim($_POST["age"]))) {
                $param_age=intval(trim($_POST["age"]));
            } else {
                $param_age=null;
            }
            
            // Set other parameters
            $param_phone=trim($_POST['phone']);
            $param_address=trim($_POST['address']);
            $param_username=trim($_POST['username']);
            $param_password=$password_hashed;

            // Attempt to execute the prepared statement
            if ($stmt_insert->execute()) {

                
                header("location: login.php");
                exit();
            } else {
                echo "Что-то пошло не так. Попробуйте позже.";
            }

            // Close statement
            stmt_insert.close();
        }

        // Close connection
        conn.close();
    }
}
?>
<head>
    <link rel="stylesheet" href="styles.css">
</head>

<?php include 'templates/header.php'; ?>


<h2>Регистрация Пациента</h2>
<form id="registerForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div>
        <label>Имя</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
        <span class="error"><?php echo htmlspecialchars($name_err); ?></span>
    </div>    
    <div>
        <label>Фамилия</label>
        <input type="text" name="surname" value="<?php echo htmlspecialchars($surname); ?>" required>
        <span class="error"><?php echo htmlspecialchars($surname_err); ?></span>
    </div>
    <div>
        <label>Отчество</label>
        <input type="text" name="patronymic" value="<?php echo htmlspecialchars($patronymic); ?>">
    </div>
    <div>
        <label>Возраст</label>
        <input type="number" name="age" value="<?php echo htmlspecialchars($age); ?>">
    </div>
    <div>
        <label>Телефон</label>
        <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
        <span class="error"><?php echo htmlspecialchars($phone_err); ?></span>
    </div>
    <div>
        <label>Адрес</label>
        <input type="text" name="address" value="<?php echo htmlspecialchars($address); ?>">
    </div>
    <div>
       <label>Логин</label> 
       <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($username); ?>" required>
       <span class="error"><?php echo htmlspecialchars($username_err); ?></span>
   </div>
   <div>
       <label>Пароль</label>
       <input type="password" name="password" required>
       <span class="error"><?php echo htmlspecialchars($password_err); ?></span>
   </div>
   <div>
       <label>Подтвердите пароль</label>
       <input type="password" name="confirm_password" required>
       <span class="error"><?php echo htmlspecialchars($confirm_password_err); ?></span>
   </div>
   <div>
       <input type="submit" value="Зарегистрироваться">
   </div>
   <p>Уже есть аккаунт? <a href="login.php">Войти</a>.</p>
</form>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<?php include 'templates/footer.php'; ?>
