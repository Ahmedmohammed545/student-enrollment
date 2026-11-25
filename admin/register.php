<?php
require_once 'db_con.php';
session_start();

if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $c_password = $_POST['c_password'];

    $input_error = array();

    if (empty($name)) {
        $input_error['name'] = "The Name Field is Required";
    }
    if (empty($email)) {
        $input_error['email'] = "The Email Field is Required";
    }
    if (empty($username)) {
        $input_error['username'] = "The Username Field is Required";
    }
    if (empty($password)) {
        $input_error['password'] = "The Password Field is Required";
    }
    if (empty($_FILES['photo']['name'])) {
        $input_error['photo'] = "The Photo Field is Required";
    }
    if (!empty($password) && $c_password !== $password) {
        $input_error['notmatch'] = "Passwords do not match!";
    }

    if (count($input_error) == 0) {
        // Validate photo
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $photo = $_FILES['photo']['name'];
        $photo_ext = strtolower(pathinfo($photo, PATHINFO_EXTENSION));
        $photo_name = $username . '.' . $photo_ext;

        if (!in_array($photo_ext, $allowed_extensions)) {
            $input_error['photo'] = "Only JPG, JPEG, PNG, or GIF files are allowed.";
        }

        // Check if email exists
        if (!isset($input_error['photo'])) {
            $query = "SELECT * FROM users WHERE email = ?";
            $stmt = mysqli_prepare($db_con, $query);
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if (mysqli_num_rows($result) > 0) {
                $email_error = "This email already exists!";
            }
            mysqli_stmt_close($stmt);
        }

        // Check if username exists
        if (!isset($email_error) && !isset($input_error['photo'])) {
            $query = "SELECT * FROM users WHERE username = ?";
            $stmt = mysqli_prepare($db_con, $query);
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if (mysqli_num_rows($result) > 0) {
                $username_error = "This username already exists!";
            }
            mysqli_stmt_close($stmt);
        }

        // Validate and insert
        if (!isset($username_error) && !isset($email_error) && !isset($input_error['photo'])) {
            if (strlen($username) <= 7) {
                $usernamelan = "Username must be more than 7 characters!";
            } elseif (strlen($password) <= 7) {
                $passlan = "Password must be more than 7 characters!";
            } else {
                $hashed_password = sha1(md5($password));
                $query = "INSERT INTO users (name, email, username, password, photo, status) VALUES (?, ?, ?, ?, ?, 'active')";
                $stmt = mysqli_prepare($db_con, $query);
                mysqli_stmt_bind_param($stmt, "sssss", $name, $email, $username, $hashed_password, $photo_name);
                if (mysqli_stmt_execute($stmt)) {
                    move_uploaded_file($_FILES['photo']['tmp_name'], 'images/' . $photo_name);
                    $success = "Registration successful! Please log in.";
                } else {
                    $error = "Registration failed: " . mysqli_stmt_error($stmt);
                }
                mysqli_stmt_close($stmt);
            }
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.0.0/animate.min.css"/>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
    <title>Register</title>
  </head>
  <body>
    <div class="container"><br>
      <h1 class="text-center">Register Users!</h1><hr><br>
      <div class="d-flex justify-content-center">
          <?php if (isset($success)) { ?>
              <div role="alert" class="toast alert alert-success fade hide" data-delay="2000"><?php echo $success; ?></div>
          <?php } ?>
          <?php if (isset($error)) { ?>
              <div role="alert" class="toast alert alert-danger fade hide" data-delay="2000"><?php echo $error; ?></div>
          <?php } ?>
          <?php if (isset($username_error)) { ?>
              <div role="alert" class="toast alert alert-danger fade hide" data-delay="2000"><?php echo $username_error; ?></div>
          <?php } ?>
          <?php if (isset($email_error)) { ?>
              <div role="alert" class="toast alert alert-danger fade hide" data-delay="2000"><?php echo $email_error; ?></div>
          <?php } ?>
          <?php if (isset($usernamelan)) { ?>
              <div role="alert" class="toast alert alert-danger fade hide" data-delay="2000"><?php echo $usernamelan; ?></div>
          <?php } ?>
          <?php if (isset($passlan)) { ?>
              <div role="alert" class="toast alert alert-danger fade hide" data-delay="2000"><?php echo $passlan; ?></div>
          <?php } ?>
          <?php if (isset($input_error['photo'])) { ?>
              <div role="alert" class="toast alert alert-danger fade hide" data-delay="2000"><?php echo $input_error['photo']; ?></div>
          <?php } ?>
      </div>
      <div class="row animate__animated animate__pulse">
        <div class="col-md-4 offset-md-4">
          <form method="POST" enctype="multipart/form-data">
            <div class="form-group row">
              <div class="col-sm-12">
                <input type="text" class="form-control" name="name" value="<?= isset($name) ? htmlspecialchars($name) : '' ?>" placeholder="Name">
                <?= isset($input_error['name']) ? '<label class="text-danger">' . $input_error['name'] . '</label>' : '' ?>
              </div>
            </div>
            <div class="form-group row">
              <div class="col-sm-12">
                <input type="email" class="form-control" name="email" value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" placeholder="Email">
                <?= isset($input_error['email']) ? '<label class="text-danger">' . $input_error['email'] . '</label>' : '' ?>
              </div>
            </div>
            <div class="form-group row">
              <div class="col-sm-12">
                <input type="text" class="form-control" name="username" value="<?= isset($username) ? htmlspecialchars($username) : '' ?>" placeholder="Username">
                <?= isset($input_error['username']) ? '<label class="text-danger">' . $input_error['username'] . '</label>' : '' ?>
              </div>
            </div>
            <div class="form-group row">
              <div class="col-sm-12">
                <input type="password" name="password" class="form-control" placeholder="Password">
                <?= isset($input_error['password']) ? '<label class="text-danger">' . $input_error['password'] . '</label>' : '' ?>
                <?= isset($passlan) ? '<label class="text-danger">' . $passlan . '</label>' : '' ?>
              </div>
            </div>
            <div class="form-group row">
              <div class="col-sm-12">
                <input type="password" name="c_password" class="form-control" placeholder="Confirm Password">
                <?= isset($input_error['notmatch']) ? '<label class="text-danger">' . $input_error['notmatch'] . '</label>' : '' ?>
              </div>
            </div>
            <div class="form-group row">
              <div class="col-sm-12">
                <label for="photo">Choose your photo</label>
                <input type="file" id="photo" name="photo" class="form-control">
                <?= isset($input_error['photo']) ? '<label class="text-danger">' . $input_error['photo'] . '</label>' : '' ?>
              </div>
            </div>
            <div class="text-center">
                <button type="submit" name="register" class="btn btn-danger">Register!</button>
            </div>
          </form>
        </div>
      </div>
      <p>If you have an account, you can <a href="login.php">Login</a> your account!</p>
    </div>
    <script src="../js/jquery-3.5.1.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script type="text/javascript">
        $('.toast').toast('show');
    </script>
  </body>
</html>