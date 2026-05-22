<?php

$action  = $action  ?? $view ?? 'login';
$profile = isset($profile) ? $profile : array();

$flashMsg   = $_SESSION['msg']   ?? '';
$flashError = $_SESSION['error'] ?? '';
$_SESSION['msg']   = '';
$_SESSION['error'] = '';

$isAuthPage = in_array($action, ['login', 'register']);

if (!$isAuthPage) {
    $pageTitle  = 'My Profile';
    $activePage = 'profile';
    include __DIR__ . '/header.php';
} else {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $action === 'register' ? 'Register' : 'Login'; ?> | CDMS</title>
    <link rel="stylesheet" href="../views/external.css">
</head>
<body>
<?php } ?>

<?php if ($action === 'login'): ?>

<div class="auth-wrapper">
    <div class="auth-box">
        <div class="auth-logo">🔍</div>
        <h2>Criminal Detection<br>Management System</h2>
        <p class="auth-sub">Officer Login</p>
        <?php if ($flashMsg):   ?><div class="alert alert-success"><?php echo htmlspecialchars($flashMsg);   ?></div><?php endif; ?>
        <?php if ($flashError): ?><div class="alert alert-danger"><?php  echo htmlspecialchars($flashError); ?></div><?php endif; ?>

        <form action="AuthController.php?action=login_save" method="post"
              onsubmit="return validateLogin(this)" novalidate>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" placeholder="officer@cdms.gov" autocomplete="email">
                <span class="err-msg" id="loginEmailErr"></span>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" autocomplete="current-password">
                <span class="err-msg" id="loginPassErr"></span>
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg">Login</button>
        </form>
        <div class="auth-footer">
            New officer? <a href="AuthController.php?action=register">Register here</a>
        </div>
    </div>
</div>

<?php elseif ($action === 'register'): ?>

<div class="auth-wrapper">
    <div class="auth-box auth-wide">
        <div class="auth-logo">🔍</div>
        <h2>Register Officer Account</h2>
        <?php if ($flashMsg):   ?><div class="alert alert-success"><?php echo htmlspecialchars($flashMsg);   ?></div><?php endif; ?>
        <?php if ($flashError): ?><div class="alert alert-danger"><?php  echo htmlspecialchars($flashError); ?></div><?php endif; ?>

        <form action="AuthController.php?action=register_save" method="post"
              onsubmit="return validateRegister(this)" novalidate>

            <div class="section-title">Personal Information</div>
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" name="name" id="name">
                    <span class="err-msg" id="regNameErr"></span>
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" name="phone" id="phone">
                </div>
            </div>
            <div class="form-group">
                <label for="reg_email">Email Address *</label>
                <input type="email" name="email" id="reg_email">
                <span class="err-msg" id="regEmailErr"></span>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password * <small>(min 8 chars)</small></label>
                    <input type="password" name="password" id="password">
                    <span class="err-msg" id="regPassErr"></span>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" name="confirm_password" id="confirm_password">
                    <span class="err-msg" id="regConfirmErr"></span>
                </div>
            </div>

            <hr class="auth-divider">
            <div class="section-title">Officer Details</div>

            <div class="form-row">
                <div class="form-group">
                    <label for="badge_number">Badge Number *</label>
                    <input type="text" name="badge_number" id="badge_number" placeholder="e.g. OFF-1042">
                    <span class="err-msg" id="regBadgeErr"></span>
                </div>
                <div class="form-group">
                    <label for="role">Role *</label>
                    <select name="role" id="role">
                        <option value="officer">Officer</option>
                        <option value="detective">Detective</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg">Register</button>
        </form>
        <div class="auth-footer">
            Already registered? <a href="AuthController.php">Login</a>
        </div>
    </div>
</div>

<?php elseif ($action === 'profile'): ?>

<div class="page-header"><h1>My Profile</h1></div>

<div class="card">
    <h2>Personal & Officer Information</h2>
    <form action="DashboardController.php" method="post">
        <input type="hidden" name="action" value="update_profile">
        <div class="form-row">
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="name"
                       value="<?php echo htmlspecialchars($profile['name'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone"
                       value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Badge Number *</label>
                <input type="text" name="badge_number"
                       value="<?php echo htmlspecialchars($profile['badge_number'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Role</label>
                <input type="text" value="<?php echo ucfirst($profile['role'] ?? ''); ?>" disabled
                       style="background:#f5f5f5;cursor:not-allowed">
            </div>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="text" value="<?php echo htmlspecialchars($profile['email'] ?? ''); ?>" disabled
                   style="background:#f5f5f5;cursor:not-allowed">
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
</div>

<div class="card">
    <h2>Change Password</h2>
    <form action="AuthController.php?action=change_password" method="post"
          onsubmit="return checkPassMatch()">
        <div class="form-group">
            <label>Current Password</label>
            <input type="password" name="current_password" required>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>New Password <small>(min 8 chars)</small></label>
                <input type="password" name="new_password" id="newPass" required>
            </div>
            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" id="confirmPass" required>
                <span class="err-msg" id="confirmPassErr"></span>
            </div>
        </div>
        <button type="submit" class="btn btn-warning">Change Password</button>
    </form>
</div>

<?php endif; ?>

<?php if ($isAuthPage): ?>
<script src="../views/external.js"></script>
</body>
</html>
<?php else: ?>
<?php include __DIR__ . '/footer.php'; ?>
<?php endif; ?>