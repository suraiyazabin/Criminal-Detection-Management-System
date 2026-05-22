<?php

$view = $view ?? 'dashboard';

if ($view === 'profile') {
    $action = 'profile';
    include __DIR__ . '/auth.php';
    exit;
}

$pageTitle  = 'Dashboard';
$activePage = 'dashboard';
include __DIR__ . '/header.php';

$stats       = isset($stats)       ? $stats       : array();
$recentCases = isset($recentCases) ? $recentCases : array();
$mostWanted  = isset($mostWanted)  ? $mostWanted  : array();
$chartData   = isset($chartData)   ? $chartData   : array();
?>

<div class="page-header">
    <h1>Dashboard</h1>
    <span style="color:#888;font-size:.9rem">Welcome, <?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?> &nbsp;|&nbsp; Badge: <?php echo htmlspecialchars($_SESSION['badge'] ?? ''); ?></span>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?php echo $stats['total_criminals'] ?? 0; ?></div>
        <div class="stat-label">Total Criminals</div>
    </div>
    <div class="stat-card danger">
        <div class="stat-value"><?php echo $stats['wanted'] ?? 0; ?></div>
        <div class="stat-label">Wanted</div>
    </div>
    <div class="stat-card success">
        <div class="stat-value"><?php echo $stats['arrested'] ?? 0; ?></div>
        <div class="stat-label">Arrested</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo $stats['total_cases'] ?? 0; ?></div>
        <div class="stat-label">Total Cases</div>
    </div>
    <div class="stat-card danger">
        <div class="stat-value"><?php echo $stats['open_cases'] ?? 0; ?></div>
        <div class="stat-label">Open Cases</div>
    </div>
    <div class="stat-card success">
        <div class="stat-value"><?php echo $stats['solved_cases'] ?? 0; ?></div>
        <div class="stat-label">Solved Cases</div>
    </div>
    <div class="stat-card danger">
        <div class="stat-value"><?php echo $stats['critical_threat'] ?? 0; ?></div>
        <div class="stat-label">Critical Threats</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo $stats['total_evidence'] ?? 0; ?></div>
        <div class="stat-label">Evidence Items</div>
    </div>
</div>

<!-- Cases per month chart -->
<?php if (!empty($chartData)): ?>
<div class="card">
    <h2>📊 Cases Filed Per Month (Last 6 Months)</h2>
    <canvas id="volumeChart"></canvas>
    <script>var volumeData = <?php echo json_encode(array_map(function($d){ return array('day'=>$d['month'],'order_count'=>$d['case_count']); }, $chartData)); ?>;</script>
</div>
<?php endif; ?>

<div class="two-col">

<!-- Most Wanted -->
<div class="card">
    <h2>🚨 Most Wanted</h2>
    <?php if (empty($mostWanted)): ?>
        <div class="no-data">No wanted criminals on record.</div>
    <?php else: ?>
    <table>
        <thead><tr><th>Name</th><th>Alias</th><th>Threat</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($mostWanted as $c): ?>
            <tr>
                <td><?php echo htmlspecialchars($c['full_name']); ?></td>
                <td><?php echo htmlspecialchars($c['alias'] ?? '—'); ?></td>
                <td><span class="badge badge-threat-<?php echo $c['threat_level']; ?>"><?php echo ucfirst($c['threat_level']); ?></span></td>
                <td><a href="CriminalController.php?action=view&id=<?php echo $c['id']; ?>" class="btn btn-sm btn-secondary">View</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
    <br><a href="CriminalController.php?status=wanted" class="btn btn-sm btn-danger">All Wanted →</a>
</div>

<!-- Recent Cases -->
<div class="card">
    <h2>📁 Recent Cases</h2>
    <?php if (empty($recentCases)): ?>
        <div class="no-data">No cases yet.</div>
    <?php else: ?>
    <table>
        <thead><tr><th>Case No.</th><th>Title</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($recentCases as $c): ?>
            <tr>
                <td><?php echo htmlspecialchars($c['case_number']); ?></td>
                <td><?php echo htmlspecialchars($c['title']); ?></td>
                <td><span class="badge badge-<?php echo str_replace('_','-',$c['status']); ?>"><?php echo ucwords(str_replace('_',' ',$c['status'])); ?></span></td>
                <td><a href="CaseController.php?action=view&id=<?php echo $c['id']; ?>" class="btn btn-sm btn-secondary">View</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
    <br><a href="CaseController.php" class="btn btn-sm btn-primary">All Cases →</a>
</div>

</div><!-- /.two-col -->

<?php include __DIR__ . '/footer.php'; ?>