<?php
require_once '../admin/db_con.php';
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['client_login'])) {
    header('Location: login.php');
    exit();
}

// Get client information
$client_id = $_SESSION['client_id'];
$query = "SELECT * FROM users WHERE id = ? LIMIT 1";
$stmt = mysqli_prepare($db_con, $query);

if ($stmt === false) {
    die("Database error: " . mysqli_error($db_con));
}

mysqli_stmt_bind_param($stmt, "i", $client_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$client = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$client) {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Get student statistics and registrations
$query = "(
    SELECT 
        pr.id,
        pr.student_id,
        pr.name,
        pr.grade_level AS class,
        'Pending' AS section,
        pr.status,
        'pending_registration' AS type,
        pr.datetime
    FROM pending_registrations pr
    WHERE pr.parent_id = ?
)
UNION ALL
(
    SELECT 
        si.id,
        si.student_id,
        si.name,
        si.class,
        si.section,
        'approved' AS status,
        'student_info' AS type,
        si.datetime
    FROM student_info si
    WHERE si.pcontact IN (
        SELECT pcontact FROM pending_registrations WHERE parent_id = ?
    )
)
ORDER BY datetime DESC";

$stmt = mysqli_prepare($db_con, $query);

if ($stmt === false) {
    die("Database error: " . mysqli_error($db_con));
}

mysqli_stmt_bind_param($stmt, "ii", $client_id, $client_id);
mysqli_stmt_execute($stmt);
$students = mysqli_stmt_get_result($stmt);

// Calculate statistics
$stats = ['total' => 0, 'pending' => 0, 'approved' => 0, 'declined' => 0];
mysqli_data_seek($students, 0); // Reset pointer
while ($row = mysqli_fetch_assoc($students)) {
    $stats['total']++;
    $stats[strtolower($row['status'])]++;
}
mysqli_data_seek($students, 0); // Reset pointer for display
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Dashboard - Lebawi International Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --primary: #5f2985;
            --primary-light: #7b3aa8;
            --secondary: #ffffff;
            --accent: #212529;
            --highlight: #f1c40f;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            color: var(--accent);
            overflow-x: hidden;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            height: 100%;
            background: var(--primary);
            color: var(--secondary);
            padding: 20px;
            transition: transform 0.3s ease;
            z-index: 1000;
        }
        .sidebar.hidden {
            transform: translateX(-250px);
        }
        .sidebar h4 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--highlight);
        }
        .sidebar .nav-link {
            color: var(--secondary);
            padding: 10px 15px;
            border-radius: 8px;
            transition: background 0.3s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: var(--primary-light);
            color: var(--highlight);
        }
        .main-content {
            margin-left: 250px;
            padding: 30px;
            transition: margin-left 0.3s ease;
        }
        .main-content.full {
            margin-left: 0;
        }
        .toggle-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1100;
            background: var(--primary);
            color: var(--secondary);
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: var(--secondary);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            margin-bottom: 30px;
        }
        .summary-card {
            background: var(--secondary);
            border-radius: 15px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
            padding: 20px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .summary-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.2);
        }
        .summary-card i {
            font-size: 2.5rem;
            color: var(--primary);
        }
        .student-tile {
            background: var(--secondary);
            border-radius: 15px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
            padding: 20px;
            position: relative;
            overflow: hidden;
            transition: transform 0.3s;
        }
        .student-tile:hover {
            transform: scale(1.05);
        }
        .status-pending {
            border-left: 5px solid #ffc107;
        }
        .status-approved {
            border-left: 5px solid #28a745;
        }
        .status-declined {
            border-left: 5px solid #dc3545;
        }
        .badge-pending {
            background: #ffc107;
            color: #212529;
        }
        .badge-approved {
            background: #28a745;
        }
        .badge-declined {
            background: #dc3545;
        }
        .chart-container {
            background: var(--secondary);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        }
        .btn-highlight {
            background: var(--highlight);
            color: var(--accent);
            border: none;
            border-radius: 50px;
            padding: 10px 20px;
            transition: background 0.3s, transform 0.3s;
        }
        .btn-highlight:hover {
            background: #d4ac0d;
            transform: scale(1.1);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate {
            animation: fadeIn 0.8s ease-out;
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-250px);
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
            .toggle-btn {
                display: block;
            }
        }
    </style>
