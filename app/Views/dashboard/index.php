<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<h2 class="mb-4">Dashboard</h2>

<div class="row g-4 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="stat-icon gold"><i class="bi bi-people-fill"></i></div>
                <div>
                    <div class="stat-value"><?= esc((string) $totalStudents) ?></div>
                    <div class="stat-label">Total Students</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <a href="<?= site_url('cases') ?>?status=open" class="text-decoration-none">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-icon blue"><i class="bi bi-folder2-open"></i></div>
                    <div>
                        <div class="stat-value"><?= esc((string) $openCases) ?></div>
                        <div class="stat-label">Open Cases</div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-sm-6 col-lg-3">
        <a href="<?= site_url('cases') ?>?min_count_overall=3" class="text-decoration-none">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="stat-icon danger"><i class="bi bi-exclamation-triangle-fill"></i></div>
                    <div>
                        <div class="stat-value"><?= esc((string) $flaggedRepeatOffenders) ?></div>
                        <div class="stat-label">Flagged Repeat Offenders</div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="stat-icon success"><i class="bi bi-calendar-check"></i></div>
                <div>
                    <div class="stat-value"><?= esc((string) $casesThisMonth) ?></div>
                    <div class="stat-label">Cases This Month</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">Cases by Category — This Month</div>
            <div class="card-body">
                <canvas id="categoryChart" height="160"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">Top 5 Offense Types</div>
            <div class="card-body">
                <canvas id="offenseTypeChart" height="160"></canvas>
            </div>
        </div>
    </div>
</div>

<?php $statusBadge = ['open' => 'primary', 'ongoing' => 'warning', 'resolved' => 'success', 'escalated' => 'danger']; ?>

<div class="card table-card">
    <div class="card-header">Recent Cases</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Case No</th>
                    <th>Date</th>
                    <th>Student</th>
                    <th>Offense</th>
                    <th>Category</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentCases)) : ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No cases filed yet.</td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($recentCases as $recentCase) : ?>
                        <tr>
                            <td><a href="<?= site_url('cases/' . $recentCase['id']) ?>"><?= esc($recentCase['case_no']) ?></a></td>
                            <td><?= esc($recentCase['date_of_incident']) ?></td>
                            <td><?= esc($recentCase['student_last_name'] . ', ' . $recentCase['student_first_name']) ?></td>
                            <td><?= esc($recentCase['offense_type_name']) ?></td>
                            <td>
                                <span class="badge text-bg-<?= $recentCase['offense_type_category'] === 'grave' ? 'danger' : 'warning' ?> text-capitalize">
                                    <?= esc($recentCase['offense_type_category']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge text-bg-<?= $statusBadge[$recentCase['status']] ?? 'secondary' ?> text-capitalize"><?= esc($recentCase['status']) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
(function () {
    const byCategory = <?= json_encode($casesByCategory) ?>;
    const categoryTotals = { grave: 0, minor: 0 };
    byCategory.forEach(function (row) {
        categoryTotals[row.category] = parseInt(row.total, 10);
    });

    new Chart(document.getElementById('categoryChart'), {
        type: 'bar',
        data: {
            labels: ['Grave', 'Minor'],
            datasets: [{
                label: 'Cases this month',
                data: [categoryTotals.grave, categoryTotals.minor],
                backgroundColor: ['#dc3545', '#ffc107'],
            }],
        },
        options: {
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
        },
    });

    const topOffenses = <?= json_encode($topOffenseTypes) ?>;

    new Chart(document.getElementById('offenseTypeChart'), {
        type: 'bar',
        data: {
            labels: topOffenses.map(function (row) { return row.offense_type_name; }),
            datasets: [{
                label: 'Cases',
                data: topOffenses.map(function (row) { return parseInt(row.total, 10); }),
                backgroundColor: '#0d6efd',
            }],
        },
        options: {
            indexAxis: 'y',
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true, ticks: { precision: 0 } } },
        },
    });
})();
</script>

<?= $this->endSection() ?>
