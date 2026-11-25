<?php
require_once '../admin/db_con.php';
session_start();

if (!isset($_SESSION['client_login'])) {
    header('Location: login.php');
    exit();
}

if (isset($_POST['step1_submit'])) {
    $_SESSION['grade_level'] = $_POST['grade_level'];
    header('Location: register.php?step=2');
    exit();
}

if (isset($_POST['step2_submit'])) {
    $_SESSION['name'] = trim($_POST['name']);
    $_SESSION['address'] = trim($_POST['address']);
    $_SESSION['pcontact'] = trim($_POST['pcontact']);
    $_SESSION['date_of_birth'] = $_POST['date_of_birth'];
    $_SESSION['gender'] = $_POST['gender'];
    header('Location: register.php?step=3');
    exit();
}

if (isset($_POST['register_submit'])) {
    if (!isset($_SESSION['grade_level'], $_SESSION['name'], $_SESSION['address'], $_SESSION['pcontact'], $_SESSION['date_of_birth'], $_SESSION['gender'])) {
        $datainsert['inserterror'] = '<p style="color: red;">Session data missing. Please restart the registration process.</p>';
    } else {
        $name = $_SESSION['name'];
        $address = $_SESSION['address'];
        $pcontact = $_SESSION['pcontact'];
        $grade_level = $_SESSION['grade_level'];
        $date_of_birth = $_SESSION['date_of_birth'];
        $gender = $_SESSION['gender'];
        $photo = '';
        $transcript = '';
        $birth_certificate = '';

        $input_error = array();

        if (empty($name)) $input_error['name'] = "Student Name is required!";
        if (empty($address)) $input_error['address'] = "Student Address is required!";
        if (empty($pcontact)) $input_error['pcontact'] = "Parent Contact is required!";
        if (!preg_match('/^\+251[5-9][0-9]{8}$/', $pcontact)) $input_error['pcontact'] = "Invalid contact format! Use +251 followed by 9 digits.";
        if (empty($date_of_birth)) $input_error['date_of_birth'] = "Date of Birth is required!";
        if (empty($gender)) $input_error['gender'] = "Gender is required!";
        if (!in_array($gender, ['Male', 'Female', 'Other'])) $input_error['gender'] = "Invalid gender selection!";

        // Define absolute paths
        $base_dir = realpath(__DIR__ . '/../admin');
        $images_dir = $base_dir . DIRECTORY_SEPARATOR . 'images';
        $transcripts_dir = $base_dir . DIRECTORY_SEPARATOR . 'transcripts';
        $birth_certificates_dir = $base_dir . DIRECTORY_SEPARATOR . 'birth_certificates';

        // Create directories if they don't exist
        $dirs = [$images_dir, $transcripts_dir, $birth_certificates_dir];
        foreach ($dirs as $dir) {
            if (!file_exists($dir)) {
                if (!mkdir($dir, 0777, true)) {
                    $input_error['dir'] = "Failed to create directory: $dir. Check permissions.";
                    break;
                }
            } elseif (!is_writable($dir)) {
                $input_error['dir'] = "Directory $dir is not writable. Check permissions.";
                break;
            }
        }

        if (empty($_FILES['photo']['name'])) {
            $input_error['photo'] = "Student Photo is required!";
        } else {
            $allowed_photo_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            $photo_ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $photo = time() . '_' . rand(1000, 9999) . '.' . $photo_ext;
            $photo_path = $images_dir . DIRECTORY_SEPARATOR . $photo;
            if (!in_array($photo_ext, $allowed_photo_extensions)) {
                $input_error['photo'] = "Only JPG, JPEG, PNG, or GIF files are allowed for the photo.";
            } elseif (!move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path)) {
                $input_error['photo'] = "Failed to upload photo!";
            }
        }

        if (empty($_FILES['transcript']['name'])) {
            $input_error['transcript'] = "Student Transcript is required!";
        } else {
            $allowed_transcript_extensions = ['pdf'];
            $transcript_ext = strtolower(pathinfo($_FILES['transcript']['name'], PATHINFO_EXTENSION));
            $transcript = time() . '_' . rand(1000, 9999) . '_transcript.' . $transcript_ext;
            $transcript_path = $transcripts_dir . DIRECTORY_SEPARATOR . $transcript;
            if (!in_array($transcript_ext, $allowed_transcript_extensions)) {
                $input_error['transcript'] = "Only PDF files are allowed for the transcript.";
            } elseif (!move_uploaded_file($_FILES['transcript']['tmp_name'], $transcript_path)) {
                $input_error['transcript'] = "Failed to upload transcript!";
            }
        }

        if (empty($_FILES['birth_certificate']['name'])) {
            $input_error['birth_certificate'] = "Birth Certificate is required!";
        } else {
            $allowed_birth_cert_extensions = ['pdf'];
            $birth_cert_ext = strtolower(pathinfo($_FILES['birth_certificate']['name'], PATHINFO_EXTENSION));
            $birth_certificate = time() . '_' . rand(1000, 9999) . '_birth_certificate.' . $birth_cert_ext;
            $birth_cert_path = $birth_certificates_dir . DIRECTORY_SEPARATOR . $birth_certificate;
            if (!in_array($birth_cert_ext, $allowed_birth_cert_extensions)) {
                $input_error['birth_certificate'] = "Only PDF files are allowed for the birth certificate.";
            } elseif (!move_uploaded_file($_FILES['birth_certificate']['tmp_name'], $birth_cert_path)) {
                $input_error['birth_certificate'] = "Failed to upload birth certificate!";
            }
        }

        if (count($input_error) == 0) {
            do {
                $random_digits = sprintf("%04d", rand(0, 9999));
                $student_id = "LIA" . $random_digits;
                $check_query = "SELECT student_id FROM pending_registrations WHERE student_id = ?";
                $check_stmt = mysqli_prepare($db_con, $check_query);
                mysqli_stmt_bind_param($check_stmt, "s", $student_id);
                mysqli_stmt_execute($check_stmt);
                $result = mysqli_stmt_get_result($check_stmt);
                $exists = mysqli_num_rows($result) > 0;
                mysqli_stmt_close($check_stmt);
            } while ($exists);

            $dob = new DateTime($date_of_birth);
            $today = new DateTime();
            $age = $today->diff($dob)->y;
            $academic_year = $_POST['academic_year'];
            $grade_map = ['1st' => 12, '2nd' => 11, '3rd' => 10, '4th' => 9, '5th' => 8, '6th' => 7, '7th' => 6, '8th' => 5, '9th' => 4, '10th' => 3, '11th' => 2, '12th' => 1];
            $years_to_grad = $grade_map[$grade_level];
            $start_year = (int)explode('-', $academic_year)[0];
            $graduation_year = $start_year + $years_to_grad;
            $status = 'pending';
            $datetime = date('Y-m-d H:i:s');
            $parent_id = $_SESSION['client_id'];

            $query = "INSERT INTO pending_registrations (parent_id, student_id, name, grade_level, gender, address, pcontact, date_of_birth, age, academic_year, graduation_year, photo, transcript, birth_certificate, status, datetime) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($db_con, $query);
            if ($stmt === false) {
                $datainsert['inserterror'] = '<p style="color: red;">Database error: ' . mysqli_error($db_con) . '</p>';
            } else {
                mysqli_stmt_bind_param($stmt, "issssssssiisssss", $parent_id, $student_id, $name, $grade_level, $gender, $address, $pcontact, $date_of_birth, $age, $academic_year, $graduation_year, $photo, $transcript, $birth_certificate, $status, $datetime);
                if (mysqli_stmt_execute($stmt)) {
                    // Generate confirmation slip
                    echo "<div class='container mt-5 confirmation-slip'>
                            <h2>Registration Confirmation Slip</h2>
                            <p>Dear Parent,</p>
                            <p>Your child {$name} has been registered with Student ID: {$student_id}.</p>
                            <p>Grade Level: {$grade_level}</p>
                            <p>Gender: {$gender}</p>
                            <p>Status: {$status}</p>
                            <p>Thank you!</p>
                            <a href='dashboard.php' class='btn btn-primary'>Back to Dashboard</a>
                          </div>";
                    // Clear session data
                    unset($_SESSION['grade_level'], $_SESSION['name'], $_SESSION['address'], $_SESSION['pcontact'], $_SESSION['date_of_birth'], $_SESSION['gender']);
                    exit;
                } else {
                    $datainsert['inserterror'] = '<p style="color: red;">Registration failed: ' . mysqli_stmt_error($stmt) . '</p>';
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Your Child</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>
    <style>
        .step-title { 
            font-size: 24px; 
            font-weight: bold; 
            color: #2c3e50; 
            margin-bottom: 20px; 
        }
        .step1-form, .step2-form, .step3-form, .confirmation-slip {
            background-color: #f3e5f5; /* Light purple background */
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border: 2px solid #6a1b9a; /* Dark purple border */
        }
        .step1-form .form-group label, 
        .step2-form .form-group label, 
        .step3-form .form-group label {
            color: #6a1b9a; /* Dark purple labels */
            font-weight: 600;
        }
        .step1-form .form-control, 
        .step2-form .form-control, 
        .step3-form .form-control {
            border: 2px solid #ab47bc; /* Medium purple border */
            border-radius: 5px;
            background-color: #fff;
            transition: border-color 0.3s ease;
        }
        .step1-form .form-control:focus, 
        .step2-form .form-control:focus, 
        .step3-form .form-control:focus {
            border-color: #6a1b9a; /* Dark purple on focus */
            box-shadow: 0 0 5px rgba(106, 27, 154, 0.5);
        }
        .step1-form .btn-primary, 
        .step2-form .btn-primary, 
        .step3-form .btn-success, 
        .confirmation-slip .btn-primary {
            background-color: #6a1b9a; /* Dark purple button */
            border-color: #6a1b9a;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        .step1-form .btn-primary:hover, 
        .step2-form .btn-primary:hover, 
        .step3-form .btn-success:hover, 
        .confirmation-slip .btn-primary:hover {
            background-color: #ab47bc; /* Lighter purple on hover */
            border-color: #ab47bc;
        }
        .step1-form .text-danger, 
        .step2-form .text-danger, 
        .step3-form .text-danger {
            color: #d81b60; /* Pinkish-red for error messages */
            font-size: 0.9em;
        }
        .confirmation-slip h2 {
            color: #6a1b9a; /* Dark purple heading */
            font-weight: bold;
        }
        .confirmation-slip p {
            color: #2c3e50; /* Dark gray for text */
            font-size: 1.1em;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-primary">Register Your Child</h1>
        <div class="mb-3">
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
        <?php if (isset($datainsert['inserterror'])) echo $datainsert['inserterror']; ?>
        <?php if (isset($input_error)) { ?>
            <div class="alert alert-danger">
                <?php foreach ($input_error as $error) echo $error . '<br>'; ?>
            </div>
        <?php } ?>

        <?php $step = isset($_GET['step']) ? $_GET['step'] : 1; ?>
        <?php if ($step == 1) { ?>
            <div class="step-title">Step 1: Select Grade Level</div>
            <form method="POST" action="" class="step1-form">
                <div class="form-group">
                    <label for="grade_level">Select Grade Level</label>
                    <select name="grade_level" class="form-control" id="grade_level" required>
                        <option value="">Select Grade</option>
                        <option value="1st">1st</option>
                        <option value="2nd">2nd</option>
                        <option value="3rd">3rd</option>
                        <option value="4th">4th</option>
                        <option value="5th">5th</option>
                        <option value="6th">6th</option>
                        <option value="7th">7th</option>
                        <option value="8th">8th</option>
                        <option value="9th">9th</option>
                        <option value="10th">10th</option>
                        <option value="11th">11th</option>
                        <option value="12th">12th</option>
                    </select>
                </div>
                <div class="form-group text-center">
                    <button type="submit" name="step1_submit" class="btn btn-primary">Next</button>
                </div>
            </form>
        <?php } elseif ($step == 2) { ?>
            <div class="step-title">Step 2: Enter Student Data</div>
            <form method="POST" action="" class="step2-form">
                <div class="form-group">
                    <label for="name">Student Name</label>
                    <input name="name" type="text" class="form-control" id="name" value="<?= isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : ''; ?>" required>
                    <?php echo isset($input_error['name']) ? '<label class="text-danger">' . $input_error['name'] . '</label>' : ''; ?>
                </div>
                <div class="form-group">
                    <label for="address">Student Address</label>
                    <input name="address" type="text" class="form-control" id="address" value="<?= isset($_SESSION['address']) ? htmlspecialchars($_SESSION['address']) : ''; ?>" required>
                    <?php echo isset($input_error['address']) ? '<label class="text-danger">' . $input_error['address'] . '</label>' : ''; ?>
                </div>
                <div class="form-group">
                    <label for="pcontact">Parent Contact No</label>
                    <input name="pcontact" type="text" class="form-control" id="pcontact" pattern="\+251[5-9][0-9]{8}" value="<?= isset($_SESSION['pcontact']) ? htmlspecialchars($_SESSION['pcontact']) : ''; ?>" placeholder="+251..." required>
                    <?php echo isset($input_error['pcontact']) ? '<label class="text-danger">' . $input_error['pcontact'] . '</label>' : ''; ?>
                </div>
                <div class="form-group">
                    <label for="date_of_birth">Date of Birth</label>
                    <input name="date_of_birth" type="date" class="form-control" id="date_of_birth" value="<?= isset($_SESSION['date_of_birth']) ? htmlspecialchars($_SESSION['date_of_birth']) : ''; ?>" max="2015-12-31" required>
                    <?php echo isset($input_error['date_of_birth']) ? '<label class="text-danger">' . $input_error['date_of_birth'] . '</label>' : ''; ?>
                </div>
                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select name="gender" class="form-control" id="gender" required>
                        <option value="">Select Gender</option>
                        <option value="Male" <?= isset($_SESSION['gender']) && $_SESSION['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?= isset($_SESSION['gender']) && $_SESSION['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?= isset($_SESSION['gender']) && $_SESSION['gender'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                    <?php echo isset($input_error['gender']) ? '<label class="text-danger">' . $input_error['gender'] . '</label>' : ''; ?>
                </div>
                <div class="form-group text-center">
                    <button type="submit" name="step2_submit" class="btn btn-primary">Next</button>
                </div>
            </form>
        <?php } elseif ($step == 3) { ?>
            <div class="step-title">Step 3: Upload Files and Select Academic Year</div>
            <form enctype="multipart/form-data" method="POST" action="" class="step3-form">
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
                <div class="form-group">
                    <label for="academic_year">Academic Year</label>
                    <select name="academic_year" class="form-control" id="academic_year" required>
                        <option value="">Select Academic Year</option>
                        <option value="2025-2026">2025-2026</option>
                        <option value="2026-2027">2026-2027</option>
                        <option value="2027-2028">2027-2028</option>
                        <option value="2028-2029">2028-2029</option>
                        <option value="2029-2030">2029-2030</option>
                    </select>
                </div>
                <div class="form-group text-center">
                    <button type="submit" name="register_submit" class="btn btn-success">Register</button>
                </div>
            </form>
        <?php } ?>
    </div>
    <script src="../js/jquery-3.5.1.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
</body>
</html>