</head>
<body>
    <button class="toggle-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
    <div class="sidebar" id="sidebar">
        <h4 class="mb-4">Lebawi Academy</h4>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="dashboard.php"><i class="fas fa-home me-2"></i> Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="register.php"><i class="fas fa-user-plus me-2"></i> Register Student</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
            </li>
        </ul>
    </div>

    <div class="main-content" id="main-content">
        <div class="dashboard-header" data-aos="fade-down">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2><i class="fas fa-user"></i> Welcome, <?php echo htmlspecialchars($client['name']); ?></h2>
                        <p>Manage your student registrations at Lebawi International Academy</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="register.php" class="btn btn-highlight"><i class="fas fa-user-plus"></i> Register New Student</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="row mb-4" data-aos="fade-up">
                <div class="col-md-3">
                    <div class="summary-card animate">
                        <i class="fas fa-users"></i>
                        <h4><?php echo $stats['total']; ?></h4>
                        <p>Total Registrations</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-card animate">
                        <i class="fas fa-hourglass-half"></i>
                        <h4><?php echo $stats['pending']; ?></h4>
                        <p>Pending</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-card animate">
                        <i class="fas fa-check-circle"></i>
                        <h4><?php echo $stats['approved']; ?></h4>
                        <p>Approved</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-card animate">
                        <i class="fas fa-times-circle"></i>
                        <h4><?php echo $stats['declined']; ?></h4>
                        <p>Declined</p>
                    </div>
                </div>
            </div>

            <div class="row mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="col-md-6">
                    <div class="chart-container animate">
                        <h5 class="mb-3">Registration Status</h5>
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="chart-container animate">
                        <h5 class="mb-3">Recent Activity</h5>
                        <ul class="list-group">
                            <?php
                            $count = 0;
                            mysqli_data_seek($students, 0);
                            while ($count < 3 && $student = mysqli_fetch_assoc($students)):
                            ?>
                                <li class="list-group-item">
                                    <strong><?php echo htmlspecialchars($student['name']); ?></strong> (<?php echo ucfirst($student['status']); ?>)<br>
                                    <small><?php echo date('d-M-Y', strtotime($student['datetime'])); ?></small>
                                </li>
                            <?php $count++; endwhile; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <h3 class="mb-4" data-aos="fade-up" data-aos-delay="200"><i class="fas fa-users"></i> Your Students</h3>
            <?php if (mysqli_num_rows($students) > 0): ?>
                <div class="row" data-aos="fade-up" data-aos-delay="300">
                    <?php
                    mysqli_data_seek($students, 0);
                    while ($student = mysqli_fetch_assoc($students)):
                        $status_class = 'status-' . strtolower($student['status']);
                        $badge_class = 'badge-' . strtolower($student['status']);
                    ?>
                        <div class="col-md-4 mb-4">
                            <div class="student-tile <?php echo $status_class; ?> animate">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5><?php echo htmlspecialchars($student['name']); ?></h5>
                                        <p><strong>ID:</strong> <?php echo htmlspecialchars($student['student_id']); ?></p>
                                        <p><strong>Grade:</strong> <?php echo htmlspecialchars($student['class']); ?></p>
                                        <?php if ($student['type'] === 'student_info'): ?>
                                            <p><strong>Section:</strong> <?php echo htmlspecialchars($student['section']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <span class="badge <?php echo $badge_class; ?>">
                                        <?php echo ucfirst($student['status']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info animate" data-aos="fade-up" data-aos-delay="300">
                    You haven't registered any students yet. <a href="register.php" class="alert-link">Register a new student</a>.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 1000, once: true });
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('hidden');
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('main-content').classList.toggle('full');
        }

        // Status Chart
        const ctx = document.getElementById('statusChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Approved', 'Declined'],
                datasets: [{
                    data: [<?php echo $stats['pending']; ?>, <?php echo $stats['approved']; ?>, <?php echo $stats['declined']; ?>],
                    backgroundColor: ['#ffc107', '#28a745', '#dc3545'],
                    borderColor: ['#fff'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: { backgroundColor: 'var(--primary)' }
                },
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }
        });
    </script>
</body>
</html>