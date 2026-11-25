<?php
define('DBHOST', 'localhost');
define('DBUSER', 'root');
define('DBPASS', ''); // Explicitly define empty password for clarity
define('DBNAME', 'student');

$db_con = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);

if (!$db_con) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>