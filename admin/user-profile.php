<?php
require_once 'db_con.php';

// Check if user is logged in
if (!isset($_SESSION['user_login'])) {
    header('Location: login.php');
    exit();
}

$user = $_SESSION['user_login'];

// Routing logic
$corepage = explode('/', $_SERVER['PHP_SELF']);
$corepage = end($corepage);
if ($corepage !== 'index.php') {
    if ($corepage == $corepage) {
        $corepage = explode('.', $corepage);
        header('Location: index.php?page=' . $corepage[0]);
        exit();
    }
}
?>

<h1 class="text-primary"><i class="fas fa-user"></i> User Profile</h1>
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item" aria-current="page"><a href="index.php">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">User Profile</li>
  </ol>
</nav>

<?php
$query = "SELECT * FROM users WHERE username = ?";
$stmt = mysqli_prepare($db_con, $query);
mysqli_stmt_bind_param($stmt, "s", $user);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_array($result);
mysqli_stmt_close($stmt);

if (!$row) {
    echo "<div class='alert alert-danger'>User not found!</div>";
    exit();
}
?>

<div class="row">
  <div class="col-sm-6">
    <table class="table table-bordered">
      <tr>
        <td>User ID</td>
        <td><?php echo htmlspecialchars($row['id']); ?></td>
      </tr>
      <tr>
        <td>Name</td>
        <td><?php echo ucwords(htmlspecialchars($row['name'])); ?></td>
      </tr>
      <tr>
        <td>Email</td>
        <td><?php echo htmlspecialchars($row['email']); ?></td>
      </tr>
      <tr>
        <td>Username</td>
        <td><?php echo ucwords(htmlspecialchars($row['username'])); ?></td>
      </tr>
      <tr>
        <td>Status</td>
        <td><?php echo ucwords(htmlspecialchars($row['status'])); ?></td>
      </tr>
      <tr>
        <td>Register Date</td>
        <td><?php echo htmlspecialchars($row['datetime']); ?></td>
      </tr>
    </table>
    <a class="btn btn-warning pull-right" href="index.php?page=edit-user&id=<?php echo base64_encode($row['id']); ?>">Edit Profile</a>
  </div>
  <div class="col-sm-6">
    <h3>Profile Picture</h3>
    <a href="images/<?php echo htmlspecialchars($row['photo']); ?>">
      <img class="img-thumbnail" id="imguser" src="images/<?php echo htmlspecialchars($row['photo']); ?>" width="200px">
    </a>
    <?php
    if (isset($_POST['upphoto'])) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $photo = $_FILES['userphoto']['name'];
        $photo_ext = strtolower(pathinfo($photo, PATHINFO_EXTENSION));
        $photo_name = $user . '-' . date('YmdHis') . '.' . $photo_ext;

        if (!in_array($photo_ext, $allowed_extensions)) {
            echo "<div class='alert alert-danger'>Only JPG, JPEG, PNG, or GIF files are allowed.</div>";
        } else {
            // Delete old photo if it exists
            if (!empty($row['photo']) && file_exists('images/' . $row['photo'])) {
                unlink('images/' . $row['photo']);
            }
            // Update with new photo
            $query = "UPDATE users SET photo = ? WHERE username = ?";
            $stmt = mysqli_prepare($db_con, $query);
            mysqli_stmt_bind_param($stmt, "ss", $photo_name, $user);
            if (mysqli_stmt_execute($stmt)) {
                if (move_uploaded_file($_FILES['userphoto']['tmp_name'], 'images/' . $photo_name)) {
                    echo "<div class='alert alert-success'>Profile Picture Updated Successfully!</div>";
                    // Refresh the page to show the new photo
                    header("Location: index.php?page=user-profile");
                    exit();
                } else {
                    echo "<div class='alert alert-danger'>Failed to upload the photo to the server.</div>";
                }
            } else {
                echo "<div class='alert alert-danger'>Failed to update profile picture: " . mysqli_stmt_error($stmt) . "</div>";
            }
            mysqli_stmt_close($stmt);
        }
    }
    ?>
    <br>
    <form method="POST" enctype="multipart/form-data">
      <input type="file" name="userphoto" required="" id="photo"><br>
      <input class="btn btn-info" type="submit" name="upphoto" value="Upload Photo">
    </form>
  </div>
</div>