<?php

if (session_status() === PHP_SESSION_NONE)
session_start();

$flashMsg   = $_SESSION['msg']   ?? '';
$flashError = $_SESSION['error'] ?? '';
$_SESSION['msg']   = '';
$_SESSION['error'] = '';

$pageTitle  = $pageTitle  ?? 'CDMS';
$activePage = $activePage ?? '';

function navClass($page) {
    global $activePage;
    return $activePage === $page ? ' class="active"' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($pageTitle); ?> | CDMS</title>
    <link rel="stylesheet" href="../views/external.css">
    <script src="../views/external.js"></script>
</head>
<body>

<nav class="navbar">
    <div class="nav-brand">🔍 CDMS</div>
    <div class="nav-links">
        <a href="DashboardController.php"<?php echo navClass('dashboard'); ?>>Dashboard</a>
        <a href="CriminalController.php"<?php echo navClass('criminals'); ?>>Criminals</a>
        <a href="CaseController.php"<?php echo navClass('cases'); ?>>Cases</a>
        <a href="DashboardController.php?view=profile"<?php echo navClass('profile'); ?>>
            👤 <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Profile'); ?>
            <span class="badge-role"><?php echo ucfirst($_SESSION['user_role'] ?? ''); ?></span>
        </a>
        <a href="AuthController.php?action=logout" class="nav-logout">Logout</a>
    </div>
</nav>

<div class="container">
<?php if ($flashMsg):   ?><div class="alert alert-success"><?php echo htmlspecialchars($flashMsg);   ?></div><?php endif; ?>
<?php if ($flashError): ?><div class="alert alert-danger"><?php  echo htmlspecialchars($flashError); ?></div><?php endif; ?>