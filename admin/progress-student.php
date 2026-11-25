<?php
require_once 'db_con.php';

if (!isset($_SESSION['user_login'])) {
    header('Location: login.php');
    exit();
}

// Handle progression update
if (isset($_POST['progress_students'])) {
    // Begin transaction
    mysqli_begin_transaction($db_con);
    
    try {
        foreach ($_POST['students'] as $student_id => $progress_data) {
            // Get current student data
            $query = "SELECT class, section, academic_year FROM student_info WHERE student_id = ?";
            $stmt = mysqli_prepare($db_con, $query);
            mysqli_stmt_bind_param($stmt, "s", $student_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $current_data = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            
            if (!$current_data) continue;
            
            // Record progression history
            $history_query = "INSERT INTO grade_progression 
                             (student_id, from_class, to_class, from_section, to_section, 
                              from_academic_year, to_academic_year)
                             VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($db_con, $history_query);
            mysqli_stmt_bind_param($stmt, "sssssss", 
                $student_id,
                $current_data['class'],
                $progress_data['new_class'],
                $current_data['section'],
                $progress_data['new_section'],
                $current_data['academic_year'],
                $progress_data['new_academic_year']
            );
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            
            // Update student record
            $update_query = "UPDATE student_info 
                            SET class = ?, section = ?, academic_year = ?
                            WHERE student_id = ?";
            $stmt = mysqli_prepare($db_con, $update_query);
            mysqli_stmt_bind_param($stmt, "ssss", 
                $progress_data['new_class'],
                $progress_data['new_section'],
                $progress_data['new_academic_year'],
                $student_id
            );
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        
        // Commit transaction
        mysqli_commit($db_con);
        $success = "Student progress updated successfully!";
    } catch (Exception $e) {
        // Rollback on error
        mysqli_rollback($db_con);
        $error = "Error updating progress: " . $e->getMessage();
    }
}

// Get all students for the progression form
$query = "SELECT student_id, name, class, section, academic_year 
          FROM student_info 
          ORDER BY class, name";
$result = mysqli_query($db_con, $query);
$students = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<h1 class="text-primary"><i class="fas fa-graduation-cap"></i> Progress Students</h1>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<form method="POST" action="">
    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>Current Grade</th>
                <th>Current Section</th>
                <th>New Grade</th>
                <th>New Section</th>
                <th>New Academic Year</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $student): ?>
                <tr>
                    <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                    <td><?php echo htmlspecialchars($student['class']); ?></td>
                    <td><?php echo htmlspecialchars($student['section']); ?></td>
                    <td>
                        <select name="students[<?php echo $student['student_id']; ?>][new_class]" class="form-control">
                            <?php 
                            $grades = ['1st','2nd','3rd','4th','5th','6th','7th','8th','9th','10th','11th','12th'];
                            foreach ($grades as $grade): 
                                $selected = ($grade == $student['class']) ? 'selected' : '';
                                echo "<option value='$grade' $selected>$grade</option>";
                            endforeach; 
                            ?>
                        </select>
                    </td>
                    <td>
                        <select name="students[<?php echo $student['student_id']; ?>][new_section]" class="form-control">
                            <option value="A" <?= $student['section']=='A'?'selected':'' ?>>A</option>
                            <option value="B" <?= $student['section']=='B'?'selected':'' ?>>B</option>
                            <option value="C" <?= $student['section']=='C'?'selected':'' ?>>C</option>
                            <option value="D" <?= $student['section']=='D'?'selected':'' ?>>D</option>
                        </select>
                    </td>
                    <td>
                        <input type="text" 
                               name="students[<?php echo $student['student_id']; ?>][new_academic_year]" 
                               value="<?php echo htmlspecialchars($student['academic_year']); ?>" 
                               class="form-control">
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="text-center">
        <button type="submit" name="progress_students" class="btn btn-primary btn-lg">
            <i class="fas fa-save"></i> Save All Progressions
        </button>
    </div>
</form>