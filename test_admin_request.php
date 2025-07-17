<?php
// test_admin_request.php - Test admin status update request
session_start();

// Set admin session for testing
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_id'] = 1;
$_SESSION['admin_kullanici_adi'] = 'test_admin';

// Simulate POST request
$_POST['talep_id'] = 1;
$_POST['action'] = 'onayla';

// Include the debug version
include('admin/debug_admin_status.php');
?>
