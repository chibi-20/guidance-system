<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">
        <?= esc(trim($student['last_name'] . ', ' . $student['first_name'] . ' ' . ($student['middle_name'] ?? ''))) ?>
    </h2>
    <div>
        <a href="<?= site_url('students/' . $student['id'] . '/edit') ?>" class="btn btn-outline-primary">Edit</a>
        <a href="<?= site_url('students') ?>" class="btn btn-outline-secondary">Back to List</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">Demographic Information</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5">LRN</dt>
                    <dd class="col-sm-7"><?= esc($student['lrn'] ?? '—') ?></dd>

                    <dt class="col-sm-5">Gender</dt>
                    <dd class="col-sm-7"><?= esc($student['gender']) ?></dd>

                    <dt class="col-sm-5">Birthdate</dt>
                    <dd class="col-sm-7"><?= esc($student['birthdate'] ?? '—') ?></dd>

                    <dt class="col-sm-5">Place of Birth</dt>
                    <dd class="col-sm-7"><?= esc($student['place_of_birth'] ?? '—') ?></dd>

                    <dt class="col-sm-5">Address</dt>
                    <dd class="col-sm-7"><?= esc($student['address'] ?? '—') ?></dd>

                    <dt class="col-sm-5">Citizenship</dt>
                    <dd class="col-sm-7"><?= esc($student['citizenship'] ?? '—') ?></dd>

                    <dt class="col-sm-5">Religion</dt>
                    <dd class="col-sm-7"><?= esc($student['religion'] ?? '—') ?></dd>

                    <dt class="col-sm-5">Height / Weight</dt>
                    <dd class="col-sm-7">
                        <?= esc($student['height_cm'] ?? '—') ?> cm / <?= esc($student['weight_kg'] ?? '—') ?> kg
                    </dd>

                    <dt class="col-sm-5">Guardian</dt>
                    <dd class="col-sm-7">
                        <?= esc($student['guardian_name'] ?? '—') ?>
                        <?php if (! empty($student['guardian_contact'])) : ?>
                            (<?= esc($student['guardian_contact']) ?>)
                        <?php endif; ?>
                    </dd>

                    <dt class="col-sm-5">Status</dt>
                    <dd class="col-sm-7">
                        <span class="badge text-bg-<?= $student['status'] === 'active' ? 'success' : 'secondary' ?> text-capitalize">
                            <?= esc($student['status']) ?>
                        </span>
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">Current Enrollment</div>
            <div class="card-body">
                <?php if (! empty($student['grade_level'])) : ?>
                    <p class="mb-1"><strong>Grade &amp; Section:</strong> Grade <?= esc((string) $student['grade_level']) ?> - <?= esc($student['section_name']) ?></p>
                    <p class="mb-0"><strong>Adviser:</strong> <?= esc($student['adviser_name'] ?? 'Not assigned') ?></p>
                <?php else : ?>
                    <p class="text-muted mb-0">Not enrolled for the current school year.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card table-card mt-4">
    <div class="card-header">Enrollment History</div>
    <?php if (empty($enrollmentHistory)) : ?>
        <div class="card-body">
            <p class="text-muted mb-0">No enrollment history on record.</p>
        </div>
    <?php else : ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>School Year</th>
                        <th>Grade &amp; Section</th>
                        <th>Adviser</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($enrollmentHistory as $enrollment) : ?>
                        <tr>
                            <td><?= esc($enrollment['school_year_name'] ?? '—') ?></td>
                            <td>
                                <?php if (! empty($enrollment['grade_level'])) : ?>
                                    Grade <?= esc((string) $enrollment['grade_level']) ?> - <?= esc($enrollment['section_name']) ?>
                                <?php else : ?>
                                    <span class="badge text-bg-warning">Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td><?= esc($enrollment['adviser_name'] ?? 'Not assigned') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        Case History
        <a href="<?= site_url('students/' . $student['id'] . '/cases/create') ?>" class="btn btn-sm btn-highlight">+ File a Case</a>
    </div>
    <?php
    $statusBadge   = ['open' => 'primary', 'ongoing' => 'warning', 'resolved' => 'success', 'escalated' => 'danger'];
    $categoryBadge = ['minor' => 'warning', 'serious' => 'serious', 'severe' => 'danger'];
    ?>
    <?php if (empty($cases)) : ?>
        <div class="card-body">
            <p class="text-muted mb-0">No cases yet.</p>
        </div>
    <?php else : ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Case No</th>
                        <th>Date</th>
                        <th>Offense</th>
                        <th>Category</th>
                        <th>Offense Count</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cases as $case) : ?>
                        <tr>
                            <td><a href="<?= site_url('cases/' . $case['id']) ?>"><?= esc($case['case_no']) ?></a></td>
                            <td><?= esc($case['date_of_incident']) ?></td>
                            <td><?= esc($case['offense_type_name']) ?></td>
                            <td>
                                <span class="badge text-bg-<?= $categoryBadge[$case['offense_type_category']] ?? 'secondary' ?> text-capitalize">
                                    <?= esc($case['offense_type_category']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge text-bg-<?= $case['offense_count_type'] >= 3 ? 'danger' : 'secondary' ?>" title="This offense type">
                                    <?= (int) $case['offense_count_type'] ?>
                                </span>
                                /
                                <span class="badge text-bg-<?= $case['offense_count_overall'] >= 3 ? 'danger' : 'secondary' ?>" title="Overall">
                                    <?= (int) $case['offense_count_overall'] ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge text-bg-<?= $statusBadge[$case['status']] ?? 'secondary' ?> text-capitalize"><?= esc($case['status']) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
