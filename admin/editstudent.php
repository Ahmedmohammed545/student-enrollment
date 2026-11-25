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
$oldPhoto = base64_decode($_GET['photo']);

// Define absolute paths for uploads
$images_dir = __DIR__ . '/images';
$transcripts_dir = __DIR__ . '/transcripts';
$birth_certificates_dir = __DIR__ . '/birth_certificates';

// Ensure directories exist
if (!is_dir($images_dir)) {
    mkdir($images_dir, 0777, true);
}
if (!is_dir($transcripts_dir)) {
    mkdir($transcripts_dir, 0777, true);
}
if (!is_dir($birth_certificates_dir)) {
    mkdir($birth_certificates_dir, 0777, true);
}

if (isset($_POST['updatestudent'])) {
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

    // Handle photo
    $photo = $oldPhoto;
    if (!empty($_FILES['photo']['name'])) {
        $allowed_photo_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $photo_ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $photo = 'student_' . date('Y-m-d-H-i-s') . '.' . $photo_ext;
        if (!in_array($photo_ext, $allowed_photo_extensions)) {
            $input_error['photo'] = "Only JPG, JPEG, PNG, or GIF files are allowed for the photo.";
        }
    }

    // Handle transcript
    $transcript = isset($_GET['transcript']) ? base64_decode($_GET['transcript']) : '';
    if (!empty($_FILES['transcript']['name'])) {
        $allowed_transcript_extensions = ['pdf'];
        $transcript_ext = strtolower(pathinfo($_FILES['transcript']['name'], PATHINFO_EXTENSION));
        $transcript = 'transcript_' . date('Y-m-d-H-i-s') . '.' . $transcript_ext;
        if (!in_array($transcript_ext, $allowed_transcript_extensions)) {
            $input_error['transcript'] = "Only PDF files are allowed for the transcript.";
        }
    }

    // Handle birth certificate
    $birth_certificate = isset($_GET['birth_certificate']) ? base64_decode($_GET['birth_certificate']) : '';
    if (!empty($_FILES['birth_certificate']['name'])) {
        $allowed_birth_cert_extensions = ['pdf'];
        $birth_cert_ext = strtolower(pathinfo($_FILES['birth_certificate']['name'], PATHINFO_EXTENSION));
        $birth_certificate = 'birth_certificate_' . date('Y-m-d-H-i-s') . '.' . $birth_cert_ext;
        if (!in_array($birth_cert_ext, $allowed_birth_cert_extensions)) {
            $input_error['birth_certificate'] = "Only PDF files are allowed for the birth certificate.";
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

        // Update database
        $query = "UPDATE `student_info` SET `name`=?, `class`=?, `section`=?, `address`=?, `pcontact`=?, 
                  `date_of_birth`=?, `age`=?, `academic_year`=?, `graduation_year`=?, `photo`=?, 
                  `transcript`=?, `birth_certificate`=? WHERE `id`=?";
        $stmt = mysqli_prepare($db_con, $query);
        if ($stmt === false) {
            $datainsert['inserterror'] = '<p style="color: red;">Database error: ' . mysqli_error($db_con) . '</p>';
        } else {
            mysqli_stmt_bind_param($stmt, "ssssssisssssi", $name, $class, $section, $address, $pcontact, 
                                  $date_of_birth, $age, $academic_year, $graduation_year, $photo, 
                                  $transcript, $birth_certificate, $id);
            if (mysqli_stmt_execute($stmt)) {
                $upload_success = true;

                // Handle photo upload
                if (!empty($_FILES['photo']['name'])) {
                    $photo_path = $images_dir . DIRECTORY_SEPARATOR . $photo;
                    if (move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path)) {
                        if (!empty($oldPhoto) && file_exists($images_dir . DIRECTORY_SEPARATOR . $oldPhoto)) {
                            unlink($images_dir . DIRECTORY_SEPARATOR . $oldPhoto);
                        }
                    } else {
                        $datainsert['inserterror'] = '<p style="color: red;">Failed to move new photo to images directory!</p>';
                        $upload_success = false;
                    }
                }

                // Handle transcript upload
                if (!empty($_FILES['transcript']['name'])) {
                    $transcript_path = $transcripts_dir . DIRECTORY_SEPARATOR . $transcript;
                    if (move_uploaded_file($_FILES['transcript']['tmp_name'], $transcript_path)) {
                        if (!empty(base64_decode($_GET['transcript'])) && file_exists($transcripts_dir . DIRECTORY_SEPARATOR . base64_decode($_GET['transcript']))) {
                            unlink($transcripts_dir . DIRECTORY_SEPARATOR . base64_decode($_GET['transcript']));
                        }
                    } else {
                        $datainsert['inserterror'] = '<p style="color: red;">Failed to move new transcript to transcripts directory!</p>';
                        $upload_success = false;
                    }
                }

                // Handle birth certificate upload
                if (!empty($_FILES['birth_certificate']['name'])) {
                    $birth_cert_path = $birth_certificates_dir . DIRECTORY_SEPARATOR . $birth_certificate;
                    if (move_uploaded_file($_FILES['birth_certificate']['tmp_name'], $birth_cert_path)) {
                        if (!empty(base64_decode($_GET['birth_certificate'])) && file_exists($birth_certificates_dir . DIRECTORY_SEPARATOR . base64_decode($_GET['birth_certificate']))) {
                            unlink($birth_certificates_dir . DIRECTORY_SEPARATOR . base64_decode($_GET['birth_certificate']));
                        }
                    } else {
                        $datainsert['inserterror'] = '<p style="color: red;">Failed to move new birth certificate to birth certificates directory!</p>';
                        $upload_success = false;
                    }
                }

                if ($upload_success) {
                    header('Location: index.php?page=all-student&edit=success');
                    exit();
                } else {
                    $datainsert['inserterror'] = '<p style="color: red;">Update failed due to file upload errors!</p>';
                }
            } else {
                $datainsert['inserterror'] = '<p style="color: red;">Update failed: ' . mysqli_stmt_error($stmt) . '</p>';
            }
            mysqli_stmt_close($stmt);
        }
    }
}

