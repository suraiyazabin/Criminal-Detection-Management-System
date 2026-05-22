<?php
$action       = $action    ?? 'list';
$criminal     = isset($criminal) ? $criminal : array();
$criminals    = isset($criminals) ? $criminals : array();
$cases        = isset($cases)    ? $cases    : array();
$arrests      = isset($arrests)  ? $arrests  : array();
$statusFilter = isset($statusFilter) ? $statusFilter : '';

$pageTitle  = $action === 'create' ? 'Add Criminal' :
             ($action === 'edit'   ? 'Edit Criminal' :
             ($action === 'view'   ? 'Criminal Profile' : 'Criminals'));
$activePage = 'criminals';
include __DIR__ . '/header.php';

$threatColors = array('low'=>'threat-low','medium'=>'threat-medium','high'=>'threat-high','critical'=>'threat-critical');
$statusColors = array('wanted'=>'status-wanted','arrested'=>'status-arrested','released'=>'status-released','deceased'=>'status-deceased');
?>

<!-- ════════ LIST ════════ -->
<?php if ($action === 'list'): ?>

<div class="page-header">
    <h1>Criminal Records</h1>
    <div style="display:flex;gap:8px;align-items:center">
        <input type="text" id="searchInput" placeholder="Search criminals…"
               oninput="ajaxSearch(this.value)" style="padding:7px 12px;border:1px solid #ccc;border-radius:5px;font-size:.875rem">
        <a href="CriminalController.php?action=create" class="btn btn-danger">+ Add Criminal</a>
    </div>
</div>

<!-- Status filter -->
<div class="filter-bar">
    <label>Filter:</label>
    <?php foreach (array(''=> 'All', 'wanted'=>'Wanted', 'arrested'=>'Arrested', 'released'=>'Released', 'deceased'=>'Deceased') as $val => $label): ?>
        <a href="CriminalController.php?status=<?php echo $val; ?>"
           class="btn btn-sm <?php echo $statusFilter === $val ? 'btn-primary' : 'btn-secondary'; ?>">
            <?php echo $label; ?>
        </a>
    <?php endforeach; ?>
</div>

<!-- AJAX result panel -->
<div id="searchPanel"></div>

