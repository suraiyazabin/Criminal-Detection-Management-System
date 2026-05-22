<?php

require_once 'auth_guard.php';
require_once '../models/Connect.php';
require_once '../models/CdmsModel.php';
require_once '../models/Close.php';

$action = $_POST['action'] ?? '';
$view   = $_GET['view']    ?? 'dashboard';

// ── UPDATE PROFILE ───────────────────────────────────────────
if ($action === 'update_profile') {
    $name        = htmlspecialchars(trim($_POST['name']         ?? ''));
    $phone       = htmlspecialchars(trim($_POST['phone']        ?? ''));
    $badgeNumber = htmlspecialchars(trim($_POST['badge_number'] ?? ''));

    if (!$name || !$badgeNumber) {
        $_SESSION['error'] = 'Name and badge number are required.';
        header('Location: DashboardController.php?view=profile'); exit;
    }

    $conn = connect();
    updateUserProfile($conn, $_SESSION['user_id'], $name, $phone, $badgeNumber);
    close($conn);

    $_SESSION['user_name'] = $name;
    $_SESSION['badge']     = $badgeNumber;
    $_SESSION['msg']       = 'Profile updated successfully.';
    header('Location: DashboardController.php?view=profile'); exit;
}

$conn = connect();

if ($view === 'profile') {
    $profile = getUserProfile($conn, $_SESSION['user_id']);
} else {
    $stats       = getDashboardStats($conn);
    $recentCases = getRecentCases($conn, 6);
    $mostWanted  = getMostWanted($conn, 5);
    $chartData   = getCasesPerMonth($conn);
}

close($conn);
require_once '../views/dashboard.php';