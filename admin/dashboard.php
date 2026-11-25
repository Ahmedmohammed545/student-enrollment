<?php
require_once 'db_con.php';

// Check if user is logged in
if (!isset($_SESSION['user_login'])) {
    header('Location: login.php');
    exit();
}

// Get statistics
$stats = [];

// Total students
$query = "SELECT COUNT(*) as total FROM student_info";
$result = mysqli_query($db_con, $query);
$stats['total_students'] = mysqli_fetch_assoc($result)['total'];

// Total users
$query = "SELECT COUNT(*) as total FROM users";
$result = mysqli_query($db_con, $query);
$stats['total_users'] = mysqli_fetch_assoc($result)['total'];

// Total pending registrations
$query = "SELECT COUNT(*) as total FROM pending_registrations WHERE status = 'pending'";
$result = mysqli_query($db_con, $query);
$stats['pending_registrations'] = mysqli_fetch_assoc($result)['total'];

// User profile
$usernameshow = $_SESSION['user_login'];
$query = "SELECT name, photo FROM users WHERE username = ?";
$stmt = mysqli_prepare($db_con, $query);
mysqli_stmt_bind_param($stmt, "s", $usernameshow);
mysqli_stmt_execute($stmt);
$user_result = mysqli_stmt_get_result($stmt);
$userrow = mysqli_fetch_assoc($user_result);
mysqli_stmt_close($stmt);

// Gender distribution
$query = "SELECT gender, COUNT(*) as count FROM student_info GROUP BY gender";
$result = mysqli_query($db_con, $query);
$stats['gender_distribution'] = [];
while ($row = mysqli_fetch_assoc($result)) {
    $stats['gender_distribution'][$row['gender']] = $row['count'];
}

// Class distribution
$query = "SELECT class, COUNT(*) as count FROM student_info GROUP BY class ORDER BY class";
$result = mysqli_query($db_con, $query);
$stats['class_distribution'] = [];
while ($row = mysqli_fetch_assoc($result)) {
    $stats['class_distribution'][$row['class']] = $row['count'];
}

// Age distribution
$query = "SELECT 
            CASE 
              WHEN age BETWEEN 5 AND 10 THEN '5-10'
              WHEN age BETWEEN 11 AND 15 THEN '11-15'
              WHEN age BETWEEN 16 AND 20 THEN '16-20'
              ELSE '20+'
            END as age_group,
            COUNT(*) as count
          FROM student_info
          GROUP BY age_group
          ORDER BY age_group";
$result = mysqli_query($db_con, $query);
$stats['age_distribution'] = [];
while ($row = mysqli_fetch_assoc($result)) {
    $stats['age_distribution'][$row['age_group']] = $row['count'];
}

// Recent activity (latest 3 approved students)
$query = "SELECT student_id, name, class, section, datetime 
          FROM student_info 
          ORDER BY datetime DESC 
          LIMIT 3";
