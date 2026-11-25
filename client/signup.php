<?php
require_once '../admin/db_con.php';
session_start();

if (isset($_SESSION['client_login'])) {
    header('Location: dashboard.php');
    exit();
}

if (isset($_POST['signup'])) {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    $input_error = array();
    if (empty($name)) $input_error['name'] = "Name is required!";
    if (empty($username)) $input_error['username'] = "Username is required!";
    if (empty($email)) $input_error['email'] = "Email is required!";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $input_error['email'] = "Invalid email format!";
    if (empty($password)) $input_error['password'] = "Password is required!";
    if ($password !== $confirm_password) $input_error['confirm_password'] = "Passwords do not match!";

    if (count($input_error) == 0) {
        // Check for unique username
        $check_query = "SELECT username FROM users WHERE username = ?";
        $check_stmt = mysqli_prepare($db_con, $check_query);
        mysqli_stmt_bind_param($check_stmt, "s", $username);
        mysqli_stmt_execute($check_stmt);
        $result = mysqli_stmt_get_result($check_stmt);
        if (mysqli_num_rows($result) > 0) {
            $input_error['username'] = "Username already exists!";
        }
        mysqli_stmt_close($check_stmt);

        // Check for unique email
        $check_email_query = "SELECT email FROM users WHERE email = ?";
        $check_email_stmt = mysqli_prepare($db_con, $check_email_query);
        mysqli_stmt_bind_param($check_email_stmt, "s", $email);
        mysqli_stmt_execute($check_email_stmt);
        $email_result = mysqli_stmt_get_result($check_email_stmt);
        if (mysqli_num_rows($email_result) > 0) {
            $input_error['email'] = "Email already exists!";
        }
        mysqli_stmt_close($check_email_stmt);

        if (count($input_error) == 0) {
            $password_hash = sha1(md5($password)); // Match existing hash method
            $role = 'client';
            $query = "INSERT INTO users (name, username, email, password, role) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($db_con, $query);
            mysqli_stmt_bind_param($stmt, "sssss", $name, $username, $email, $password_hash, $role);
            if (mysqli_stmt_execute($stmt)) {
                $signup_success = "Registration successful! Please log in.";
                header('Location: login.php');
                exit();
            } else {
                $signup_error = "Registration failed: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Sign Up</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>
    <style>
        body {
            background: linear-gradient(135deg, #e0eafc, #cfdef3);
            font-family: 'Arial', sans-serif;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .signup-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        .signup-container h1 {
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
        }
        .form-group label {
            color: #2c3e50;
            font-weight: 500;
        }
        .form-control {
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .btn-success {
            background-color: #2c3e50;
            border: none;
            padding: 10px;
            width: 100%;
            transition: background-color 0.3s;
        }
        .btn-success:hover {
            background-color: #1a252f;
        }
        .alert {
            margin-top: 20px;
        }
        .link-text {
            text-align: center;
            margin-top: 15px;
        }
        .link-text a {
            color: #e74c3c;
            text-decoration: none;
        }
        .link-text a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <h1>Client Sign Up</h1>
        <?php if (isset($signup_success)) { ?>
            <div class="alert alert-success"><?php echo $signup_success; ?></div>
        <?php } ?>
        <?php if (isset($signup_error)) { ?>
            <div class="alert alert-danger"><?php echo $signup_error; ?></div>
        <?php } ?>
        <?php if (isset($input_error)) { ?>
            <div class="alert alert-danger">
                <?php foreach ($input_error as $error) echo $error . '<br>'; ?>
            </div>
        <?php } ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" name="name" class="form-control" id="name" required>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" class="form-control" id="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" class="form-control" id="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" class="form-control" id="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" id="confirm_password" required>
            </div>
            <button type="submit" name="signup" class="btn btn-success mt-3">Sign Up</button>
            <div class="link-text">Already have an account? <a href="login.php">Login</a></div>
        </form>
    </div>
    <script src="../js/jquery-3.5.1.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
</body>
</html>