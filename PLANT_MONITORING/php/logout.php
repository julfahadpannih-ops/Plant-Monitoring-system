<?php
session_start();

// Burahin ang lahat ng session variables
$_SESSION = array();

// I-destroy ang session
session_destroy();

// I-redirect pabalik sa login page
header("location: ../frontend/login.html");
exit;
?>