$recent_activity = mysqli_query($db_con, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f1f3f5;
            font-family: 'Poppins', sans-serif;
        }
        .dashboard-header {
            background: linear-gradient(135deg, #5f2985, #7b3aa8);
            color: white;
            padding: 30px 0;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .summary-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.2);
        }
        .summary-card i {
            font-size: 2.5rem;
            color: #5f2985;
        }
        .summary-card h4 {
            color: #333;
            font-weight: 600;
        }
        .summary-card .card-link {
            color: #5f2985;
            text-decoration: none;
            font-weight: 500;
        }
        .summary-card .card-link:hover {
            text-decoration: underline;
        }
        .card-header {
            background: linear-gradient(135deg, #5f2985, #7b3aa8);
            color: white;
            border-radius: 10px 10px 0 0;
            padding: 15px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        }
        .recent-activity {
            max-height: 200px;
            overflow-y: auto;
        }
        .recent-activity .list-group-item {
            border: none;
            padding: 10px 15px;
            transition: background 0.3s;
        }
        .recent-activity .list-group-item:hover {
            background: #f8f9fa;
        }
        canvas {
            max-height: 300px;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .summary-card, .card {
            animation: fadeIn 0.5s ease-in-out;
        }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2><i class="fas fa-tachometer-alt"></i> Welcome, <?php echo ucwords(htmlspecialchars($userrow['name'])); ?></h2>
                </div>
                <div class="col-md-6 text-right">
                    <a href="logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="summary-card">
                    <div class="row align-items-center">
                        <div class="col-4"><i class="fas fa-users"></i></div>
                        <div class="col-8 text-right">
                            <h4><?php echo $stats['total_students']; ?></h4>
                            <p>Total Students</p>
                            <a href="index.php?page=all-student" class="card-link">View All <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="summary-card">
                    <div class="row align-items-center">
                        <div class="col-4"><i class="fas fa-user-friends"></i></div>
                        <div class="col-8 text-right">
                            <h4><?php echo $stats['total_users']; ?></h4>
                            <p>Total Users</p>
                            <a href="index.php?page=all-users" class="card-link">View All <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="summary-card">
                    <div class="row align-items-center">
                        <div class="col-4"><i class="fas fa-hourglass-half"></i></div>
                        <div class="col-8 text-right">
                            <h4><?php echo $stats['pending_registrations']; ?></h4>
                            <p>Pending Registrations</p>
                            <a href="index.php?page=approve-registrations" class="card-link">Review <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mt-4">
                <div class="summary-card">
                    <div class="row align-items-center">
                        <div class="col-4">
                            <img src="images/<?php echo htmlspecialchars($userrow['photo']); ?>" alt="User Photo" style="width: 50px; border-radius: 50%;">
                        </div>
                        <div class="col-8 text-right">
                            <h4><?php echo ucwords(htmlspecialchars($userrow['name'])); ?></h4>
                            <p>Your Profile</p>
                            <a href="index.php?page=user-profile" class="card-link">View Profile <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-venus-mars"></i> Gender Distribution
                    </div>
                    <div class="card-body">
                        <canvas id="genderChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-chalkboard"></i> Class Distribution
                    </div>
                    <div class="card-body">
                        <canvas id="classChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-user-graduate"></i> Age Distribution
                    </div>
                    <div class="card-body">
                        <canvas id="ageChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-clock"></i> Recent Activity
                    </div>
                    <div class="card-body recent-activity">
                        <ul class="list-group">
                            <?php while ($row = mysqli_fetch_assoc($recent_activity)) { ?>
                                <li class="list-group-item">
                                    <strong><?php echo htmlspecialchars($row['name']); ?></strong> (ID: <?php echo htmlspecialchars($row['student_id']); ?>) <br>
                                    Approved in <?php echo htmlspecialchars($row['class']); ?>-<?php echo htmlspecialchars($row['section']); ?> on <?php echo date('d-M-Y', strtotime($row['datetime'])); ?>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Gender Chart (Donut)
        const genderCtx = document.getElementById('genderChart').getContext('2d');
        const genderChart = new Chart(genderCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_keys($stats['gender_distribution'])); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_values($stats['gender_distribution'])); ?>,
                    backgroundColor: ['#5f2985', '#7b3aa8', '#a66dd4'],
                    borderColor: ['#fff'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: { backgroundColor: '#5f2985' }
                },
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }
        });

        // Class Chart (Bar with Gradient)
        const classCtx = document.getElementById('classChart').getContext('2d');
        const classGradient = classCtx.createLinearGradient(0, 0, 0, 400);
        classGradient.addColorStop(0, '#5f2985');
        classGradient.addColorStop(1, '#7b3aa8');
        const classChart = new Chart(classCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($stats['class_distribution'])); ?>,
                datasets: [{
                    label: 'Students per Class',
                    data: <?php echo json_encode(array_values($stats['class_distribution'])); ?>,
                    backgroundColor: classGradient,
                    borderColor: '#5f2985',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                },
                plugins: {
                    tooltip: { backgroundColor: '#5f2985' }
                },
                animation: {
                    duration: 1500,
                    easing: 'easeInOutQuart'
                }
            }
        });

        // Age Chart (Line with Gradient Fill)
        const ageCtx = document.getElementById('ageChart').getContext('2d');
        const ageGradient = ageCtx.createLinearGradient(0, 0, 0, 300);
        ageGradient.addColorStop(0, 'rgba(95, 41, 133, 0.4)');
        ageGradient.addColorStop(1, 'rgba(95, 41, 133, 0)');
        const ageChart = new Chart(ageCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_keys($stats['age_distribution'])); ?>,
                datasets: [{
                    label: 'Students by Age Group',
                    data: <?php echo json_encode(array_values($stats['age_distribution'])); ?>,
                    backgroundColor: ageGradient,
                    borderColor: '#5f2985',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                },
                plugins: {
                    tooltip: { backgroundColor: '#5f2985' }
                },
                animation: {
                    duration: 1500,
                    easing: 'easeInOutQuart'
                }
            }
        });
    </script>
</body>
</html>