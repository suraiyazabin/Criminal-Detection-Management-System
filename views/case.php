<?php
$action            = $action            ?? 'list';
$case              = isset($case)             ? $case             : array();
$cases             = isset($cases)            ? $cases            : array();
$officers          = isset($officers)         ? $officers         : array();
$criminals_in_case = isset($criminals_in_case)? $criminals_in_case: array();
$all_criminals     = isset($all_criminals)    ? $all_criminals    : array();
$evidence          = isset($evidence)         ? $evidence         : array();
$reports           = isset($reports)          ? $reports          : array();
$statusFilter      = isset($statusFilter)     ? $statusFilter     : '';

$pageTitle  = $action === 'create' ? 'New Case'  :
             ($action === 'edit'   ? 'Edit Case'  :
             ($action === 'view'   ? 'Case Detail': 'Cases'));
$activePage = 'cases';
include __DIR__ . '/header.php';

$caseStatuses = array('open'=>'open','under_investigation'=>'under-investigation','solved'=>'solved','closed'=>'closed');
?>

<!-- ════════ LIST ════════ -->
<?php if ($action === 'list'): ?>

<div class="page-header">
    <h1>Case Management</h1>
    <a href="CaseController.php?action=create" class="btn btn-primary">+ New Case</a>
</div>

<div class="filter-bar">
    <label>Filter:</label>
    <?php foreach (array(''=> 'All', 'open'=>'Open', 'under_investigation'=>'Under Investigation', 'solved'=>'Solved', 'closed'=>'Closed') as $val => $label): ?>
        <a href="CaseController.php?status=<?php echo $val; ?>"
           class="btn btn-sm <?php echo $statusFilter === $val ? 'btn-primary' : 'btn-secondary'; ?>">
            <?php echo $label; ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="filter-bar">
    <label>Search:</label>
    <input type="text" placeholder="Filter cases…" oninput="tableSearch(this,'caseTable')">
</div>

