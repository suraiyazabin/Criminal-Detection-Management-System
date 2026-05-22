<?php

require_once 'auth_guard.php';
require_once '../models/Connect.php';
require_once '../models/CdmsModel.php';
require_once '../models/Close.php';

$action = $_POST['action'] ?? $_GET['action'] ?? 'list';

// ── SAVE new case ────────────────────────────────────────────
if ($action === 'save') {
    $caseNumber   = htmlspecialchars(trim($_POST['case_number']   ?? ''));
    $title        = htmlspecialchars(trim($_POST['title']         ?? ''));
    $description  = htmlspecialchars(trim($_POST['description']   ?? ''));
    $crimeType    = htmlspecialchars(trim($_POST['crime_type']     ?? ''));
    $location     = htmlspecialchars(trim($_POST['location']       ?? ''));
    $incidentDate = htmlspecialchars(trim($_POST['incident_date'] ?? ''));
    $status       = htmlspecialchars(trim($_POST['status']         ?? 'open'));
    $assignedTo   = (int)($_POST['assigned_to'] ?? 0);

    if (!$caseNumber || !$title) {
        $_SESSION['error'] = 'Case number and title are required.';
        header('Location: CaseController.php?action=create'); exit;
    }

    $conn = connect();
    if (caseNumberExists($conn, $caseNumber)) {
        $_SESSION['error'] = 'Case number already exists.';
        close($conn);
        header('Location: CaseController.php?action=create'); exit;
    }
    createCase($conn, $caseNumber, $title, $description, $crimeType, $location, $incidentDate, $status, $assignedTo, $_SESSION['user_id']);
    close($conn);

    $_SESSION['msg'] = 'Case created successfully.';
    header('Location: CaseController.php'); exit;
}

// ── UPDATE ───────────────────────────────────────────────────
if ($action === 'update') {
    $caseId       = (int)($_POST['case_id']       ?? 0);
    $title        = htmlspecialchars(trim($_POST['title']         ?? ''));
    $description  = htmlspecialchars(trim($_POST['description']   ?? ''));
    $crimeType    = htmlspecialchars(trim($_POST['crime_type']     ?? ''));
    $location     = htmlspecialchars(trim($_POST['location']       ?? ''));
    $incidentDate = htmlspecialchars(trim($_POST['incident_date'] ?? ''));
    $status       = htmlspecialchars(trim($_POST['status']         ?? 'open'));
    $assignedTo   = (int)($_POST['assigned_to']   ?? 0);

    if (!$caseId || !$title) {
        $_SESSION['error'] = 'Title is required.';
        header('Location: CaseController.php?action=edit&id=' . $caseId); exit;
    }

    $conn = connect();
    updateCase($conn, $caseId, $title, $description, $crimeType, $location, $incidentDate, $status, $assignedTo);
    close($conn);

    $_SESSION['msg'] = 'Case updated.';
    header('Location: CaseController.php?action=view&id=' . $caseId); exit;
}

// ── DELETE ───────────────────────────────────────────────────
if ($action === 'delete') {
    $caseId = (int)($_POST['case_id'] ?? 0);
    if ($caseId) {
        $conn = connect();
        deleteCase($conn, $caseId);
        close($conn);
        $_SESSION['msg'] = 'Case deleted.';
    }
    header('Location: CaseController.php'); exit;
}

// ── LINK CRIMINAL ────────────────────────────────────────────
if ($action === 'link_criminal') {
    $caseId     = (int)($_POST['case_id']     ?? 0);
    $criminalId = (int)($_POST['criminal_id'] ?? 0);
    $roleInCase = htmlspecialchars(trim($_POST['role_in_case'] ?? ''));
    if ($caseId && $criminalId) {
        $conn = connect();
        linkCriminalToCase($conn, $caseId, $criminalId, $roleInCase);
        close($conn);
        $_SESSION['msg'] = 'Criminal linked to case.';
    }
    header('Location: CaseController.php?action=view&id=' . $caseId); exit;
}

