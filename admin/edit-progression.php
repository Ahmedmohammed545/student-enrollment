<?php
require_once 'db_con.php';

if (!isset($_SESSION['user_login'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['student_id'])) {
    header('Location: index.php?page=approve-registrations');
    exit();
}

$student_id = $_GET['student_id'];

// Fetch current student data
$query = "SELECT * FROM student_info WHERE student_id = ?";
$stmt = mysqli_prepare($db_con, $query);
mysqli_stmt_bind_param($stmt, "s", $student_id);
mysqli_stmt_execute($stmt);
$student = mysqli_fetch_assoc($stmt);
mysqli_stmt_close($stmt);

if (!$student) {
    header('Location: index.php?page=approve-registrations');
    exit();
}

if (isset($_POST['update_progression'])) {
    $new_class = $_POST['class'];
    $new_section = $_POST['section'];
    $new_academic_year = $_POST['academic_year'];
    
    // Record progression history
    $prog_query = "INSERT INTO grade_progression 
                  (student_id, from_class, to_class, from_section, to_section, 
                   from_academic_year, to_academic_year) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
    $prog_stmt = mysqli_prepare($db_con, $prog_query);
    mysqli_stmt_bind_param($prog_stmt, "sssssss", 
        $student_id,
        $student['class'],
        $new_class,
        $student['section'],
        $new_section,
        $student['academic_year'],
        $new_academic_year
    );
    mysqli_stmt_execute($prog_stmt);
    mysqli_stmt_close($prog_stmt);
    
    // Update student record
    $update_query = "UPDATE student_info 
                    SET class = ?, section = ?, academic_year = ?
                    WHERE student_id = ?";
    $update_stmt = mysqli_prepare($db_con, $update_query);
    mysqli_stmt_bind_param($update_stmt, "ssss", 
        $new_class, 
        $new_section, 
        $new_academic_year, 
        $student_id
    );
    
    if (mysqli_stmt_execute($update_stmt)) {
        header("Location: index.php?page=all-student&edit=success");
        exit();
    }
    mysqli_stmt_close($update_stmt);
}
?>

<!-- HTML Form for Progression Update -->
<h1 class="text-primary"><i class="fas fa-edit"></i> Edit Progression</h1>

<form method="POST">
    <div class="form-group">
        <label>Current Grade: <?php echo $student['class']; ?></label>
        <select name="class" class="form-control" required>
            <option value="">Select New Grade</option>
            <?php 
            $grades = ['1st','2nd','3rd','4th','5th','6th',
                      '7th','8th','9th','10th','11th','12th'];
            foreach ($grades as $grade): 
                $selected = ($grade == $student['class']) ? 'selected' : '';
                echo "<option value='$grade' $selected>$grade</option>";
            endforeach; 
            ?>
        </select>
    </div>
    
    <div class="form-group">
        <label>Current Section: <?php echo $student['section']; ?></label>
        <select name="section" class="form-control" required>
            <option value="">Select Section</option>
            <option value="A" <?= $student['section']=='A'?'selected':'' ?>>A</option>
            <option value="B" <?= $student['section']=='B'?'selected':'' ?>>B</option>
            <option value="C" <?= $student['section']=='C'?'selected':'' ?>>C</option>
            <option value="D" <?= $student['section']=='D'?'selected':'' ?>>D</option>
        </select>
    </div>
    
    <div class="form-group">
        <label>Academic Year</label>
        <input type="text" name="academic_year" 
               value="<?php echo $student['academic_year']; ?>"
               class="form-control" required>
    </div>
    
    <button type="submit" name="update_progression" 
            class="btn btn-primary">
        Update Progression
    </button>
</form>