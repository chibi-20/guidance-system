<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Promotion Complete</h2>
</div>

<div class="alert alert-success">
    Students have been promoted from <strong><?= esc($sourceYear['name']) ?></strong> to
    <strong><?= esc($targetYear['name'] ?? '') ?></strong>, which is now the current school year.
</div>

<div class="row g-4 mb-4">
    <div class="col-sm-6 col-lg-4">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="stat-icon blue"><i class="bi bi-arrow-up-circle"></i></div>
                <div>
                    <div class="stat-value"><?= (int) $promoted ?></div>
                    <div class="stat-label">Promoted to the next grade</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="stat-icon gold"><i class="bi bi-mortarboard-fill"></i></div>
                <div>
                    <div class="stat-value"><?= (int) $graduated ?></div>
                    <div class="stat-label">Graduated</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="stat-icon danger"><i class="bi bi-exclamation-triangle-fill"></i></div>
                <div>
                    <div class="stat-value"><?= (int) $unassigned ?></div>
                    <div class="stat-label">Left unassigned — needs manual section assignment</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($unassigned > 0) : ?>
    <div class="alert alert-warning d-flex justify-content-between align-items-center">
        <span><?= (int) $unassigned ?> student(s) were promoted without a matching section name and need to be manually assigned.</span>
        <a href="<?= site_url('students') ?>?section=unassigned" class="btn btn-sm btn-outline-secondary">Review Unassigned Students</a>
    </div>
<?php endif; ?>

<a href="<?= site_url('students') ?>" class="btn btn-outline-secondary">Back to Students</a>
<a href="<?= site_url('dashboard') ?>" class="btn btn-outline-secondary">Back to Dashboard</a>

<?= $this->endSection() ?>
