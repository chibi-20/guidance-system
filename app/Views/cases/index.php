<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<h2 class="mb-4">Cases</h2>

<?php if (! empty($filters['min_count_overall'])) : ?>
    <div class="alert alert-warning">
        Showing cases where the student's overall offense count was
        <?= (int) $filters['min_count_overall'] ?> or more at the time of filing.
        <a href="<?= site_url('cases') ?>" class="alert-link">Clear this filter</a>.
    </div>
<?php endif; ?>

<form method="get" action="<?= site_url('cases') ?>" class="row g-2 mb-4">
    <?php if (! empty($filters['min_count_overall'])) : ?>
        <input type="hidden" name="min_count_overall" value="<?= (int) $filters['min_count_overall'] ?>">
    <?php endif; ?>
    <div class="col-md-2">
        <label class="form-label small mb-1">From</label>
        <input type="date" name="date_from" class="form-control form-control-sm" value="<?= esc($filters['date_from'] ?? '') ?>">
    </div>
    <div class="col-md-2">
        <label class="form-label small mb-1">To</label>
        <input type="date" name="date_to" class="form-control form-control-sm" value="<?= esc($filters['date_to'] ?? '') ?>">
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
        <label class="form-label small mb-1">Category</label>
        <select name="category" class="form-select form-select-sm">
            <option value="">All</option>
            <option value="grave" <?= ($filters['category'] ?? '') === 'grave' ? 'selected' : '' ?>>Grave</option>
            <option value="minor" <?= ($filters['category'] ?? '') === 'minor' ? 'selected' : '' ?>>Minor</option>
        </select>
    </div>
    <div class="col-md-2">
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
    <div class="col-12">
        <button type="submit" class="btn btn-outline-secondary btn-sm">Apply Filters</button>
        <a href="<?= site_url('cases') ?>" class="btn btn-outline-secondary btn-sm">Clear</a>
    </div>
</form>

<?php $statusBadge = ['open' => 'primary', 'ongoing' => 'warning', 'resolved' => 'success', 'escalated' => 'danger']; ?>

<div class="card table-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Case No</th>
                    <th>Date</th>
                    <th>Student</th>
                    <th>Offense</th>
                    <th>Category</th>
                    <th>Grade &amp; Section</th>
                    <th>Offense Count</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($cases)) : ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No cases match these filters.</td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($cases as $case) : ?>
                        <tr>
                            <td><a href="<?= site_url('cases/' . $case['id']) ?>"><?= esc($case['case_no']) ?></a></td>
                            <td><?= esc($case['date_of_incident']) ?></td>
                            <td><?= esc($case['student_last_name'] . ', ' . $case['student_first_name']) ?></td>
                            <td><?= esc($case['offense_type_name']) ?></td>
                            <td>
                                <span class="badge text-bg-<?= $case['offense_type_category'] === 'grave' ? 'danger' : 'warning' ?> text-capitalize">
                                    <?= esc($case['offense_type_category']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if (! empty($case['grade_level'])) : ?>
                                    Grade <?= esc((string) $case['grade_level']) ?> - <?= esc($case['section_name']) ?>
                                <?php else : ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge text-bg-<?= $case['offense_count_type'] >= 3 ? 'danger' : 'secondary' ?>">
                                    <?= (int) $case['offense_count_type'] ?>
                                </span>
                                /
                                <span class="badge text-bg-<?= $case['offense_count_overall'] >= 3 ? 'danger' : 'secondary' ?>">
                                    <?= (int) $case['offense_count_overall'] ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge text-bg-<?= $statusBadge[$case['status']] ?? 'secondary' ?> text-capitalize"><?= esc($case['status']) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    <?= $pager->links('default', 'bootstrap_full') ?>
</div>

<?= $this->endSection() ?>