<div class="card">
    <div class="table-wrap">
        <table id="caseTable">
            <thead>
                <tr><th>Case No.</th><th>Title</th><th>Crime Type</th><th>Location</th><th>Assigned To</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php if (empty($cases)): ?>
                <tr><td colspan="7"><div class="no-data">No cases found.</div></td></tr>
            <?php else: ?>
            <?php foreach ($cases as $c): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($c['case_number']); ?></strong></td>
                    <td><?php echo htmlspecialchars($c['title']); ?></td>
                    <td><?php echo htmlspecialchars($c['crime_type'] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($c['location'] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($c['assigned_name'] ?? 'Unassigned'); ?></td>
                    <td><span class="badge badge-<?php echo $caseStatuses[$c['status']] ?? ''; ?>"><?php echo ucwords(str_replace('_',' ',$c['status'])); ?></span></td>
                    <td>
                        <a href="CaseController.php?action=view&id=<?php echo $c['id']; ?>" class="btn btn-sm btn-primary">View</a>
                        <a href="CaseController.php?action=edit&id=<?php echo $c['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                        <form style="display:inline" action="CaseController.php?action=delete" method="post"
                              onsubmit="return confirm('Delete this case and all its data?')">
                            <input type="hidden" name="case_id" value="<?php echo $c['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ════════ CREATE ════════ -->
<?php elseif ($action === 'create'): ?>

<div class="page-header">
    <h1>Open New Case</h1>
    <a href="CaseController.php" class="btn btn-secondary">← Back</a>
</div>

<div class="card">
    <form action="CaseController.php?action=save" method="post"
          onsubmit="return validateCase(this)" novalidate>
        <div class="form-row">
            <div class="form-group">
                <label>Case Number *</label>
                <input type="text" name="case_number" id="caseNum" placeholder="e.g. CASE-2026-001">
                <span class="err-msg" id="caseNumErr"></span>
            </div>
            <div class="form-group">
                <label>Title *</label>
                <input type="text" name="title" id="caseTitle" placeholder="Brief case title">
                <span class="err-msg" id="caseTitleErr"></span>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Crime Type</label>
                <input type="text" name="crime_type" placeholder="e.g. Robbery, Homicide, Fraud">
            </div>
            <div class="form-group">
                <label>Incident Date</label>
                <input type="date" name="incident_date">
            </div>
        </div>
        <div class="form-group">
            <label>Location</label>
            <input type="text" name="location" placeholder="Crime scene address or area">
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="4" placeholder="Describe the case…"></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="open" selected>Open</option>
                    <option value="under_investigation">Under Investigation</option>
                    <option value="solved">Solved</option>
                    <option value="closed">Closed</option>
                </select>
            </div>
            <div class="form-group">
                <label>Assign To Officer</label>
                <select name="assigned_to">
                    <option value="0">— Unassigned —</option>
                    <?php foreach ($officers as $o): ?>
                        <option value="<?php echo $o['id']; ?>">
                            <?php echo htmlspecialchars($o['name'] . ' (' . $o['badge_number'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div style="display:flex;gap:10px">
            <button type="submit" class="btn btn-primary">Create Case</button>
            <a href="CaseController.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<!-- ════════ EDIT ════════ -->
<?php elseif ($action === 'edit'): ?>

<div class="page-header">
    <h1>Edit Case: <?php echo htmlspecialchars($case['case_number'] ?? ''); ?></h1>
    <a href="CaseController.php?action=view&id=<?php echo $case['id']; ?>" class="btn btn-secondary">← Back</a>
</div>

<div class="card">
    <form action="CaseController.php?action=update" method="post"
          onsubmit="return validateCase(this)" novalidate>
        <input type="hidden" name="case_id" value="<?php echo (int)$case['id']; ?>">
        <div class="form-row">
            <div class="form-group">
                <label>Case Number</label>
                <input type="text" value="<?php echo htmlspecialchars($case['case_number'] ?? ''); ?>" disabled
                       style="background:#f5f5f5">
            </div>
            <div class="form-group">
                <label>Title *</label>
                <input type="text" name="title" id="caseTitle"
                       value="<?php echo htmlspecialchars($case['title'] ?? ''); ?>" required>
                <span class="err-msg" id="caseTitleErr"></span>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Crime Type</label>
                <input type="text" name="crime_type"
                       value="<?php echo htmlspecialchars($case['crime_type'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Incident Date</label>
                <input type="date" name="incident_date"
                       value="<?php echo htmlspecialchars($case['incident_date'] ?? ''); ?>">
            </div>
        </div>
        <div class="form-group">
            <label>Location</label>
            <input type="text" name="location"
                   value="<?php echo htmlspecialchars($case['location'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="4"><?php echo htmlspecialchars($case['description'] ?? ''); ?></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <?php foreach (array('open','under_investigation','solved','closed') as $s): ?>
                        <option value="<?php echo $s; ?>" <?php echo ($case['status'] ?? '') === $s ? 'selected' : ''; ?>><?php echo ucwords(str_replace('_',' ',$s)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Assigned Officer</label>
                <select name="assigned_to">
                    <option value="0">— Unassigned —</option>
                    <?php foreach ($officers as $o): ?>
                        <option value="<?php echo $o['id']; ?>"
                            <?php echo (int)($case['assigned_to'] ?? 0) === (int)$o['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($o['name'] . ' (' . $o['badge_number'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div style="display:flex;gap:10px">
            <button type="submit" class="btn btn-primary">Update Case</button>
            <a href="CaseController.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<!-- ════════ VIEW DETAIL ════════ -->
<?php elseif ($action === 'view'): ?>

<div class="page-header">
    <h1>Case: <?php echo htmlspecialchars($case['case_number'] ?? ''); ?></h1>
    <div style="display:flex;gap:8px">
        <a href="CaseController.php?action=edit&id=<?php echo $case['id']; ?>" class="btn btn-warning">Edit</a>
        <a href="CaseController.php" class="btn btn-secondary">← Back</a>
    </div>
</div>

<!-- Case summary -->
<div class="card">
    <h2><?php echo htmlspecialchars($case['title']); ?></h2>
    <div class="form-row" style="gap:30px">
        <div>
            <p><strong>Crime Type:</strong> <?php echo htmlspecialchars($case['crime_type'] ?? '—'); ?></p>
            <p><strong>Location:</strong>   <?php echo htmlspecialchars($case['location'] ?? '—'); ?></p>
            <p><strong>Incident Date:</strong> <?php echo htmlspecialchars($case['incident_date'] ?? '—'); ?></p>
        </div>
        <div>
            <p><strong>Status:</strong> <span class="badge badge-<?php echo $caseStatuses[$case['status']] ?? ''; ?>"><?php echo ucwords(str_replace('_',' ',$case['status'])); ?></span></p>
            <p><strong>Assigned To:</strong> <?php echo htmlspecialchars($case['assigned_name'] ?? 'Unassigned'); ?></p>
            <p><strong>Created By:</strong>  <?php echo htmlspecialchars($case['created_by_name'] ?? '—'); ?></p>
            <p><strong>Filed:</strong> <?php echo date('d M Y', strtotime($case['created_at'])); ?></p>
        </div>
    </div>
    <?php if (!empty($case['description'])): ?>
        <hr style="margin:14px 0">
        <p><?php echo nl2br(htmlspecialchars($case['description'])); ?></p>
    <?php endif; ?>
</div>

<!-- Suspects / Criminals -->
<div class="card">
    <h2>🚨 Suspects / Criminals</h2>

    <!-- Link criminal form -->
    <form action="CaseController.php?action=link_criminal" method="post"
          style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:18px">
        <input type="hidden" name="case_id" value="<?php echo $case['id']; ?>">
        <select name="criminal_id" style="flex:1;min-width:180px;padding:7px 10px;border:1px solid #ccc;border-radius:5px">
            <option value="0">— Select Criminal —</option>
            <?php foreach ($all_criminals as $cr): ?>
                <option value="<?php echo $cr['id']; ?>"><?php echo htmlspecialchars($cr['full_name']); ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" name="role_in_case" placeholder="Role (e.g. Suspect, Accomplice)"
               style="flex:1;min-width:160px;padding:7px 10px;border:1px solid #ccc;border-radius:5px">
        <button type="submit" class="btn btn-danger btn-sm">Link Criminal</button>
    </form>

    <?php if (empty($criminals_in_case)): ?>
        <div class="no-data">No suspects linked yet.</div>
    <?php else: ?>
    <table>
        <thead><tr><th>Photo</th><th>Name</th><th>Role in Case</th><th>Threat</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($criminals_in_case as $cr): ?>
            <tr>
                <td>
                    <?php if (!empty($cr['photo_path'])): ?>
                        <img src="../<?php echo htmlspecialchars($cr['photo_path']); ?>"
                             style="width:38px;height:38px;border-radius:50%;object-fit:cover" alt="">
                    <?php else: ?>
                        <span style="font-size:1.4rem">👤</span>
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($cr['full_name']); ?></td>
                <td><?php echo htmlspecialchars($cr['role_in_case'] ?? '—'); ?></td>
                <td><span class="badge badge-threat-<?php echo $cr['threat_level']; ?>"><?php echo ucfirst($cr['threat_level']); ?></span></td>
                <td><span class="badge badge-status-<?php echo $cr['status']; ?>"><?php echo ucfirst($cr['status']); ?></span></td>
                <td><a href="CriminalController.php?action=view&id=<?php echo $cr['id']; ?>" class="btn btn-sm btn-secondary">Profile</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<!-- Record Arrest -->
<div class="card">
    <h2>🚔 Record Arrest</h2>
    <form action="CaseController.php?action=record_arrest" method="post"
          style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
        <input type="hidden" name="case_id" value="<?php echo $case['id']; ?>">
        <div class="form-group" style="margin:0;flex:1;min-width:160px">
            <label>Criminal *</label>
            <select name="criminal_id" required>
                <option value="0">— Select —</option>
                <?php foreach ($criminals_in_case as $cr): ?>
                    <option value="<?php echo $cr['id']; ?>"><?php echo htmlspecialchars($cr['full_name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" style="margin:0;flex:1;min-width:160px">
            <label>Arrest Date *</label>
            <input type="datetime-local" name="arrest_date" required>
        </div>
        <div class="form-group" style="margin:0;flex:1;min-width:140px">
            <label>Location</label>
            <input type="text" name="arrest_location" placeholder="Arrest location">
        </div>
        <div class="form-group" style="margin:0;flex:1.5;min-width:160px">
            <label>Notes</label>
            <input type="text" name="arrest_notes" placeholder="Additional notes">
        </div>
        <button type="submit" class="btn btn-danger" style="height:38px">Record Arrest</button>
    </form>
</div>

<!-- Evidence -->
<div class="card">
    <h2>🔬 Evidence</h2>

    <!-- Add evidence form -->
    <form action="CaseController.php?action=add_evidence" method="post"
          enctype="multipart/form-data" style="margin-bottom:18px;padding:14px;background:#f8f9fa;border-radius:8px">
        <input type="hidden" name="case_id" value="<?php echo $case['id']; ?>">
        <div class="form-row">
            <div class="form-group">
                <label>Evidence Title *</label>
                <input type="text" name="ev_title" placeholder="e.g. CCTV footage, Fingerprint">
            </div>
            <div class="form-group">
                <label>Type</label>
                <select name="evidence_type">
                    <?php foreach (array('document','photo','video','physical','digital') as $et): ?>
                        <option value="<?php echo $et; ?>"><?php echo ucfirst($et); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Description</label>
                <input type="text" name="ev_description" placeholder="Brief description">
            </div>
            <div class="form-group">
                <label>File (optional)</label>
                <input type="file" name="ev_file">
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Add Evidence</button>
    </form>

    <?php if (empty($evidence)): ?>
        <div class="no-data">No evidence logged yet.</div>
    <?php else: ?>
    <table>
        <thead><tr><th>Title</th><th>Type</th><th>Description</th><th>Collected By</th><th>Date</th><th>File</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($evidence as $ev): ?>
            <tr>
                <td><?php echo htmlspecialchars($ev['title']); ?></td>
                <td><span class="badge badge-secondary"><?php echo ucfirst($ev['evidence_type']); ?></span></td>
                <td><?php echo htmlspecialchars($ev['description'] ?? '—'); ?></td>
                <td><?php echo htmlspecialchars($ev['collected_by_name'] ?? '—'); ?></td>
                <td><?php echo date('d M Y', strtotime($ev['collected_at'])); ?></td>
                <td>
                    <?php if (!empty($ev['file_path'])): ?>
                        <a href="../<?php echo htmlspecialchars($ev['file_path']); ?>" target="_blank" class="btn btn-sm btn-secondary">View</a>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
                <td>
                    <form style="display:inline" action="CaseController.php?action=delete_evidence" method="post"
                          onsubmit="return confirm('Delete this evidence?')">
                        <input type="hidden" name="evidence_id" value="<?php echo $ev['id']; ?>">
                        <input type="hidden" name="case_id" value="<?php echo $case['id']; ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<!-- Reports -->
<div class="card">
    <h2>📋 Case Reports</h2>

    <!-- Add report form -->
    <form action="CaseController.php?action=add_report" method="post"
          style="margin-bottom:18px;padding:14px;background:#f8f9fa;border-radius:8px">
        <input type="hidden" name="case_id" value="<?php echo $case['id']; ?>">
        <div class="form-row">
            <div class="form-group">
                <label>Report Title *</label>
                <input type="text" name="rp_title" placeholder="e.g. Initial Investigation Report">
            </div>
            <div class="form-group">
                <label>Report Type</label>
                <select name="report_type">
                    <?php foreach (array('initial','progress','final','incident') as $rt): ?>
                        <option value="<?php echo $rt; ?>"><?php echo ucfirst($rt); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label>Content *</label>
            <textarea name="rp_content" rows="4" placeholder="Write the report…"></textarea>
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Submit Report</button>
    </form>

    <?php if (empty($reports)): ?>
        <div class="no-data">No reports filed yet.</div>
    <?php else: ?>
        <?php foreach ($reports as $rp): ?>
        <div style="border:1px solid #eee;border-radius:7px;padding:14px;margin-bottom:12px">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:6px">
                <div>
                    <strong><?php echo htmlspecialchars($rp['title']); ?></strong>
                    <span class="badge badge-secondary" style="margin-left:8px"><?php echo ucfirst($rp['report_type']); ?></span>
                </div>
                <small style="color:#888"><?php echo htmlspecialchars($rp['officer_name']); ?> · <?php echo date('d M Y, h:i A', strtotime($rp['created_at'])); ?></small>
            </div>
            <p style="margin-top:10px;font-size:.9rem;color:#444"><?php echo nl2br(htmlspecialchars($rp['content'])); ?></p>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>