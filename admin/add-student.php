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

// Define absolute paths for uploads
$images_dir = __DIR__ . '/images';
$transcripts_dir = __DIR__ . '/transcripts';
$birth_certificates_dir = __DIR__ . '/birth_certificates';

// Ensure directories exist, create them if they don't
if (!is_dir($images_dir)) {
    if (!mkdir($images_dir, 0777, true)) {
        die('Failed to create images directory: ' . $images_dir);
    }
}
if (!is_dir($transcripts_dir)) {
    if (!mkdir($transcripts_dir, 0777, true)) {
        die('Failed to create transcripts directory: ' . $transcripts_dir);
    }
}
if (!is_dir($birth_certificates_dir)) {
    if (!mkdir($birth_certificates_dir, 0777, true)) {
        die('Failed to create birth certificates directory: ' . $birth_certificates_dir);
    }
}

if (isset($_POST['addstudent'])) {
    $name = trim($_POST['name']);
    $address = trim($_POST['address']);
    $class = $_POST['class'];
    $section = $_POST['section'];
    $pcontact = $_POST['pcontact'];
    $date_of_birth = $_POST['date_of_birth'];
    $academic_year = $_POST['academic_year'];

    $input_error = array();

    // Validate inputs
    if (empty($name)) {
        $input_error['name'] = "Student Name is required!";
    }
    if (empty($address)) {
        $input_error['address'] = "Student Address is required!";
    }
    if (empty($class) || $class == "Select") {
        $input_error['class'] = "Student Class is required!";
    }
    if (empty($section) || $section == "Select") {
        $input_error['section'] = "Student Section is required!";
    }
    if (empty($pcontact)) {
        $input_error['pcontact'] = "Parent Contact is required!";
    }
    if (empty($date_of_birth)) {
        $input_error['date_of_birth'] = "Date of Birth is required!";
    }
    if (empty($academic_year)) {
        $input_error['academic_year'] = "Academic Year is required!";
    }
    if (empty($_FILES['photo']['name'])) {
        $input_error['photo'] = "Student Photo is required!";
    }
    if (empty($_FILES['transcript']['name'])) {
        $input_error['transcript'] = "Student Transcript is required!";
    }
    if (empty($_FILES['birth_certificate']['name'])) {
        $input_error['birth_certificate'] = "Birth Certificate is required!";
    }

    // Check class capacity
    if (count($input_error) == 0) {
        $capacity_query = "SELECT capacity FROM class_capacities WHERE class = ? AND section = ?";
        $capacity_stmt = mysqli_prepare($db_con, $capacity_query);
        mysqli_stmt_bind_param($capacity_stmt, "ss", $class, $section);
        mysqli_stmt_execute($capacity_stmt);
        $capacity_result = mysqli_stmt_get_result($capacity_stmt);
        $capacity_row = mysqli_fetch_assoc($capacity_result);
        $capacity = $capacity_row['capacity'] ?? 25;
        mysqli_stmt_close($capacity_stmt);

        $count_query = "SELECT COUNT(*) as count FROM student_info WHERE class = ? AND section = ?";
        $count_stmt = mysqli_prepare($db_con, $count_query);
        mysqli_stmt_bind_param($count_stmt, "ss", $class, $section);
        mysqli_stmt_execute($count_stmt);
        $count_result = mysqli_stmt_get_result($count_stmt);
        $count_row = mysqli_fetch_assoc($count_result);
        $current_count = $count_row['count'];
        mysqli_stmt_close($count_stmt);

        if ($current_count >= $capacity) {
            $input_error['capacity'] = "Class $class $section has reached its capacity of $capacity students!";
        }
    }

    if (count($input_error) == 0) {
        // Calculate age
        $dob = new DateTime($date_of_birth);
        $today = new DateTime();
        $age = $today->diff($dob)->y;
        
        // Calculate graduation year
        $grade_map = ['1st' => 12, '2nd' => 11, '3rd' => 10, '4th' => 9, '5th' => 8, 
                     '6th' => 7, '7th' => 6, '8th' => 5, '9th' => 4, '10th' => 3, 
                     '11th' => 2, '12th' => 1];
        $years_to_grad = $grade_map[$class];
        $start_year = (int)explode('-', $academic_year)[0];
        $graduation_year = $start_year + $years_to_grad;

        // Generate student ID
        do {
            $random_digits = sprintf("%04d", rand(0, 9999));
            $student_id = "LIA" . $random_digits;
            $check_query = "SELECT student_id FROM student_info WHERE student_id = ?";
            $check_stmt = mysqli_prepare($db_con, $check_query);
            mysqli_stmt_bind_param($check_stmt, "s", $student_id);
            mysqli_stmt_execute($check_stmt);
            $result = mysqli_stmt_get_result($check_stmt);
            $exists = mysqli_num_rows($result) > 0;
            mysqli_stmt_close($check_stmt);
        } while ($exists);

        // Validate and process photo
        $allowed_photo_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $photo_ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $photo = time() . '_' . rand(1000, 9999) . '.' . $photo_ext;
        if (!in_array($photo_ext, $allowed_photo_extensions)) {
            $input_error['photo'] = "Only JPG, JPEG, PNG, or GIF files are allowed for the photo.";
        }

        // Validate and process transcript
        $allowed_transcript_extensions = ['pdf'];
        $transcript_ext = strtolower(pathinfo($_FILES['transcript']['name'], PATHINFO_EXTENSION));
        $transcript = time() . '_' . rand(1000, 9999) . '_transcript.' . $transcript_ext;
        if (!in_array($transcript_ext, $allowed_transcript_extensions)) {
            $input_error['transcript'] = "Only PDF files are allowed for the transcript.";
        }

        // Validate and process birth certificate
        $allowed_birth_cert_extensions = ['pdf'];
        $birth_cert_ext = strtolower(pathinfo($_FILES['birth_certificate']['name'], PATHINFO_EXTENSION));
        $birth_certificate = time() . '_' . rand(1000, 9999) . '_birth_certificate.' . $birth_cert_ext;
        if (!in_array($birth_cert_ext, $allowed_birth_cert_extensions)) {
            $input_error['birth_certificate'] = "Only PDF files are allowed for the birth certificate.";
        }

        if (count($input_error) == 0) {
            // Insert into database using prepared statement
            $query = "INSERT INTO student_info (student_id, name, class, section, address, pcontact, 
                     date_of_birth, age, academic_year, graduation_year, photo, transcript, birth_certificate) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($db_con, $query);
            if ($stmt === false) {
                $datainsert['inserterror'] = '<p style="color: red;">Database error: ' . mysqli_error($db_con) . '</p>';
            } else {
                mysqli_stmt_bind_param($stmt, "sssssssiissss", $student_id, $name, $class, $section, $address, 
                                      $pcontact, $date_of_birth, $age, $academic_year, $graduation_year, 
                                      $photo, $transcript, $birth_certificate);
                if (mysqli_stmt_execute($stmt)) {
                    // Move uploaded files
                    $photo_path = $images_dir . DIRECTORY_SEPARATOR . $photo;
                    $transcript_path = $transcripts_dir . DIRECTORY_SEPARATOR . $transcript;
                    $birth_cert_path = $birth_certificates_dir . DIRECTORY_SEPARATOR . $birth_certificate;
                    
                    $upload_success = true;
                    
                    if (!move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path)) {
                        $datainsert['inserterror'] = '<p style="color: red;">Failed to move photo to images directory!</p>';
                        $upload_success = false;
                    }
                    
                    if (!move_uploaded_file($_FILES['transcript']['tmp_name'], $transcript_path)) {
                        $datainsert['inserterror'] = '<p style="color: red;">Failed to move transcript to transcripts directory!</p>';
                        $upload_success = false;
                    }
                    
                    if (!move_uploaded_file($_FILES['birth_certificate']['tmp_name'], $birth_cert_path)) {
                        $datainsert['inserterror'] = '<p style="color: red;">Failed to move birth certificate to birth certificates directory!</p>';
                        $upload_success = false;
                    }
                    
                    if ($upload_success) {
                        $datainsert['insertsuccess'] = '<p style="color: green;">Student Inserted Successfully!</p>';
                    } else {
                        // Delete database entry if file uploads failed
                        $delete_query = "DELETE FROM student_info WHERE student_id = ?";
                        $delete_stmt = mysqli_prepare($db_con, $delete_query);
                        mysqli_stmt_bind_param($delete_stmt, "s", $student_id);
                        mysqli_stmt_execute($delete_stmt);
                        mysqli_stmt_close($delete_stmt);
                    }
                } else {
                    $datainsert['inserterror'] = '<p style="color: red;">Student Not Inserted: ' . mysqli_stmt_error($stmt) . '</p>';
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}
?>

<h1 class="text-primary"><i class="fas fa-user-plus"></i> Add Student <small class="text-warning">Add New Student!</small></h1>
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Add Student</li>
  </ol>
</nav>

<div class="row">
  <div class="col-sm-6">
    <?php if (isset($datainsert)) { ?>
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

    <form enctype="multipart/form-data" method="POST" action="">
      <div class="form-group">
        <label for="name">Student Name</label>
        <input name="name" type="text" class="form-control" id="name" value="<?= isset($name) ? htmlspecialchars($name) : ''; ?>" required>
        <?php echo isset($input_error['name']) ? '<label class="text-danger">' . $input_error['name'] . '</label>' : ''; ?>
      </div>
      <div class="form-group">
        <label for="gender">Gender</label>
        <select name="gender" class="form-control" id="gender" required>
          <option value="">Select Gender</option>
          <option value="Male">Male</option>
          <option value="Female">Female</option>
        </select>
        <?php echo isset($input_error['gender']) ? '<label class="text-danger">' . $input_error['gender'] . '</label>' : ''; ?>
      </div>
      <div class="form-group">
        <label for="address">Student Address</label>
        <input name="address" type="text" class="form-control" id="address" value="<?= isset($address) ? htmlspecialchars($address) : ''; ?>" required>
        <?php echo isset($input_error['address']) ? '<label class="text-danger">' . $input_error['address'] . '</label>' : ''; ?>
      </div>
      <div class="form-group">
        <label for="class">Student Class</label>
        <select name="class" class="form-control" id="class" required>
          <option value="">Select</option>
          <option value="1st" <?= isset($class) && $class == '1st' ? 'selected' : ''; ?>>1st</option>
          <option value="2nd" <?= isset($class) && $class == '2nd' ? 'selected' : ''; ?>>2nd</option>
          <option value="3rd" <?= isset($class) && $class == '3rd' ? 'selected' : ''; ?>>3rd</option>
          <option value="4th" <?= isset($class) && $class == '4th' ? 'selected' : ''; ?>>4th</option>
          <option value="5th" <?= isset($class) && $class == '5th' ? 'selected' : ''; ?>>5th</option>
          <option value="6th" <?= isset($class) && $class == '6th' ? 'selected' : ''; ?>>6th</option>
          <option value="7th" <?= isset($class) && $class == '7th' ? 'selected' : ''; ?>>7th</option>
          <option value="8th" <?= isset($class) && $class == '8th' ? 'selected' : ''; ?>>8th</option>
          <option value="9th" <?= isset($class) && $class == '9th' ? 'selected' : ''; ?>>9th</option>
          <option value="10th" <?= isset($class) && $class == '10th' ? 'selected' : ''; ?>>10th</option>
          <option value="11th" <?= isset($class) && $class == '11th' ? 'selected' : ''; ?>>11th</option>
          <option value="12th" <?= isset($class) && $class == '12th' ? 'selected' : ''; ?>>12th</option>
        </select>
        <?php echo isset($input_error['class']) ? '<label class="text-danger">' . $input_error['class'] . '</label>' : ''; ?>
      </div>
      <div class="form-group">
        <label for="section">Student Section</label>
        <select name="section" class="form-control" id="section" required>
          <option value="">Select</option>
          <option value="A" <?= isset($section) && $section == 'A' ? 'selected' : ''; ?>>A</option>
          <option value="B" <?= isset($section) && $section == 'B' ? 'selected' : ''; ?>>B</option>
          <option value="C" <?= isset($section) && $section == 'C' ? 'selected' : ''; ?>>C</option>
          <option value="D" <?= isset($section) && $section == 'D' ? 'selected' : ''; ?>>D</option>
        </select>
        <?php echo isset($input_error['section']) ? '<label class="text-danger">' . $input_error['section'] . '</label>' : ''; ?>
      </div>
      <div class="form-group">
        <label for="pcontact">Parent Contact No</label>
        <input name="pcontact" type="text" class="form-control" id="pcontact" pattern="\+251[5-9][0-9]{8}" value="<?= isset($pcontact) ? htmlspecialchars($pcontact) : ''; ?>" placeholder="+251..." required>
        <?php echo isset($input_error['pcontact']) ? '<label class="text-danger">' . $input_error['pcontact'] . '</label>' : ''; ?>
      </div>
      <div class="form-group">
        <label for="date_of_birth">Date of Birth</label>
        <input name="date_of_birth" type="date" class="form-control" id="date_of_birth" value="<?= isset($date_of_birth) ? htmlspecialchars($date_of_birth) : ''; ?>" max="<?php echo date('Y-m-d'); ?>" required>
        <?php echo isset($input_error['date_of_birth']) ? '<label class="text-danger">' . $input_error['date_of_birth'] . '</label>' : ''; ?>
      </div>
      <div class="form-group">
        <label for="academic_year">Academic Year</label>
        <select name="academic_year" class="form-control" id="academic_year" required>
          <option value="">Select Academic Year</option>
          <option value="2025-2026" <?= isset($academic_year) && $academic_year == '2025-2026' ? 'selected' : ''; ?>>2025-2026</option>
          <option value="2026-2027" <?= isset($academic_year) && $academic_year == '2026-2027' ? 'selected' : ''; ?>>2026-2027</option>
          <option value="2027-2028" <?= isset($academic_year) && $academic_year == '2027-2028' ? 'selected' : ''; ?>>2027-2028</option>
          <option value="2028-2029" <?= isset($academic_year) && $academic_year == '2028-2029' ? 'selected' : ''; ?>>2028-2029</option>
          <option value="2029-2030" <?= isset($academic_year) && $academic_year == '2029-2030' ? 'selected' : ''; ?>>2029-2030</option>
        </select>
        <?php echo isset($input_error['academic_year']) ? '<label class="text-danger">' . $input_error['academic_year'] . '</label>' : ''; ?>
      </div>
      <div class="form-group">
        <label for="photo">Student Photo</label>
        <input name="photo" type="file" class="form-control" id="photo" accept="image/*" required>
        <?php echo isset($input_error['photo']) ? '<label class="text-danger">' . $input_error['photo'] . '</label>' : ''; ?>
      </div>
      <div class="form-group">
        <label for="transcript">Student Transcript (PDF)</label>
        <input name="transcript" type="file" class="form-control" id="transcript" accept="application/pdf" required>
        <?php echo isset($input_error['transcript']) ? '<label class="text-danger">' . $input_error['transcript'] . '</label>' : ''; ?>
      </div>
      <div class="form-group">
        <label for="birth_certificate">Birth Certificate (PDF)</label>
        <input name="birth_certificate" type="file" class="form-control" id="birth_certificate" accept="application/pdf" required>
        <?php echo isset($input_error['birth_certificate']) ? '<label class="text-danger">' . $input_error['birth_certificate'] . '</label>' : ''; ?>
      </div>
      <div class="form-group text-center">
        <input name="addstudent" value="Add Student" type="submit" class="btn btn-danger">
      </div>
    </form>
  </div>
</div>