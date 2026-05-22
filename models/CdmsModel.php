<?php

function getUserByEmail($conn, $email)
{
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ? AND is_active = 1");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

function emailExists($conn, $email)
{
    $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    return mysqli_stmt_num_rows($stmt) > 0;
}

function registerUser($conn, $name, $email, $passwordHash, $phone, $role, $badgeNumber)
{
    $stmt = mysqli_prepare($conn,
        "INSERT INTO users (name, email, password_hash, phone, role, badge_number)
         VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssssss", $name, $email, $passwordHash, $phone, $role, $badgeNumber);
    mysqli_stmt_execute($stmt);
    return mysqli_insert_id($conn);
}

function getUserProfile($conn, $userId)
{
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

function updateUserProfile($conn, $userId, $name, $phone, $badgeNumber)
{
    $stmt = mysqli_prepare($conn,
        "UPDATE users SET name = ?, phone = ?, badge_number = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "sssi", $name, $phone, $badgeNumber, $userId);
    return mysqli_stmt_execute($stmt);
}

function changePassword($conn, $userId, $newHash)
{
    $stmt = mysqli_prepare($conn, "UPDATE users SET password_hash = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "si", $newHash, $userId);
    return mysqli_stmt_execute($stmt);
}

function getAllCriminals($conn, $statusFilter = '')
{
    if ($statusFilter !== '') {
        $stmt = mysqli_prepare($conn,
            "SELECT c.*, u.name AS created_by_name
             FROM criminals c
             LEFT JOIN users u ON c.created_by = u.id
             WHERE c.status = ?
             ORDER BY c.created_at DESC");
        mysqli_stmt_bind_param($stmt, "s", $statusFilter);
    } else {
        $stmt = mysqli_prepare($conn,
            "SELECT c.*, u.name AS created_by_name
             FROM criminals c
             LEFT JOIN users u ON c.created_by = u.id
             ORDER BY c.created_at DESC");
    }
    mysqli_stmt_execute($stmt);
    $result    = mysqli_stmt_get_result($stmt);
    $criminals = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $criminals[] = $row;
    }
    return $criminals;
}

function getCriminalById($conn, $criminalId)
{
    $stmt = mysqli_prepare($conn,
        "SELECT c.*, u.name AS created_by_name
         FROM criminals c
         LEFT JOIN users u ON c.created_by = u.id
         WHERE c.id = ?");
    mysqli_stmt_bind_param($stmt, "i", $criminalId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

function createCriminal($conn, $fullName, $alias, $dob, $gender, $nationality, $address,
                         $photoPath, $threatLevel, $status, $description, $createdBy)
{
    $stmt = mysqli_prepare($conn,
        "INSERT INTO criminals
            (full_name, alias, dob, gender, nationality, address,
             photo_path, threat_level, status, description, created_by)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssssssssssi",
        $fullName, $alias, $dob, $gender, $nationality, $address,
        $photoPath, $threatLevel, $status, $description, $createdBy);
    mysqli_stmt_execute($stmt);
    return mysqli_insert_id($conn);
}

function updateCriminal($conn, $criminalId, $fullName, $alias, $dob, $gender, $nationality,
                         $address, $photoPath, $threatLevel, $status, $description)
{
    $stmt = mysqli_prepare($conn,
        "UPDATE criminals
         SET full_name = ?, alias = ?, dob = ?, gender = ?, nationality = ?,
             address = ?, photo_path = ?, threat_level = ?, status = ?, description = ?
         WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "ssssssssssi",
        $fullName, $alias, $dob, $gender, $nationality,
        $address, $photoPath, $threatLevel, $status, $description, $criminalId);
    return mysqli_stmt_execute($stmt);
}

function deleteCriminal($conn, $criminalId)
{
    $stmt = mysqli_prepare($conn, "DELETE FROM criminals WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $criminalId);
    return mysqli_stmt_execute($stmt);
}

function getCriminalCases($conn, $criminalId)
{
    $stmt = mysqli_prepare($conn,
        "SELECT c.*, cc.role_in_case
         FROM cases c
         JOIN case_criminals cc ON c.id = cc.case_id
         WHERE cc.criminal_id = ?
         ORDER BY c.created_at DESC");
    mysqli_stmt_bind_param($stmt, "i", $criminalId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $cases  = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $cases[] = $row;
    }
    return $cases;
}

function searchCriminals($conn, $query)
{
    $like = '%' . $query . '%';
    $stmt = mysqli_prepare($conn,
        "SELECT * FROM criminals
         WHERE full_name LIKE ? OR alias LIKE ? OR nationality LIKE ?
         ORDER BY full_name ASC");
    mysqli_stmt_bind_param($stmt, "sss", $like, $like, $like);
    mysqli_stmt_execute($stmt);
    $result    = mysqli_stmt_get_result($stmt);
    $criminals = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $criminals[] = $row;
    }
    return $criminals;
}

function getAllCases($conn, $statusFilter = '')
{
    if ($statusFilter !== '') {
        $stmt = mysqli_prepare($conn,
            "SELECT c.*, u.name AS assigned_name
             FROM cases c
             LEFT JOIN users u ON c.assigned_to = u.id
             WHERE c.status = ?
             ORDER BY c.created_at DESC");
        mysqli_stmt_bind_param($stmt, "s", $statusFilter);
    } else {
        $stmt = mysqli_prepare($conn,
            "SELECT c.*, u.name AS assigned_name
             FROM cases c
             LEFT JOIN users u ON c.assigned_to = u.id
             ORDER BY c.created_at DESC");
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $cases  = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $cases[] = $row;
    }
    return $cases;
}

function getCaseById($conn, $caseId)
{
    $stmt = mysqli_prepare($conn,
        "SELECT c.*, u.name AS assigned_name, u2.name AS created_by_name
         FROM cases c
         LEFT JOIN users u  ON c.assigned_to = u.id
         LEFT JOIN users u2 ON c.created_by  = u2.id
         WHERE c.id = ?");
    mysqli_stmt_bind_param($stmt, "i", $caseId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

function createCase($conn, $caseNumber, $title, $description, $crimeType, $location,
                    $incidentDate, $status, $assignedTo, $createdBy)
{
    $stmt = mysqli_prepare($conn,
        "INSERT INTO cases
            (case_number, title, description, crime_type, location,
             incident_date, status, assigned_to, created_by)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sssssssii",
        $caseNumber, $title, $description, $crimeType, $location,
        $incidentDate, $status, $assignedTo, $createdBy);
    mysqli_stmt_execute($stmt);
    return mysqli_insert_id($conn);
}

function updateCase($conn, $caseId, $title, $description, $crimeType, $location,
                    $incidentDate, $status, $assignedTo)
{
    $stmt = mysqli_prepare($conn,
        "UPDATE cases
         SET title = ?, description = ?, crime_type = ?, location = ?,
             incident_date = ?, status = ?, assigned_to = ?
         WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "ssssssii",
        $title, $description, $crimeType, $location,
        $incidentDate, $status, $assignedTo, $caseId);
    return mysqli_stmt_execute($stmt);
}

function deleteCase($conn, $caseId)
{
    $stmt = mysqli_prepare($conn, "DELETE FROM cases WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $caseId);
    return mysqli_stmt_execute($stmt);
}

function getCaseCriminals($conn, $caseId)
{
    $stmt = mysqli_prepare($conn,
        "SELECT cr.*, cc.role_in_case
         FROM criminals cr
         JOIN case_criminals cc ON cr.id = cc.criminal_id
         WHERE cc.case_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $caseId);
    mysqli_stmt_execute($stmt);
    $result    = mysqli_stmt_get_result($stmt);
    $criminals = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $criminals[] = $row;
    }
    return $criminals;
}

function linkCriminalToCase($conn, $caseId, $criminalId, $roleInCase)
{
    $stmt = mysqli_prepare($conn,
        "INSERT IGNORE INTO case_criminals (case_id, criminal_id, role_in_case)
         VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iis", $caseId, $criminalId, $roleInCase);
    return mysqli_stmt_execute($stmt);
}

function caseNumberExists($conn, $caseNumber)
{
    $stmt = mysqli_prepare($conn, "SELECT id FROM cases WHERE case_number = ?");
    mysqli_stmt_bind_param($stmt, "s", $caseNumber);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    return mysqli_stmt_num_rows($stmt) > 0;
}

function getEvidenceByCaseId($conn, $caseId)
{
    $stmt = mysqli_prepare($conn,
        "SELECT e.*, u.name AS collected_by_name
         FROM evidence e
         LEFT JOIN users u ON e.collected_by = u.id
         WHERE e.case_id = ?
         ORDER BY e.collected_at DESC");
    mysqli_stmt_bind_param($stmt, "i", $caseId);
    mysqli_stmt_execute($stmt);
    $result   = mysqli_stmt_get_result($stmt);
    $evidence = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $evidence[] = $row;
    }
    return $evidence;
}

function addEvidence($conn, $caseId, $title, $description, $filePath, $evidenceType, $collectedBy)
{
    $stmt = mysqli_prepare($conn,
        "INSERT INTO evidence (case_id, title, description, file_path, evidence_type, collected_by)
         VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "issssi",
        $caseId, $title, $description, $filePath, $evidenceType, $collectedBy);
    mysqli_stmt_execute($stmt);
    return mysqli_insert_id($conn);
}

function deleteEvidence($conn, $evidenceId)
{
    $stmt = mysqli_prepare($conn, "DELETE FROM evidence WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $evidenceId);
    return mysqli_stmt_execute($stmt);
}

function getReportsByCaseId($conn, $caseId)
{
    $stmt = mysqli_prepare($conn,
        "SELECT r.*, u.name AS officer_name
         FROM reports r
         JOIN users u ON r.officer_id = u.id
         WHERE r.case_id = ?
         ORDER BY r.created_at DESC");
    mysqli_stmt_bind_param($stmt, "i", $caseId);
    mysqli_stmt_execute($stmt);
    $result  = mysqli_stmt_get_result($stmt);
    $reports = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $reports[] = $row;
    }
    return $reports;
}

function addReport($conn, $caseId, $officerId, $title, $content, $reportType)
{
    $stmt = mysqli_prepare($conn,
        "INSERT INTO reports (case_id, officer_id, title, content, report_type)
         VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iisss",
        $caseId, $officerId, $title, $content, $reportType);
    mysqli_stmt_execute($stmt);
    return mysqli_insert_id($conn);
}

function getArrestsByCriminalId($conn, $criminalId)
{
    $stmt = mysqli_prepare($conn,
        "SELECT a.*, u.name AS officer_name, c.case_number, c.title AS case_title
         FROM arrests a
         JOIN users u ON a.arrested_by = u.id
         JOIN cases c ON a.case_id     = c.id
         WHERE a.criminal_id = ?
         ORDER BY a.arrest_date DESC");
    mysqli_stmt_bind_param($stmt, "i", $criminalId);
    mysqli_stmt_execute($stmt);
    $result  = mysqli_stmt_get_result($stmt);
    $arrests = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $arrests[] = $row;
    }
    return $arrests;
}

function recordArrest($conn, $criminalId, $caseId, $arrestedBy, $arrestDate, $location, $notes)
{
    $stmt = mysqli_prepare($conn,
        "INSERT INTO arrests (criminal_id, case_id, arrested_by, arrest_date, location, notes)
         VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iiisss",
        $criminalId, $caseId, $arrestedBy, $arrestDate, $location, $notes);
    mysqli_stmt_execute($stmt);

    $upd = mysqli_prepare($conn, "UPDATE criminals SET status = 'arrested' WHERE id = ?");
    mysqli_stmt_bind_param($upd, "i", $criminalId);
    mysqli_stmt_execute($upd);

    return mysqli_insert_id($conn);
}

function getDashboardStats($conn)
{
    $stats = array();

    $r = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM criminals");
    $stats['total_criminals'] = mysqli_fetch_assoc($r)['cnt'];

    $r = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM criminals WHERE status = 'wanted'");
    $stats['wanted'] = mysqli_fetch_assoc($r)['cnt'];

    $r = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM criminals WHERE status = 'arrested'");
    $stats['arrested'] = mysqli_fetch_assoc($r)['cnt'];

    $r = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM cases");
    $stats['total_cases'] = mysqli_fetch_assoc($r)['cnt'];

    $r = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM cases WHERE status = 'open'");
    $stats['open_cases'] = mysqli_fetch_assoc($r)['cnt'];

    $r = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM cases WHERE status = 'solved'");
    $stats['solved_cases'] = mysqli_fetch_assoc($r)['cnt'];

    $r = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM criminals WHERE threat_level = 'critical'");
    $stats['critical_threat'] = mysqli_fetch_assoc($r)['cnt'];

    $r = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM evidence");
    $stats['total_evidence'] = mysqli_fetch_assoc($r)['cnt'];

    return $stats;
}

function getRecentCases($conn, $limit = 5)
{
    $stmt = mysqli_prepare($conn,
        "SELECT c.*, u.name AS assigned_name
         FROM cases c
         LEFT JOIN users u ON c.assigned_to = u.id
         ORDER BY c.created_at DESC LIMIT ?");
    mysqli_stmt_bind_param($stmt, "i", $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $cases  = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $cases[] = $row;
    }
    return $cases;
}

function getMostWanted($conn, $limit = 5)
{
    $stmt = mysqli_prepare($conn,
        "SELECT * FROM criminals
         WHERE status = 'wanted'
         ORDER BY FIELD(threat_level, 'critical', 'high', 'medium', 'low') ASC
         LIMIT ?");
    mysqli_stmt_bind_param($stmt, "i", $limit);
    mysqli_stmt_execute($stmt);
    $result    = mysqli_stmt_get_result($stmt);
    $criminals = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $criminals[i] = $row;
    }
    return $criminals;
}

function getCasesPerMonth($conn)
{
    $result = mysqli_query($conn,
        "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS case_count
         FROM cases
         WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
         GROUP BY DATE_FORMAT(created_at, '%Y-%m')
         ORDER BY month ASC");
    $data = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}

function getAllOfficers($conn)
{
    $result = mysqli_query($conn,
        "SELECT id, name, badge_number, role FROM users WHERE is_active = 1 ORDER BY name");
    $users  = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
    return $users;
}
