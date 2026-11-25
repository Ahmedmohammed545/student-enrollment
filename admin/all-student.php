<?php
require_once 'db_con.php';

// Check if user is logged in
if (!isset($_SESSION['user_login'])) {
    header('Location: login.php');
    exit();
}

// Student filter
$filter_column = isset($_GET['filter_column']) ? $_GET['filter_column'] : '';
$filter_value = isset($_GET['filter_value']) ? $_GET['filter_value'] : '';
$query = "SELECT * FROM student_info ORDER BY datetime DESC";
if (!empty($filter_column) && !empty($filter_value)) {
    $query = "SELECT * FROM student_info WHERE `$filter_column` LIKE ? ORDER BY datetime DESC";
    $stmt = mysqli_prepare($db_con, $query);
    $filter_value = "%$filter_value%";
    mysqli_stmt_bind_param($stmt, "s", $filter_value);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($db_con, $query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Students</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f1f3f5;
            font-family: 'Poppins', sans-serif;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        }
        .card-header {
            background: linear-gradient(135deg, #5f2985, #7b3aa8);
            color: white;
            border-radius: 10px 10px 0 0;
            padding: 15px;
        }
        .table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .thead-dark {
            background: #5f2985;
            color: white;
        }
        .btn-primary, .btn-secondary {
            border-radius: 50px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1 class="text-primary"><i class="fas fa-users"></i> All Students <small class="text-warning">All Students List!</small></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">All Students</li>
            </ol>
        </nav>

        <?php if (isset($_GET['delete']) || isset($_GET['edit'])) { ?>
            <div role="alert" aria-live="assertive" aria-atomic="true" class="toast fade show" data-autohide="true" data-animation="true" data-delay="2000">
                <div class="toast-header">
                    <strong class="mr-auto">Student Insert Alert</strong>
                    <small><?php echo date('d-M-Y'); ?></small>
                    <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="toast-body">
                    <?php 
                    if (isset($_GET['delete'])) {
                        if ($_GET['delete'] == 'success') {
                            echo "<p style='color: green; font-weight: bold;'>Student Deleted Successfully!</p>";
                        } elseif ($_GET['delete'] == 'error') {
                            echo "<p style='color: red; font-weight: bold;'>Student Not Deleted!</p>";
                        }  
                    }
                    if (isset($_GET['edit'])) {
                        if ($_GET['edit'] == 'success') {
                            echo "<p style='color: green; font-weight: bold;'>Student Edited Successfully!</p>";
                        } elseif ($_GET['edit'] == 'error') {
                            echo "<p style='color: red; font-weight: bold;'>Student Not Edited!</p>";
                        }  
                    }
                    ?>
                </div>
            </div>
        <?php } ?>

        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-filter"></i> Filter Students
            </div>
            <div class="card-body">
                <form method="GET" action="" class="mb-3">
                    <div class="row">
                        <div class="col-md-4">
                            <select name="filter_column" class="form-control">
                                <option value="">Select Column</option>
                                <option value="student_id" <?php echo $filter_column == 'student_id' ? 'selected' : ''; ?>>Student ID</option>
                                <option value="name" <?php echo $filter_column == 'name' ? 'selected' : ''; ?>>Name</option>
                                <option value="class" <?php echo $filter_column == 'class' ? 'selected' : ''; ?>>Grade Level</option>
                                <option value="gender" <?php echo $filter_column == 'gender' ? 'selected' : ''; ?>>Gender</option>
                                <option value="section" <?php echo $filter_column == 'section' ? 'selected' : ''; ?>>Section</option>
                                <option value="address" <?php echo $filter_column == 'address' ? 'selected' : ''; ?>>Address</option>
                                <option value="pcontact" <?php echo $filter_column == 'pcontact' ? 'selected' : ''; ?>>Contact</option>
                                <option value="date_of_birth" <?php echo $filter_column == 'date_of_birth' ? 'selected' : ''; ?>>Date of Birth</option>
                                <option value="age" <?php echo $filter_column == 'age' ? 'selected' : ''; ?>>Age</option>
                                <option value="academic_year" <?php echo $filter_column == 'academic_year' ? 'selected' : ''; ?>>Academic Year</option>
                                <option value="graduation_year" <?php echo $filter_column == 'graduation_year' ? 'selected' : ''; ?>>Graduation Year</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="filter_value" class="form-control" placeholder="Enter value to filter" value="<?php echo htmlspecialchars($filter_value); ?>">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="index.php?page=all-student" class="btn btn-secondary">Clear Filter</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <i class="fas fa-users"></i> Students List
            </div>
            <div class="card-body">
                <table class="table table-striped table-hover table-bordered" id="data">
                    <thead class="thead-dark">
                        <tr>
                            <th scope="col">SL</th>
                            <th scope="col">Student ID</th>
                            <th scope="col">Name</th>
                            <th scope="col">Grade Level</th>
                            <th scope="col">Gender</th>
                            <th scope="col">Section</th>
                            <th scope="col">Address</th>
                            <th scope="col">Contact</th>
                            <th scope="col">Date of Birth</th>
                            <th scope="col">Age</th>
                            <th scope="col">Academic Year</th>
                            <th scope="col">Graduation Year</th>
                            <th scope="col">Photo</th>
                            <th scope="col">Transcript</th>
                            <th scope="col">Birth Certificate</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        while ($row = mysqli_fetch_array($result)) {
                            $photo = $row['photo'];
                            $transcript = $row['transcript'];
                            $birth_certificate = $row['birth_certificate'];
                            if (!empty($photo) && !file_exists('images/' . $photo)) {
                                $photo_files = glob('images/' . basename($photo) . '_*.*');
                                $photo = !empty($photo_files) ? basename($photo_files[0]) : $photo;
                            }
                            if (!empty($transcript) && !file_exists('transcripts/' . $transcript)) {
                                $transcript_files = glob('transcripts/' . basename($transcript) . '_*.*');
                                $transcript = !empty($transcript_files) ? basename($transcript_files[0]) : $transcript;
                            }
                            if (!empty($birth_certificate) && !file_exists('birth_certificates/' . $birth_certificate)) {
                                $birth_cert_files = glob('birth_certificates/' . basename($birth_certificate) . '_*.*');
                                $birth_certificate = !empty($birth_cert_files) ? basename($birth_cert_files[0]) : $birth_certificate;
                            }
                            ?>
                            <tr>
                                <td><?php echo $i; ?></td>
                                <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                                <td><?php echo ucwords(htmlspecialchars($row['name'])); ?></td>
                                <td><?php echo htmlspecialchars($row['class']); ?></td>
                                <td><?php echo htmlspecialchars($row['gender']); ?></td>
                                <td><?php echo htmlspecialchars($row['section']); ?></td>
                                <td><?php echo ucwords(htmlspecialchars($row['address'])); ?></td>
                                <td><?php echo htmlspecialchars($row['pcontact']); ?></td>
                                <td><?php echo htmlspecialchars($row['date_of_birth']); ?></td>
                                <td><?php echo htmlspecialchars($row['age']); ?></td>
                                <td><?php echo htmlspecialchars($row['academic_year']); ?></td>
                                <td><?php echo htmlspecialchars($row['graduation_year']); ?></td>
                                <td>
                                    <?php if (empty($photo)) { ?>
                                        <span style="color: red;">No photo</span>
                                    <?php } else { ?>
                                        <img src="images/<?php echo htmlspecialchars($photo); ?>" height="50px" onerror="this.onerror=null; this.src='images/default.jpg';">
                                    <?php } ?>
                                </td>
                                <td>
                                    <?php if (empty($transcript) || !file_exists('transcripts/' . $transcript)) { ?>
                                        <span style="color: red;">Transcript not found (<?php echo htmlspecialchars($transcript); ?>)</span>
                                    <?php } else { ?>
                                        <a href="view_transcript.php?file=<?php echo urlencode($transcript); ?>" target="_blank">View Transcript</a>
                                    <?php } ?>
                                </td>
                                <td>
                                    <?php if (empty($birth_certificate) || !file_exists('birth_certificates/' . $birth_certificate)) { ?>
                                        <span style="color: red;">Birth Certificate not found (<?php echo htmlspecialchars($birth_certificate); ?>)</span>
                                    <?php } else { ?>
                                        <a href="view_birth_certificate.php?file=<?php echo urlencode($birth_certificate); ?>" target="_blank">View Birth Certificate</a>
                                    <?php } ?>
                                </td>
                                <td>
                                    <a class="btn btn-xs btn-warning" href="index.php?page=editstudent&id=<?php echo base64_encode($row['id']); ?>&photo=<?php echo base64_encode($row['photo']); ?>">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <a class="btn btn-xs btn-danger" onclick="javascript:confirmationDelete($(this));return false;" href="index.php?page=delete&id=<?php echo base64_encode($row['id']); ?>&photo=<?php echo base64_encode($row['photo']); ?>">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php $i++; } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="js/jquery-3.5.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script>
        function confirmationDelete(anchor) {
            var conf = confirm('Are you sure want to delete this record?');
            if (conf)
                window.location = anchor.attr("href");
        }
    </script>
</body>
</html>