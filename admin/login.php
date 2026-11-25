<?php
require_once 'db_con.php';
session_start();

if (isset($_SESSION['user_login'])) {
    header('Location: index.php');
    exit();
}

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $input_arr = array();
    if (empty($username)) {
        $input_arr['input_user_error'] = "Username is required!";
    }
    if (empty($password)) {
        $input_arr['input_pass_error'] = "Password is required!";
    }

    if (count($input_arr) == 0) {
        $query = "SELECT * FROM users WHERE username = ? AND role = 'admin'";
        $stmt = mysqli_prepare($db_con, $query);
        if ($stmt === false) {
            die("Prepare failed: " . mysqli_error($db_con));
        }

        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result === false) {
            die("Query failed: " . mysqli_error($db_con));
        }

        if (mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);
            if ($row['password'] == sha1(md5($password))) {
                if ($row['status'] == 'active') {
                    $_SESSION['user_login'] = $username;
                    header('Location: index.php');
                    exit();
                } else {
                    $status_inactive = "Your status is inactive, please contact admin or support!";
                }
            } else {
                $worngpass = "This password is wrong!";
            }
        } else {
            $usernameerr = "Username not found or not an admin!";
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.0.0/animate.min.css"/>
    <link rel="stylesheet" type="text="text/css" href="../css/style.css">
    <title>Login</title>
    <style>
        .container {
            margin-top: 175px; /* Top margin of 175px */
        }
        .login-form {
            background-color: #f3e5f5; /* Light purple background */
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border: 2px solid #6a1b9a; /* Dark purple border */
        }
        .login-form h1 {
            color: #6a1b9a; /* Dark purple heading */
            font-weight: bold;
            margin-bottom: 20px;
        }
        .login-form .form-group label {
            color: #6a1b9a; /* Dark purple labels */
            font-weight: 600;
        }
        .login-form .form-control {
            border: 2px solid #ab47bc; /* Medium purple border */
            border-radius: 5px;
            background-color: #fff;
            transition: border-color 0.3s ease;
        }
        .login-form .form-control:focus {
            border-color: #6a1b9a; /* Dark purple on focus */
            box-shadow: 0 0 5px rgba(106, 27, 154, 0.5);
        }
        .login-form .btn-warning {
            background-color: #6a1b9a; /* Dark purple button */
            border-color: #6a1b9a;
            font-weight: bold;
            transition: background-color 0.3s ease;
            color: #fff; /* White text for contrast */
        }
        .login-form .btn-warning:hover {
            background-color: #ab47bc; /* Lighter purple on hover */
            border-color: #ab47bc;
        }
        .login-form .text-danger {
            color: #d81b60; /* Pinkish-red for input error messages */
            font-size: 0.9em;
        }
        .toast.alert-danger {
            background-color: #d81b60; /* Pinkish-red for toast alerts */
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            font-size: 1em;
        }
        .animate__animated.animate__pulse {
            animation-duration: 1s;
        }
    </style>
  </head>
  <body>
    <div class="container">
          <h1 class="text-center">Login Users!</h1><hr><br>
          <div class="d-flex justify-content-center">
          	<?php if(isset($usernameerr)){ ?> <div role="alert" aria-live="assertive" aria-atomic="true" align="center" class="toast alert alert-danger fade hide" data-delay="2000"><?php echo $usernameerr; ?></div><?php } ?>
          	<?php if(isset($worngpass)){ ?> <div role="alert" aria-live="assertive" aria-atomic="true" align="center" class="toast alert alert-danger fade hide" data-delay="2000"><?php echo $worngpass; ?></div><?php } ?>
          	<?php if(isset($status_inactive)){ ?> <div role="alert" aria-live="assertive" aria-atomic="true" align="center" class="toast alert alert-danger fade hide" data-delay="2000"><?php echo $status_inactive; ?></div><?php } ?>
          </div>
          <div class="row animate__animated animate__pulse">
            <div class="col-md-4 offset-md-4">
             	<form method="POST" action="" class="login-form">
				  <div class="form-group row">
				    <div class="col-sm-12">
				      <input type="text" class="form-control" name="username" value="<?= isset($username) ? htmlspecialchars($username) : ''; ?>" placeholder="Username" id="inputEmail3">
				      <?php echo isset($input_arr['input_user_error']) ? '<label class="text-danger">' . $input_arr['input_user_error'] . '</label>' : ''; ?>
				    </div>
				  </div>
				  <div class="form-group row">
				    <div class="col-sm-12">
				      <input type="password" name="password" class="form-control" id="inputPassword3" placeholder="Password">
				      <?php echo isset($input_arr['input_pass_error']) ? '<label class="text-danger">' . $input_arr['input_pass_error'] . '</label>' : ''; ?>
				    </div>
				  </div>
				  <div class="text-center">
				      <button type="submit" name="login" class="btn btn-warning">Sign in</button>
				  </div>
				</form>
            </div>
          </div>
    </div>

    <script src="../js/jquery-3.5.1.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script type="text/javascript">
    	$('.toast').toast('show');
    </script>
  </body>
</html>