// ── ADD EVIDENCE ─────────────────────────────────────────────
if ($action === 'add_evidence') {
    $caseId       = (int)($_POST['case_id']        ?? 0);
    $title        = htmlspecialchars(trim($_POST['ev_title']       ?? ''));
    $description  = htmlspecialchars(trim($_POST['ev_description'] ?? ''));
    $evidenceType = htmlspecialchars(trim($_POST['evidence_type']  ?? 'document'));

    if (!$caseId || !$title) {
        $_SESSION['error'] = 'Evidence title is required.';
        header('Location: CaseController.php?action=view&id=' . $caseId); exit;
    }

    $filePath = '';
    if (isset($_FILES['ev_file']) && $_FILES['ev_file']['error'] === UPLOAD_ERR_OK) {
        $ext     = strtolower(pathinfo($_FILES['ev_file']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','pdf','doc','docx','mp4','avi'];
        if (in_array($ext, $allowed)) {
            if (!is_dir('../uploads/evidence')) mkdir('../uploads/evidence', 0755, true);
            $filename = uniqid('ev_', true) . '.' . $ext;
            move_uploaded_file($_FILES['ev_file']['tmp_name'], '../uploads/evidence/' . $filename);
            $filePath = 'uploads/evidence/' . $filename;
        }
    }

    $conn = connect();
    addEvidence($conn, $caseId, $title, $description, $filePath, $evidenceType, $_SESSION['user_id']);
    close($conn);

    $_SESSION['msg'] = 'Evidence added.';
    header('Location: CaseController.php?action=view&id=' . $caseId); exit;
}

// ── ADD REPORT ───────────────────────────────────────────────
if ($action === 'add_report') {
    $caseId     = (int)($_POST['case_id']      ?? 0);
    $title      = htmlspecialchars(trim($_POST['rp_title']      ?? ''));
    $content    = htmlspecialchars(trim($_POST['rp_content']    ?? ''));
    $reportType = htmlspecialchars(trim($_POST['report_type']   ?? 'progress'));

    if (!$caseId || !$title || !$content) {
        $_SESSION['error'] = 'Report title and content are required.';
        header('Location: CaseController.php?action=view&id=' . $caseId); exit;
    }

    $conn = connect();
    addReport($conn, $caseId, $_SESSION['user_id'], $title, $content, $reportType);
    close($conn);

    $_SESSION['msg'] = 'Report added.';
    header('Location: CaseController.php?action=view&id=' . $caseId); exit;
}

// ── RECORD ARREST ────────────────────────────────────────────
if ($action === 'record_arrest') {
    $criminalId = (int)($_POST['criminal_id']  ?? 0);
    $caseId     = (int)($_POST['case_id']      ?? 0);
    $arrestDate = htmlspecialchars(trim($_POST['arrest_date']  ?? ''));
    $location   = htmlspecialchars(trim($_POST['arrest_location'] ?? ''));
    $notes      = htmlspecialchars(trim($_POST['arrest_notes'] ?? ''));

    if (!$criminalId || !$caseId || !$arrestDate) {
        $_SESSION['error'] = 'Criminal, case, and arrest date are required.';
        header('Location: CaseController.php?action=view&id=' . $caseId); exit;
    }

    $conn = connect();
    recordArrest($conn, $criminalId, $caseId, $_SESSION['user_id'], $arrestDate, $location, $notes);
    close($conn);

    $_SESSION['msg'] = 'Arrest recorded and criminal status updated.';
    header('Location: CaseController.php?action=view&id=' . $caseId); exit;
}

// ── DELETE EVIDENCE ───────────────────────────────────────────
if ($action === 'delete_evidence') {
    $evidenceId = (int)($_POST['evidence_id'] ?? 0);
    $caseId     = (int)($_POST['case_id']     ?? 0);
    if ($evidenceId) {
        $conn = connect();
        deleteEvidence($conn, $evidenceId);
        close($conn);
        $_SESSION['msg'] = 'Evidence deleted.';
    }
    header('Location: CaseController.php?action=view&id=' . $caseId); exit;
}

// ── LOAD VIEW ────────────────────────────────────────────────
$conn = connect();

if ($action === 'create') {
    $officers = getAllOfficers($conn);
    $case     = array();
    $criminals_in_case = array();
    $all_criminals     = getAllCriminals($conn);
    $evidence = array();
    $reports  = array();
} elseif ($action === 'edit') {
    $id   = (int)($_GET['id'] ?? 0);
    $case = getCaseById($conn, $id);
    if (!$case) { close($conn); header('Location: CaseController.php'); exit; }
    $officers          = getAllOfficers($conn);
    $criminals_in_case = getCaseCriminals($conn, $id);
    $all_criminals     = getAllCriminals($conn);
    $evidence          = getEvidenceByCaseId($conn, $id);
    $reports           = getReportsByCaseId($conn, $id);
} elseif ($action === 'view') {
    $id   = (int)($_GET['id'] ?? 0);
    $case = getCaseById($conn, $id);
    if (!$case) { close($conn); header('Location: CaseController.php'); exit; }
    $officers          = getAllOfficers($conn);
    $criminals_in_case = getCaseCriminals($conn, $id);
    $all_criminals     = getAllCriminals($conn);
    $evidence          = getEvidenceByCaseId($conn, $id);
    $reports           = getReportsByCaseId($conn, $id);
} else {
    $action       = 'list';
    $statusFilter = htmlspecialchars(trim($_GET['status'] ?? ''));
    $cases        = getAllCases($conn, $statusFilter);
    $case         = array();
    $officers     = array();
    $criminals_in_case = array();
    $all_criminals     = array();
    $evidence = array();
    $reports  = array();
}

close($conn);
require_once '../views/case.php';