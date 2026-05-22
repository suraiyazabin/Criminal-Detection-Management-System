<?php

require_once 'auth_guard.php';
require_once '../models/Connect.php';
require_once '../models/CdmsModel.php';
require_once '../models/Close.php';

$action = $_POST['action'] ?? $_GET['action'] ?? 'list';

// ── SAVE new criminal ────────────────────────────────────────
if ($action === 'save') {
    $fullName    = htmlspecialchars(trim($_POST['full_name']    ?? ''));
    $alias       = htmlspecialchars(trim($_POST['alias']        ?? ''));
    $dob         = htmlspecialchars(trim($_POST['dob']          ?? ''));
    $gender      = htmlspecialchars(trim($_POST['gender']       ?? ''));
    $nationality = htmlspecialchars(trim($_POST['nationality']  ?? ''));
    $address     = htmlspecialchars(trim($_POST['address']      ?? ''));
    $threatLevel = htmlspecialchars(trim($_POST['threat_level'] ?? 'medium'));
    $status      = htmlspecialchars(trim($_POST['status']       ?? 'wanted'));
    $description = htmlspecialchars(trim($_POST['description']  ?? ''));

    if (!$fullName) {
        $_SESSION['error'] = 'Full name is required.';
        header('Location: CriminalController.php?action=create'); exit;
    }

    $photoPath = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif'])) {
            if (!is_dir('../uploads/photos')) mkdir('../uploads/photos', 0755, true);
            $filename  = uniqid('crim_', true) . '.' . $ext;
            move_uploaded_file($_FILES['photo']['tmp_name'], '../uploads/photos/' . $filename);
            $photoPath = 'uploads/photos/' . $filename;
        }
    }

    $conn = connect();
    createCriminal($conn, $fullName, $alias, $dob, $gender, $nationality, $address, $photoPath, $threatLevel, $status, $description, $_SESSION['user_id']);
    close($conn);

    $_SESSION['msg'] = 'Criminal record created.';
    header('Location: CriminalController.php'); exit;
}

// ── UPDATE ───────────────────────────────────────────────────
if ($action === 'update') {
    $criminalId  = (int)($_POST['criminal_id'] ?? 0);
    $fullName    = htmlspecialchars(trim($_POST['full_name']    ?? ''));
    $alias       = htmlspecialchars(trim($_POST['alias']        ?? ''));
    $dob         = htmlspecialchars(trim($_POST['dob']          ?? ''));
    $gender      = htmlspecialchars(trim($_POST['gender']       ?? ''));
    $nationality = htmlspecialchars(trim($_POST['nationality']  ?? ''));
    $address     = htmlspecialchars(trim($_POST['address']      ?? ''));
    $threatLevel = htmlspecialchars(trim($_POST['threat_level'] ?? 'medium'));
    $status      = htmlspecialchars(trim($_POST['status']       ?? 'wanted'));
    $description = htmlspecialchars(trim($_POST['description']  ?? ''));

    if (!$criminalId || !$fullName) {
        $_SESSION['error'] = 'Full name is required.';
        header('Location: CriminalController.php?action=edit&id=' . $criminalId); exit;
    }

    $conn      = connect();
    $criminal  = getCriminalById($conn, $criminalId);
    $photoPath = $criminal['photo_path'];

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif'])) {
            $filename  = uniqid('crim_', true) . '.' . $ext;
            move_uploaded_file($_FILES['photo']['tmp_name'], '../uploads/photos/' . $filename);
            $photoPath = 'uploads/photos/' . $filename;
        }
    }

    updateCriminal($conn, $criminalId, $fullName, $alias, $dob, $gender, $nationality, $address, $photoPath, $threatLevel, $status, $description);
    close($conn);

    $_SESSION['msg'] = 'Criminal record updated.';
    header('Location: CriminalController.php'); exit;
}

// ── DELETE ───────────────────────────────────────────────────
if ($action === 'delete') {
    $criminalId = (int)($_POST['criminal_id'] ?? 0);
    if ($criminalId) {
        $conn = connect();
        deleteCriminal($conn, $criminalId);
        close($conn);
        $_SESSION['msg'] = 'Criminal record deleted.';
    }
    header('Location: CriminalController.php'); exit;
}

// ── AJAX SEARCH ──────────────────────────────────────────────
if ($action === 'search_ajax') {
    $query     = htmlspecialchars(trim($_GET['q'] ?? ''));
    $conn      = connect();
    $criminals = searchCriminals($conn, $query);
    close($conn);
    header('Content-Type: application/json');
    echo json_encode($criminals);
    exit;
}

// ── LOAD VIEW ────────────────────────────────────────────────
$conn = connect();

if ($action === 'create') {
    $criminal = array();
    $cases    = array();
    $arrests  = array();
} elseif ($action === 'edit' || $action === 'view') {
    $id       = (int)($_GET['id'] ?? 0);
    $criminal = getCriminalById($conn, $id);
    if (!$criminal) { close($conn); header('Location: CriminalController.php'); exit; }
    $cases   = getCriminalCases($conn, $id);
    $arrests = getArrestsByCriminalId($conn, $id);
} else {
    $action        = 'list';
    $statusFilter  = htmlspecialchars(trim($_GET['status'] ?? ''));
    $criminals     = getAllCriminals($conn, $statusFilter);
    $criminal      = array();
}

close($conn);
require_once '../views/criminal.php';