<div class="card" id="mainTable">
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Photo</th><th>Name</th><th>Alias</th><th>Nationality</th><th>Threat</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php if (empty($criminals)): ?>
                <tr><td colspan="7"><div class="no-data">No criminal records found.</div></td></tr>
            <?php else: ?>
            <?php foreach ($criminals as $c): ?>
                <tr>
                    <td>
                        <?php if (!empty($c['photo_path'])): ?>
                            <img src="../<?php echo htmlspecialchars($c['photo_path']); ?>"
                                 style="width:44px;height:44px;border-radius:50%;object-fit:cover;border:2px solid #ddd" alt="">
                        <?php else: ?>
                            <div style="width:44px;height:44px;border-radius:50%;background:#e0e0e0;display:flex;align-items:center;justify-content:center;font-size:1.2rem">👤</div>
                        <?php endif; ?>
                    </td>
                    <td><strong><?php echo htmlspecialchars($c['full_name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($c['alias'] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($c['nationality'] ?? '—'); ?></td>
                    <td><span class="badge badge-<?php echo $threatColors[$c['threat_level']] ?? ''; ?>"><?php echo ucfirst($c['threat_level']); ?></span></td>
                    <td><span class="badge badge-<?php echo $statusColors[$c['status']] ?? ''; ?>"><?php echo ucfirst($c['status']); ?></span></td>
                    <td>
                        <a href="CriminalController.php?action=view&id=<?php echo $c['id']; ?>" class="btn btn-sm btn-primary">View</a>
                        <a href="CriminalController.php?action=edit&id=<?php echo $c['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                        <form style="display:inline" action="CriminalController.php?action=delete" method="post"
                              onsubmit="return confirm('Delete this criminal record permanently?')">
                            <input type="hidden" name="criminal_id" value="<?php echo $c['id']; ?>">
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
    <h1>Add Criminal Record</h1>
    <a href="CriminalController.php" class="btn btn-secondary">← Back</a>
</div>

<div class="card">
    <form action="CriminalController.php?action=save" method="post"
          enctype="multipart/form-data" onsubmit="return validateCriminal(this)" novalidate>
        <div class="form-row">
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="full_name" id="crimName" placeholder="Enter full legal name">
                <span class="err-msg" id="crimNameErr"></span>
            </div>
            <div class="form-group">
                <label>Alias / Nickname</label>
                <input type="text" name="alias" placeholder="Known as…">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Date of Birth</label>
                <input type="date" name="dob">
            </div>
            <div class="form-group">
                <label>Gender</label>
                <select name="gender">
                    <option value="">— Select —</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="form-group">
                <label>Nationality</label>
                <input type="text" name="nationality" placeholder="e.g. Bangladeshi">
            </div>
        </div>
        <div class="form-group">
            <label>Last Known Address</label>
            <textarea name="address" rows="2" placeholder="Street, City, Country"></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Threat Level *</label>
                <select name="threat_level" id="crimThreat">
                    <option value="low">Low</option>
                    <option value="medium" selected>Medium</option>
                    <option value="high">High</option>
                    <option value="critical">Critical</option>
                </select>
                <span class="err-msg" id="crimThreatErr"></span>
            </div>
            <div class="form-group">
                <label>Status *</label>
                <select name="status" id="crimStatus">
                    <option value="wanted" selected>Wanted</option>
                    <option value="arrested">Arrested</option>
                    <option value="released">Released</option>
                    <option value="deceased">Deceased</option>
                </select>
                <span class="err-msg" id="crimStatusErr"></span>
            </div>
        </div>
        <div class="form-group">
            <label>Description / Known Crimes</label>
            <textarea name="description" rows="4" placeholder="Describe known criminal activities…"></textarea>
        </div>
        <div class="form-group">
            <label>Photo</label>
            <input type="file" name="photo" id="primaryImageInput" accept="image/*">
            <img id="primaryImagePreview" src="#" alt=""
                 style="display:none;margin-top:8px;height:100px;border-radius:8px;border:2px solid #ddd">
        </div>
        <div style="display:flex;gap:10px">
            <button type="submit" class="btn btn-danger">Add Criminal Record</button>
            <a href="CriminalController.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<!-- ════════ EDIT ════════ -->
<?php elseif ($action === 'edit'): ?>

<div class="page-header">
    <h1>Edit: <?php echo htmlspecialchars($criminal['full_name'] ?? ''); ?></h1>
    <a href="CriminalController.php?action=view&id=<?php echo $criminal['id']; ?>" class="btn btn-secondary">← Back</a>
</div>

<div class="card">
    <form action="CriminalController.php?action=update" method="post"
          enctype="multipart/form-data" onsubmit="return validateCriminal(this)" novalidate>
        <input type="hidden" name="criminal_id" value="<?php echo (int)$criminal['id']; ?>">
        <div class="form-row">
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="full_name" id="crimName"
                       value="<?php echo htmlspecialchars($criminal['full_name'] ?? ''); ?>" required>
                <span class="err-msg" id="crimNameErr"></span>
            </div>
            <div class="form-group">
                <label>Alias / Nickname</label>
                <input type="text" name="alias"
                       value="<?php echo htmlspecialchars($criminal['alias'] ?? ''); ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Date of Birth</label>
                <input type="date" name="dob"
                       value="<?php echo htmlspecialchars($criminal['dob'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Gender</label>
                <select name="gender">
                    <option value="">— Select —</option>
                    <?php foreach (array('male','female','other') as $g): ?>
                        <option value="<?php echo $g; ?>" <?php echo ($criminal['gender'] ?? '') === $g ? 'selected' : ''; ?>><?php echo ucfirst($g); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Nationality</label>
                <input type="text" name="nationality"
                       value="<?php echo htmlspecialchars($criminal['nationality'] ?? ''); ?>">
            </div>
        </div>
        <div class="form-group">
            <label>Last Known Address</label>
            <textarea name="address" rows="2"><?php echo htmlspecialchars($criminal['address'] ?? ''); ?></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Threat Level</label>
                <select name="threat_level" id="crimThreat">
                    <?php foreach (array('low','medium','high','critical') as $t): ?>
                        <option value="<?php echo $t; ?>" <?php echo ($criminal['threat_level'] ?? '') === $t ? 'selected' : ''; ?>><?php echo ucfirst($t); ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="err-msg" id="crimThreatErr"></span>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" id="crimStatus">
                    <?php foreach (array('wanted','arrested','released','deceased') as $s): ?>
                        <option value="<?php echo $s; ?>" <?php echo ($criminal['status'] ?? '') === $s ? 'selected' : ''; ?>><?php echo ucfirst($s); ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="err-msg" id="crimStatusErr"></span>
            </div>
        </div>
        <div class="form-group">
            <label>Description / Known Crimes</label>
            <textarea name="description" rows="4"><?php echo htmlspecialchars($criminal['description'] ?? ''); ?></textarea>
        </div>
        <div class="form-group">
            <label>Photo</label>
            <?php if (!empty($criminal['photo_path'])): ?>
                <br><img id="primaryImagePreview"
                     src="../<?php echo htmlspecialchars($criminal['photo_path']); ?>"
                     style="height:90px;border-radius:8px;border:2px solid #ddd;margin-bottom:8px"><br>
            <?php else: ?>
                <img id="primaryImagePreview" src="#" alt=""
                     style="display:none;height:90px;border-radius:8px;border:2px solid #ddd;margin-bottom:8px"><br>
            <?php endif; ?>
            <input type="file" name="photo" id="primaryImageInput" accept="image/*">
            <small style="color:#777">Leave blank to keep current photo.</small>
        </div>
        <div style="display:flex;gap:10px">
            <button type="submit" class="btn btn-primary">Update Record</button>
            <a href="CriminalController.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<!-- ════════ VIEW PROFILE ════════ -->
<?php elseif ($action === 'view'): ?>

<div class="page-header">
    <h1>Criminal Profile</h1>
    <div style="display:flex;gap:8px">
        <a href="CriminalController.php?action=edit&id=<?php echo $criminal['id']; ?>" class="btn btn-warning">Edit</a>
        <a href="CriminalController.php" class="btn btn-secondary">← Back</a>
    </div>
</div>

<div class="profile-grid">
    <!-- Left: photo + identity -->
    <div class="card profile-left">
        <?php if (!empty($criminal['photo_path'])): ?>
            <img src="../<?php echo htmlspecialchars($criminal['photo_path']); ?>"
                 style="width:100%;max-height:280px;object-fit:cover;border-radius:8px;margin-bottom:16px" alt="">
        <?php else: ?>
            <div style="width:100%;height:180px;background:#ecf0f1;border-radius:8px;margin-bottom:16px;display:flex;align-items:center;justify-content:center;font-size:4rem">👤</div>
        <?php endif; ?>

        <h2><?php echo htmlspecialchars($criminal['full_name']); ?></h2>
        <?php if (!empty($criminal['alias'])): ?>
            <p style="color:#888;font-style:italic">aka "<?php echo htmlspecialchars($criminal['alias']); ?>"</p>
        <?php endif; ?>
        <br>
        <span class="badge badge-<?php echo $threatColors[$criminal['threat_level']] ?? ''; ?>" style="font-size:.9rem;padding:5px 14px">
            ⚠ <?php echo ucfirst($criminal['threat_level']); ?> Threat
        </span>
        &nbsp;
        <span class="badge badge-<?php echo $statusColors[$criminal['status']] ?? ''; ?>" style="font-size:.9rem;padding:5px 14px">
            <?php echo ucfirst($criminal['status']); ?>
        </span>

        <table style="margin-top:20px;width:100%;font-size:.875rem">
            <tr><td style="color:#888;padding:4px 0">DOB</td><td><?php echo htmlspecialchars($criminal['dob'] ?? '—'); ?></td></tr>
            <tr><td style="color:#888;padding:4px 0">Gender</td><td><?php echo ucfirst($criminal['gender'] ?? '—'); ?></td></tr>
            <tr><td style="color:#888;padding:4px 0">Nationality</td><td><?php echo htmlspecialchars($criminal['nationality'] ?? '—'); ?></td></tr>
            <tr><td style="color:#888;padding:4px 0">Address</td><td><?php echo htmlspecialchars($criminal['address'] ?? '—'); ?></td></tr>
            <tr><td style="color:#888;padding:4px 0">Added by</td><td><?php echo htmlspecialchars($criminal['created_by_name'] ?? '—'); ?></td></tr>
        </table>
    </div>

    <!-- Right: description, cases, arrests -->
    <div class="profile-right">
        <?php if (!empty($criminal['description'])): ?>
        <div class="card">
            <h2>Known Criminal Activities</h2>
            <p><?php echo nl2br(htmlspecialchars($criminal['description'])); ?></p>
        </div>
        <?php endif; ?>

        <!-- Linked Cases -->
        <div class="card">
            <h2>📁 Linked Cases</h2>
            <?php if (empty($cases)): ?>
                <div class="no-data">No cases linked.</div>
            <?php else: ?>
            <table>
                <thead><tr><th>Case No.</th><th>Title</th><th>Role</th><th>Status</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($cases as $c): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($c['case_number']); ?></td>
                        <td><?php echo htmlspecialchars($c['title']); ?></td>
                        <td><?php echo htmlspecialchars($c['role_in_case'] ?? '—'); ?></td>
                        <td><span class="badge badge-<?php echo str_replace('_','-',$c['status']); ?>"><?php echo ucwords(str_replace('_',' ',$c['status'])); ?></span></td>
                        <td><a href="CaseController.php?action=view&id=<?php echo $c['id']; ?>" class="btn btn-sm btn-secondary">View</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <!-- Arrest History -->
        <div class="card">
            <h2>🚔 Arrest History</h2>
            <?php if (empty($arrests)): ?>
                <div class="no-data">No arrest records.</div>
            <?php else: ?>
            <table>
                <thead><tr><th>Date</th><th>Case</th><th>Officer</th><th>Location</th></tr></thead>
                <tbody>
                <?php foreach ($arrests as $a): ?>
                    <tr>
                        <td><?php echo date('d M Y', strtotime($a['arrest_date'])); ?></td>
                        <td><?php echo htmlspecialchars($a['case_number']); ?></td>
                        <td><?php echo htmlspecialchars($a['officer_name']); ?></td>
                        <td><?php echo htmlspecialchars($a['location'] ?? '—'); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>