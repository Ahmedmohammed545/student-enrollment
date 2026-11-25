<?php
require_once '../admin/db_con.php';
session_start();

// If already logged in and session is valid, redirect to dashboard
if (isset($_SESSION['client_login'])) {
    header('Location: dashboard.php');
    exit();
}

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $input_error = array();
    if (empty($username)) $input_error['username'] = "Username is required!";
    if (empty($password)) $input_error['password'] = "Password is required!";

    if (count($input_error) == 0) {
        // First, verify the user credentials
        $query = "SELECT * FROM users WHERE username = ? AND role = 'client' LIMIT 1";
        $stmt = mysqli_prepare($db_con, $query);
        
        if ($stmt === false) {
            die("Prepare failed: " . mysqli_error($db_con));
        }
        
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($user) {
            // Verify password (using your existing hashing method)
            $stored_hash = $user['password'];
            $input_hash = sha1(md5($password));
            
            if ($stored_hash === $input_hash) {
                // Login successful - set session variables
                $_SESSION['client_login'] = $user['username'];
                $_SESSION['client_id'] = $user['id'];
                
                // Redirect to dashboard
                header('Location: dashboard.php');
                exit();
            } else {
                $login_error = "Invalid username or password!";
            }
        } else {
            $login_error = "Invalid username or password!";
        }
    }
}
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
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
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        .login-container h1 {
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
        .btn-primary {
            background-color: #2c3e50;
            border: none;
            padding: 10px;
            width: 100%;
            transition: background-color 0.3s;
        }
        .btn-primary:hover {
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
    <div class="login-container">
        <h1>Client Login</h1>
        <?php if (isset($login_error)) { ?>
            <div class="alert alert-danger"><?php echo $login_error; ?></div>
        <?php } ?>
        <?php if (isset($input_error)) { ?>
            <div class="alert alert-danger">
                <?php foreach ($input_error as $error) echo $error . '<br>'; ?>
            </div>
        <?php } ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" class="form-control" id="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" class="form-control" id="password" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary mt-3">Login</button>
            <div class="link-text">Don't have an account? <a href="signup.php">Sign Up</a></div>
        </form>
    </div>
    <script src="../js/jquery-3.5.1.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
  </body>
</html>