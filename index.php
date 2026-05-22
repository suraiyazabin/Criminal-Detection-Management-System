<?php
session_start();
if (!empty($_SESSION['user_id'])) {
    header('Location: controllers/DashboardController.php');
} else {
    header('Location: controllers/AuthController.php');
}
exit;