$query = "SELECT `id`, `student_id`, `name`, `class`, `section`, `address`, `pcontact`, `date_of_birth`, 
          `age`, `academic_year`, `graduation_year`, `photo`, `transcript`, `birth_certificate`, `datetime` 
          FROM `student_info` WHERE `id`=?";
$stmt = mysqli_prepare($db_con, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_array($result);
mysqli_stmt_close($stmt);
?>

<h1 class="text-primary"><i class="fas fa-user-plus"></i> Edit Student Information! <small class="text-warning">Edit Student!</small></h1>
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="index.php?page=all-student">All Students</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit Student</li>
  </ol>
</nav>

<div class="row">
  <div class="col-sm-6">
    <?php if (isset($datainsert)) { ?>
    <div role="alert" aria-live="assertive" aria-atomic="true" class="toast fade show" data-autohide="true" data-animation="true" data-delay="2000">
      <div class="toast-header">
        <strong class="mr-auto">Student Update Alert</strong>
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
        <label for="student_id">Student ID</label>
        <input name="student_id" type="text" class="form-control" id="student_id" value="<?= isset($row['student_id']) ? htmlspecialchars($row['student_id']) : ''; ?>" readonly>
      </div>
      <div class="form-group">
        <label for="name">Student Name</label>
        <input name="name" type="text" class="form-control" id="name" value="<?= isset($row['name']) ? htmlspecialchars($row['name']) : ''; ?>" required>
        <?php echo isset($input_error['name']) ? '<label class="text-danger">' . $input_error['name'] . '</label>' : ''; ?>
      </div>
      <div class="form-group">
        <label for="address">Student Address</label>
        <input name="address" type="text" class="form-control" id="address" value="<?= isset($row['address']) ? htmlspecialchars($row['address']) : ''; ?>" required>
        <?php echo isset($input_error['address']) ? '<label class="text-danger">' . $input_error['address'] . '</label>' : ''; ?>
      </div>
      <div class="form-group">
        <label for="class">Student Class</label>
        <select name="class" class="form-control" id="class" required>
          <option value="">Select</option>
          <option value="1st" <?= isset($row['class']) && $row['class'] == '1st' ? 'selected' : ''; ?>>1st</option>
          <option value="2nd" <?= isset($row['class']) && $row['class'] == '2nd' ? 'selected' : ''; ?>>2nd</option>
          <option value="3rd" <?= isset($row['class']) && $row['class'] == '3rd' ? 'selected' : ''; ?>>3rd</option>
          <option value="4th" <?= isset($row['class']) && $row['class'] == '4th' ? 'selected' : ''; ?>>4th</option>
          <option value="5th" <?= isset($row['class']) && $row['class'] == '5th' ? 'selected' : ''; ?>>5th</option>
          <option value="6th" <?= isset($row['class']) && $row['class'] == '6th' ? 'selected' : ''; ?>>6th</option>
          <option value="7th" <?= isset($row['class']) && $row['class'] == '7th' ? 'selected' : ''; ?>>7th</option>
          <option value="8th" <?= isset($row['class']) && $row['class'] == '8th' ? 'selected' : ''; ?>>8th</option>
          <option value="9th" <?= isset($row['class']) && $row['class'] == '9th' ? 'selected' : ''; ?>>9th</option>
          <option value="10th" <?= isset($row['class']) && $row['class'] == '10th' ? 'selected' : ''; ?>>10th</option>
          <option value="11th" <?= isset($row['class']) && $row['class'] == '11th' ? 'selected' : ''; ?>>11th</option>
          <option value="12th" <?= isset($row['class']) && $row['class'] == '12th' ? 'selected' : ''; ?>>12th</option>
        </select>
        <?php echo isset($input_error['class']) ? '<label class="text-danger">' . $input_error['class'] . '</label>' : ''; ?>
      </div>
      <div class="form-group">
        <label for="section">Student Section</label>
        <select name="section" class="form-control" id="section" required>
          <option value="">Select</option>
          <option value="A" <?= isset($row['section']) && $row['section'] == 'A' ? 'selected' : ''; ?>>A</option>
          <option value="B" <?= isset($row['section']) && $row['section'] == 'B' ? 'selected' : ''; ?>>B</option>
          <option value="C" <?= isset($row['section']) && $row['section'] == 'C' ? 'selected' : ''; ?>>C</option>
          <option value="D" <?= isset($row['section']) && $row['section'] == 'D' ? 'selected' : ''; ?>>D</option>
        </select>
        <?php echo isset($input_error['section']) ? '<label class="text-danger">' . $input_error['section'] . '</label>' : ''; ?>
      </div>
      <div class="form-group">
        <label for="pcontact">Parent Contact No</label>
        <input name="pcontact" type="text" class="form-control" id="pcontact" pattern="\+251[5-9][0-9]{8}" value="<?= isset($row['pcontact']) ? htmlspecialchars($row['pcontact']) : ''; ?>" placeholder="+251..." required>
        <?php echo isset($input_error['pcontact']) ? '<label class="text-danger">' . $input_error['pcontact'] . '</label>' : ''; ?>
      </div>
      <div class="form-group">
        <label for="date_of_birth">Date of Birth</label>
        <input name="date_of_birth" type="date" class="form-control" id="date_of_birth" value="<?= isset($row['date_of_birth']) ? htmlspecialchars($row['date_of_birth']) : ''; ?>" max="<?php echo date('Y-m-d'); ?>" required>
        <?php echo isset($input_error['date_of_birth']) ? '<label class="text-danger">' . $input_error['date_of_birth'] . '</label>' : ''; ?>
      </div>
      <div class="form-group">
        <label for="academic_year">Academic Year</label>
        <select name="academic_year" class="form-control" id="academic_year" required>
          <option value="">Select Academic Year</option>
          <option value="2025-2026" <?= isset($row['academic_year']) && $row['academic_year'] == '2025-2026' ? 'selected' : ''; ?>>2025-2026</option>
          <option value="2026-2027" <?= isset($row['academic_year']) && $row['academic_year'] == '2026-2027' ? 'selected' : ''; ?>>2026-2027</option>
          <option value="2027-2028" <?= isset($row['academic_year']) && $row['academic_year'] == '2027-2028' ? 'selected' : ''; ?>>2027-2028</option>
          <option value="2028-2029" <?= isset($row['academic_year']) && $row['academic_year'] == '2028-2029' ? 'selected' : ''; ?>>2028-2029</option>
          <option value="2029-2030" <?= isset($row['academic_year']) && $row['academic_year'] == '2029-2030' ? 'selected' : ''; ?>>2029-2030</option>
        </select>
        <?php echo isset($input_error['academic_year']) ? '<label class="text-danger">' . $input_error['academic_year'] . '</label>' : ''; ?>
      </div>
      <div class="form-group">
        <label for="photo">Student Photo</label>
        <input name="photo" type="file" class="form-control" id="photo" accept="image/*">
        <?php echo isset($input_error['photo']) ? '<label class="text-danger">' . $input_error['photo'] . '</label>' : ''; ?>
      </div>
      <div class="form-group">
        <label for="transcript">Student Transcript (PDF)</label>
        <input name="transcript" type="file" class="form-control" id="transcript" accept="application/pdf">
        <?php echo isset($input_error['transcript']) ? '<label class="text-danger">' . $input_error['transcript'] . '</label>' : ''; ?>
      </div>
      <div class="form-group">
        <label for="birth_certificate">Birth Certificate (PDF)</label>
        <input name="birth_certificate" type="file" class="form-control" id="birth_certificate" accept="application/pdf">
        <?php echo isset($input_error['birth_certificate']) ? '<label class="text-danger">' . $input_error['birth_certificate'] . '</label>' : ''; ?>
      </div>
      <div class="form-group text-center">
        <input name="updatestudent" value="Update Student" type="submit" class="btn btn-danger">
      </div>
    </form>
  </div>
</div>