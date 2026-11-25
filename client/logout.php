<?php
session_start();
unset($_SESSION['client_login']);
unset($_SESSION['client_id']);
header('Location: index.php');
exit();
?>