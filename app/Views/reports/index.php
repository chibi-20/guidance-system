<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php $categoryBadge = ['minor' => 'warning', 'serious' => 'serious', 'severe' => 'danger']; ?>

<h2 class="mb-4">Reports</h2>

<form method="get" action="<?= site_url('reports') ?>" class="row g-2 mb-4">
    <div class="col-md-2">
        <label class="form-label small mb-1">From</label>
        <input type="date" name="start_date" class="form-control form-control-sm" value="<?= esc($startDate) ?>">
    </div>
    <div class="col-md-2">
        <label class="form-label small mb-1">To</label>
        <input type="date" name="end_date" class="form-control form-control-sm" value="<?= esc($endDate) ?>">
    </div>
    <div class="col-md-2">
        <label class="form-label small mb-1">Category</label>
        <select name="category" class="form-select form-select-sm">
            <option value="">All</option>
            <option value="minor" <?= ($filters['category'] ?? '') === 'minor' ? 'selected' : '' ?>>Minor</option>
            <option value="serious" <?= ($filters['category'] ?? '') === 'serious' ? 'selected' : '' ?>>Serious</option>
            <option value="severe" <?= ($filters['category'] ?? '') === 'severe' ? 'selected' : '' ?>>Severe</option>
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label small mb-1">Offense Type</label>
        <select name="offense_type_id" class="form-select form-select-sm">
            <option value="">All</option>
            <?php foreach ($offenseTypes as $offenseType) : ?>
                <option value="<?= (int) $offenseType['id'] ?>" <?= (string) ($filters['offense_type_id'] ?? '') === (string) $offenseType['id'] ? 'selected' : '' ?>>
                    <?= esc($offenseType['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-1">
        <label class="form-label small mb-1">Grade</label>
        <select name="grade_level" class="form-select form-select-sm">
            <option value="">All</option>
            <?php foreach ([7, 8, 9, 10] as $grade) : ?>
                <option value="<?= $grade ?>" <?= (string) ($filters['grade_level'] ?? '') === (string) $grade ? 'selected' : '' ?>>
                    <?= $grade ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label small mb-1">Section</label>
        <select name="section_id" class="form-select form-select-sm">
            <option value="">All</option>
            <?php foreach ($sections as $section) : ?>
                <option value="<?= (int) $section['id'] ?>" <?= (string) ($filters['section_id'] ?? '') === (string) $section['id'] ? 'selected' : '' ?>>
                    Grade <?= (int) $section['grade_level'] ?> - <?= esc($section['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-1">
        <label class="form-label small mb-1">Status</label>
        <select name="status" class="form-select form-select-sm">
            <option value="">All</option>
            <?php foreach (['open', 'ongoing', 'resolved', 'escalated'] as $statusOption) : ?>
                <option value="<?= $statusOption ?>" <?= ($filters['status'] ?? '') === $statusOption ? 'selected' : '' ?>>
                    <?= ucfirst($statusOption) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-outline-secondary btn-sm">Apply Filters</button>
        <a href="<?= site_url('reports') ?>" class="btn btn-outline-secondary btn-sm">Reset to This Month</a>
        <a href="<?= site_url('reports/export') ?>?<?= http_build_query(array_merge(['start_date' => $startDate, 'end_date' => $endDate], array_filter($filters))) ?>" class="btn btn-outline-primary btn-sm">Export CSV</a>
    </div>
</form>

<div class="row g-4 mb-2">
    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="stat-icon blue"><i class="bi bi-folder2-open"></i></div>
                <div>
                    <div class="stat-value"><?= count($cases) ?></div>
                    <div class="stat-label">Total Cases</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="stat-icon success"><i class="bi bi-check-circle-fill"></i></div>
                <div>
                    <div class="stat-value"><?= (int) $resolutionStats['resolved'] ?></div>
                    <div class="stat-label">Resolved</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="stat-icon gold"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <div class="stat-value"><?= (int) $resolutionStats['open'] ?></div>
                    <div class="stat-label">Open</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="stat-icon blue"><i class="bi bi-calendar2-week"></i></div>
                <div>
                    <div class="stat-value"><?= $resolutionStats['avg_resolution_days'] !== null ? esc((string) $resolutionStats['avg_resolution_days']) : '—' ?></div>
                    <div class="stat-label">Avg. Resolution Days</div>
                </div>
            </div>
        </div>
    </div>
</div>
<p class="text-muted small mb-4">Resolved / Open / Avg. Resolution Days reflect the selected date range only (not the category/section/offense-type filters below).</p>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">Cases by Offense Type</div>
            <div class="card-body">
                <canvas id="offenseTypeChart" height="220"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">Cases by Offense Type</div>
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead>
                        <tr><th>Offense Type</th><th>Category</th><th class="text-end">Count</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($byOffenseType)) : ?>
                            <tr><td colspan="3" class="text-center text-muted py-3">No data.</td></tr>
                        <?php else : ?>
                            <?php foreach ($byOffenseType as $row) : ?>
                                <tr>
                                    <td><?= esc($row['offense_type_name'] ?? 'Unknown') ?></td>
                                    <td>
                                        <span class="badge text-bg-<?= $categoryBadge[$row['category']] ?? 'secondary' ?> text-capitalize">
                                            <?= esc($row['category']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end"><?= (int) $row['total'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">Cases by Section</div>
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead>
                <tr><th>Grade &amp; Section</th><th class="text-end">Count</th></tr>
            </thead>
            <tbody>
                <?php if (empty($bySection)) : ?>
                    <tr><td colspan="2" class="text-center text-muted py-3">No data.</td></tr>
                <?php else : ?>
                    <?php foreach ($bySection as $row) : ?>
                        <tr>
                            <td>
                                <?php if (! empty($row['section_id'])) : ?>
                                    Grade <?= (int) $row['grade_level'] ?> - <?= esc($row['section_name']) ?>
                                <?php else : ?>
                                    <span class="text-muted">Not enrolled</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end"><?= (int) $row['total'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">Repeat Offenders (3+ Overall Offenses)</div>
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead>
                <tr><th>Student</th><th>Grade &amp; Section</th><th class="text-end">Total Cases</th><th>Most Recent Case</th></tr>
            </thead>
            <tbody>
                <?php if (empty($repeatOffenders)) : ?>
                    <tr><td colspan="4" class="text-center text-muted py-3">No flagged students.</td></tr>
                <?php else : ?>
                    <?php foreach ($repeatOffenders as $row) : ?>
                        <tr>
                            <td>
                                <a href="<?= site_url('students/' . $row['student_id']) ?>">
                                    <?= esc($row['last_name'] . ', ' . $row['first_name']) ?>
                                </a>
                            </td>
                            <td>
                                <?php if (! empty($row['grade_level'])) : ?>
                                    Grade <?= (int) $row['grade_level'] ?> - <?= esc($row['section_name']) ?>
                                <?php else : ?>
                                    <span class="text-muted">Not enrolled</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end"><span class="badge text-bg-danger"><?= (int) $row['total_cases'] ?></span></td>
                            <td><?= esc($row['most_recent_case_date']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $statusBadge = ['open' => 'primary', 'ongoing' => 'warning', 'resolved' => 'success', 'escalated' => 'danger']; ?>

<div class="card">
    <div class="card-header">Case List (<?= count($cases) ?>)</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Case No</th><th>Date</th><th>Student</th><th>Grade &amp; Section</th>
                    <th>Offense</th><th>Category</th><th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($cases)) : ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No cases match these filters.</td></tr>
                <?php else : ?>
                    <?php foreach ($cases as $case) : ?>
                        <tr>
                            <td><a href="<?= site_url('cases/' . $case['id']) ?>"><?= esc($case['case_no']) ?></a></td>
                            <td><?= esc($case['date_of_incident']) ?></td>
                            <td><?= esc($case['student_last_name'] . ', ' . $case['student_first_name']) ?></td>
                            <td>
                                <?php if (! empty($case['grade_level'])) : ?>
                                    Grade <?= esc((string) $case['grade_level']) ?> - <?= esc($case['section_name']) ?>
                                <?php else : ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td><?= esc($case['offense_type_name']) ?></td>
                            <td>
                                <span class="badge text-bg-<?= $categoryBadge[$case['offense_type_category']] ?? 'secondary' ?> text-capitalize">
                                    <?= esc($case['offense_type_category']) ?>
                                </span>
                            </td>
                            <td><span class="badge text-bg-<?= $statusBadge[$case['status']] ?? 'secondary' ?> text-capitalize"><?= esc($case['status']) ?></span></td>
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
    const byOffenseType = <?= json_encode($byOffenseType) ?>;

    new Chart(document.getElementById('offenseTypeChart'), {
        type: 'bar',
        data: {
            labels: byOffenseType.map(function (row) { return row.offense_type_name; }),
            datasets: [{
                label: 'Cases',
                data: byOffenseType.map(function (row) { return parseInt(row.total, 10); }),
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
