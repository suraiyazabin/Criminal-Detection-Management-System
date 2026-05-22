<?php

session_start();
require_once '../models/Connect.php';
require_once '../models/CdmsModel.php';
require_once '../models/Close.php';

$action = $_POST['action'] ?? $_GET['action'] ?? 'login';

if ($action === 'logout') {
    session_destroy();
    header('Location: AuthController.php');
    exit;
}

if ($action === 'register_save') {
    $name        = htmlspecialchars(trim($_POST['name']         ?? ''));
    $email       = htmlspecialchars(trim($_POST['email']        ?? ''));
    $phone       = htmlspecialchars(trim($_POST['phone']        ?? ''));
    $password    = $_POST['password']                           ?? '';
    $role        = htmlspecialchars(trim($_POST['role']         ?? 'officer'));
    $badgeNumber = htmlspecialchars(trim($_POST['badge_number'] ?? ''));

    if (!$name || !$email || !$password || !$badgeNumber) {
        $_SESSION['error'] = 'Name, email, password and badge number are required.';
        header('Location: AuthController.php?action=register'); exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Invalid email address.';
        header('Location: AuthController.php?action=register'); exit;
    }
    if (strlen($password) < 8) {
        $_SESSION['error'] = 'Password must be at least 8 characters.';
        header('Location: AuthController.php?action=register'); exit;
    }
    if (!in_array($role, ['officer','detective','admin'])) {
        $_SESSION['error'] = 'Invalid role selected.';
        header('Location: AuthController.php?action=register'); exit;
    }

    $conn = connect();
    if (emailExists($conn, $email)) {
        $_SESSION['error'] = 'Email already registered.';
        close($conn);
        header('Location: AuthController.php?action=register'); exit;
    }

    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    registerUser($conn, $name, $email, $passwordHash, $phone, $role, $badgeNumber);
    close($conn);

    $_SESSION['msg'] = 'Account created successfully. You may now log in.';
    header('Location: AuthController.php'); exit;
}

if ($action === 'login_save') {
    $email    = htmlspecialchars(trim($_POST['email']    ?? ''));
    $password = $_POST['password']                       ?? '';

    if (!$email || !$password) {
        $_SESSION['error'] = 'Please fill in all fields.';
        header('Location: AuthController.php'); exit;
    }

    $conn = connect();
    $user = getUserByEmail($conn, $email);
    close($conn);

    if (!$user || !password_verify($password, $user['password_hash'])) {
        $_SESSION['error'] = 'Invalid credentials.';
        header('Location: AuthController.php'); exit;
    }
    if (!$user['is_active']) {
        $_SESSION['error'] = 'Your account has been deactivated.';
        header('Location: AuthController.php'); exit;
    }

    $_SESSION['user_id']   = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['badge']     = $user['badge_number'];

    header('Location: DashboardController.php'); exit;
}

if ($action === 'change_password') {
    require_once 'auth_guard.php';

    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password']     ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!$current || !$new || !$confirm) {
        $_SESSION['error'] = 'All password fields are required.';
        header('Location: DashboardController.php?view=profile'); exit;
    }
    if ($new !== $confirm) {
        $_SESSION['error'] = 'New passwords do not match.';
        header('Location: DashboardController.php?view=profile'); exit;
    }
    if (strlen($new) < 8) {
        $_SESSION['error'] = 'Password must be at least 8 characters.';
        header('Location: DashboardController.php?view=profile'); exit;
    }

    $conn    = connect();
    $profile = getUserProfile($conn, $_SESSION['user_id']);

    if (!password_verify($current, $profile['password_hash'])) {
        $_SESSION['error'] = 'Current password is incorrect.';
        close($conn);
        header('Location: DashboardController.php?view=profile'); exit;
    }

    changePassword($conn, $_SESSION['user_id'], password_hash($new, PASSWORD_BCRYPT));
    close($conn);

    $_SESSION['msg'] = 'Password changed successfully.';
    header('Location: DashboardController.php?view=profile'); exit;
}

require_once '../views/auth.php';
