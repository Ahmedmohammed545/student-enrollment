<?php
require_once 'db_con.php';

// Check if user is logged in
if (!isset($_SESSION['user_login'])) {
    header('Location: login.php');
    exit();
}

// Routing logic
$corepage = explode('/', $_SERVER['PHP_SELF']);
$corepage = end($corepage);
if ($corepage !== 'index.php') {
    $corepage = explode('.', $corepage);
    header('Location: index.php?page=' . $corepage[0]);
    exit();
}

$id = base64_decode($_GET['id']);

// Define absolute paths for uploads
$images_dir = __DIR__ . '/images';

// Ensure images directory exists
if (!is_dir($images_dir)) {
    mkdir($images_dir, 0777, true);
}

if (isset($_POST['userupdate'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    $input_error = array();

    if (empty($name)) {
        $input_error['name'] = "Name is required!";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $input_error['email'] = "Valid email is required!";
    }

    if (count($input_error) == 0) {
        $query = "UPDATE `users` SET `name`=?, `email`=? WHERE `id`=?";
        $stmt = mysqli_prepare($db_con, $query);
        mysqli_stmt_bind_param($stmt, "ssi", $name, $email, $id);
        if (mysqli_stmt_execute($stmt)) {
            $datainsert['insertsuccess'] = '<p style="color: green;">User Updated!</p>';
            header('Location: index.php?page=user-profile&edit=success');
            exit();
        } else {
            $datainsert['inserterror'] = '<p style="color: red;">Update failed: ' . mysqli_stmt_error($stmt) . '</p>';
        }
        mysqli_stmt_close($stmt);
    }
}

$query = "SELECT `name`, `email` FROM `users` WHERE `id`=?";
$stmt = mysqli_prepare($db_con, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_array($result);
mysqli_stmt_close($stmt);
?>

<h1 class="text-primary"><i class="fas fa-user-plus"></i> Edit User Information! <small class="text-warning">Edit User!</small></h1>
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="index.php?page=user-profile">User Profile</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit Profile</li>
  </ol>
</nav>

<div class="row">
  <div class="col-sm-6">
    <?php if (isset($datainsert)) { ?>
    <div role="alert" aria-live="assertive" aria-atomic="true" class="toast fade show" data-autohide="true" data-animation="true" data-delay="2000">
      <div class="toast-header">
        <strong class="mr-auto">User Update Alert</strong>
        <small><?php echo date('d-M-Y'); ?></small>
        <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="toast-body">
        <?php 
          if (isset($datainsert['insertsuccess'])) {
            echo $datainsert['insertsuccess'];
          }
          if (isset($datainsert['inserterror'])) {
            echo $datainsert['inserterror'];
          }
        ?>
      </div>
    </div>
    <?php } ?>

    <?php if (isset($input_error)) { ?>
      <div class="alert alert-danger">
        <?php foreach ($input_error as $error) { echo $error . '<br>'; } ?>
      </div>
    <?php } ?>

    <form method="POST" action="">
      <div class="form-group">
        <label for="name">Full Name</label>
        <input name="name" type="text" class="form-control" id="name" value="<?= isset($row['name']) ? htmlspecialchars($row['name']) : ''; ?>" required>
        <?php echo isset($input_error['name']) ? '<label class="text-danger">' . $input_error['name'] . '</label>' : ''; ?>
      </div>
      <div class="form-group">
        <label for="email">Email</label>
        <input name="email" type="email" class="form-control" id="email" value="<?= isset($row['email']) ? htmlspecialchars($row['email']) : ''; ?>" required>
        <?php echo isset($input_error['email']) ? '<label class="text-danger">' . $input_error['email'] . '</label>' : ''; ?>
      </div>
      <div class="form-group text-center">
        <input name="userupdate" value="Update Profile" type="submit" class="btn btn-danger">
      </div>
    </form>
  </div>
</div>