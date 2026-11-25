<?php
require_once 'db_con.php';

// Check if user is logged in
if (!isset($_SESSION['user_login'])) {
    header('Location: login.php');
    exit();
}

// Class capacities overview
$query = "SELECT 
            c.class, 
            c.section, 
            c.capacity, 
            COUNT(s.id) as current,
            (c.capacity - COUNT(s.id)) as remaining
          FROM class_capacities c
          LEFT JOIN student_info s ON c.class = s.class AND c.section = s.section
          GROUP BY c.class, c.section, c.capacity
          ORDER BY c.class, c.section";
$result = mysqli_query($db_con, $query);
$capacity_overview = [];
while ($row = mysqli_fetch_assoc($result)) {
    $capacity_overview[] = $row;
}
?>

<h1 class="text-primary"><i class="fas fa-chalkboard-teacher"></i> Class Capacities <small class="text-warning">Overview</small></h1>
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Class Capacities</li>
  </ol>
</nav>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <i class="fas fa-users"></i> Class Capacities Overview
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-light">
                            <tr>
                                <th>Class</th>
                                <th>Section</th>
                                <th>Capacity</th>
                                <th>Current</th>
                                <th>Remaining</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($capacity_overview as $class): ?>
                            <tr>
                                <td><?php echo $class['class']; ?></td>
                                <td><?php echo $class['section']; ?></td>
                                <td><?php echo $class['capacity']; ?></td>
                                <td><?php echo $class['current']; ?></td>
                                <td><?php echo $class['remaining']; ?></td>
                                <td>
                                    <?php if ($class['remaining'] <= 0): ?>
                                        <span class="badge badge-danger">Full</span>
                                    <?php elseif ($class['remaining'] <= 5): ?>
                                        <span class="badge badge-warning">Almost Full</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">Available</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>