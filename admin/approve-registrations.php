<?php
require_once 'db_con.php';

if (!isset($_SESSION['user_login'])) {
    header('Location: login.php');
    exit();
}

$corepage = explode('/', $_SERVER['PHP_SELF']);
$corepage = end($corepage);
if ($corepage !== 'index.php') {
    $corepage = explode('.', $corepage);
    header('Location: index.php?page=' . $corepage[0]);
    exit();
}

if (isset($_POST['approve_submit']) && isset($_POST['student_id'])) {
    $id = base64_decode($_POST['student_id']);
    $section = $_POST['section'];

    $input_error = array();
    if (empty($section) || $section == "Select") {
        $input_error['section'] = "Please select a section.";
    }

    if (count($input_error) == 0) {
        $query = "SELECT * FROM pending_registrations WHERE id = ?";
        $stmt = mysqli_prepare($db_con, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_array($result);

        if ($row) {
            $student_id = $row['student_id'];
            $name = $row['name'];
            $grade_level = $row['grade_level'];
            $gender = $row['gender'];
            $address = $row['address'];
            $pcontact = $row['pcontact'];
            $date_of_birth = $row['date_of_birth'];
            $age = $row['age'];
            $academic_year = $row['academic_year'];
            $graduation_year = $row['graduation_year'];
            $photo = $row['photo'];
            $transcript = $row['transcript'];
            $birth_certificate = $row['birth_certificate'];

            $insert_query = "INSERT INTO student_info (student_id, name, class, gender, section, address, pcontact, date_of_birth, age, academic_year, graduation_year, photo, transcript, birth_certificate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $insert_stmt = mysqli_prepare($db_con, $insert_query);
            if ($insert_stmt === false) {
                $datainsert['inserterror'] = '<p style="color: red;">Database error: ' . mysqli_error($db_con) . '</p>';
            } else {
                mysqli_stmt_bind_param($insert_stmt, "sssssssssisiss", $student_id, $name, $grade_level, $gender, $section, $address, $pcontact, $date_of_birth, $age, $academic_year, $graduation_year, $photo, $transcript, $birth_certificate);
                if (mysqli_stmt_execute($insert_stmt)) {
                    $delete_query = "DELETE FROM pending_registrations WHERE id = ?";
                    $delete_stmt = mysqli_prepare($db_con, $delete_query);
                    mysqli_stmt_bind_param($delete_stmt, "i", $id);
                    mysqli_stmt_execute($delete_stmt);
                    mysqli_stmt_close($delete_stmt);

                    $datainsert['insertsuccess'] = '<p style="color: green;">Student Approved Successfully!</p>';
                } else {
                    $datainsert['inserterror'] = '<p style="color: red;">Approval failed: ' . mysqli_stmt_error($insert_stmt) . '</p>';
                }
                mysqli_stmt_close($insert_stmt);
            }
        }
        mysqli_stmt_close($stmt);
    }
}

if (isset($_POST['decline_submit']) && isset($_POST['student_id'])) {
    $id = base64_decode($_POST['student_id']);
    $query = "UPDATE pending_registrations SET status = 'declined' WHERE id = ?";
    $stmt = mysqli_prepare($db_con, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    if (mysqli_stmt_execute($stmt)) {
        $datainsert['declinesuccess'] = '<p style="color: orange;">Student Registration Declined!</p>';
    } else {
        $datainsert['declineerror'] = '<p style="color: red;">Decline failed: ' . mysqli_stmt_error($stmt) . '</p>';
    }
    mysqli_stmt_close($stmt);
}
?>

<h1 class="text-primary"><i class="fas fa-check"></i> Approve Registrations <small class="text-warning">Review New Students!</small></h1>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Approve Registrations</li>
    </ol>
</nav>

<?php if (isset($datainsert)) { ?>
    <div role="alert" aria-live="assertive" aria-atomic="true" class="toast fade show" data-autohide="true" data-animation="true" data-delay="2000">
        <div class="toast-header">
            <strong class="mr-auto">Approval Alert</strong>
            <small><?php echo date('d-M-Y'); ?></small>
            <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                <span aria-hidden="true">×</span>
            </button>
        </div>
        <div class="toast-body">
            <?php
            if (isset($datainsert['insertsuccess'])) echo $datainsert['insertsuccess'];
            if (isset($datainsert['inserterror'])) echo $datainsert['inserterror'];
            if (isset($datainsert['declinesuccess'])) echo $datainsert['declinesuccess'];
            if (isset($datainsert['declineerror'])) echo $datainsert['declineerror'];
            ?>
        </div>
    </div>
<?php } ?>

<?php if (isset($input_error['section'])) { ?>
    <div class="alert alert-danger"><?php echo $input_error['section']; ?></div>
<?php } ?>

<div class="row">
    <div class="col-sm-12">
        <table class="table table-striped table-hover table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Grade Level</th>
                    <th>Gender</th>
                    <th>Address</th>
                    <th>Contact</th>
                    <th>Date of Birth</th>
                    <th>Age</th>
                    <th>Academic Year</th>
                    <th>Graduation Year</th>
                    <th>Photo</th>
                    <th>Transcript</th>
                    <th>Birth Certificate</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT * FROM pending_registrations WHERE status = 'pending'";
                $result = mysqli_query($db_con, $query);
                while ($row = mysqli_fetch_array($result)) {
                    $photo = $row['photo'];
                    if (!empty($photo) && !file_exists('images/' . $photo)) {
                        $photo_files = glob('images/' . basename($photo) . '_*.*');
                        $photo = !empty($photo_files) ? basename($photo_files[0]) : $photo;
                    }
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['grade_level']); ?></td>
                        <td><?php echo htmlspecialchars($row['gender']); ?></td>
                        <td><?php echo htmlspecialchars($row['address']); ?></td>
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
                        <td><a href="view_transcript.php?file=<?php echo urlencode($row['transcript']); ?>" target="_blank">View</a></td>
                        <td><a href="view_birth_certificate.php?file=<?php echo urlencode($row['birth_certificate']); ?>" target="_blank">View</a></td>
                        <td>
                            <form method="POST" action="">
                                <input type="hidden" name="student_id" value="<?php echo base64_encode($row['id']); ?>">
                                <div class="form-group">
                                    <select name="section" class="form-control" required>
                                        <option value="">Select Section</option>
                                        <option value="A">A</option>
                                        <option value="B">B</option>
                                        <option value="C">C</option>
                                        <option value="D">D</option>
                                    </select>
                                </div>
                                <button type="submit" name="approve_submit" class="btn btn-success">Approve</button>
                                <button type="submit" name="decline_submit" class="btn btn-warning">Decline</button>
                            </form>
                            <?php
                            $check_query = "SELECT * FROM student_info WHERE student_id = ?";
                            $check_stmt = mysqli_prepare($db_con, $check_query);
                            mysqli_stmt_bind_param($check_stmt, "s", $row['student_id']);
                            mysqli_stmt_execute($check_stmt);
                            $check_result = mysqli_stmt_get_result($check_stmt);
                            if (mysqli_num_rows($check_result) > 0) {
                                echo '<a href="edit-progression.php?student_id=' . urlencode($row['student_id']) . '" class="btn btn-info mt-2">Edit Progression</a>';
                            }
                            mysqli_stmt_close($check_stmt);
                            ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>