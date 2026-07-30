<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Import Results</h2>
    <div>
        <a href="<?= site_url('students/import') ?>" class="btn btn-outline-secondary">Import Another File</a>
        <a href="<?= site_url('students') ?>" class="btn btn-outline-secondary">Back to Students</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card text-bg-primary">
            <div class="card-body">
                <h6 class="text-uppercase small mb-1">Rows Processed</h6>
                <p class="display-6 mb-0"><?= (int) $summary['processed'] ?></p>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card text-bg-success">
            <div class="card-body">
                <h6 class="text-uppercase small mb-1">Inserted</h6>
                <p class="display-6 mb-0"><?= (int) $summary['inserted'] ?></p>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card text-bg-info">
            <div class="card-body">
                <h6 class="text-uppercase small mb-1">Updated</h6>
                <p class="display-6 mb-0"><?= (int) $summary['updated'] ?></p>
            </div>
        </div>
    </div>
</div>

<?php if (! empty($summary['errors'])) : ?>
    <div class="alert alert-danger">
        <strong><?= count($summary['errors']) ?> row(s) failed validation — nothing was imported.</strong>
        Fix the rows below in your CSV and re-upload the whole file.
    </div>
    <div class="card">
        <div class="card-header">Failed Rows</div>
        <ul class="list-group list-group-flush">
            <?php foreach ($summary['errors'] as $error) : ?>
                <li class="list-group-item text-danger"><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php else : ?>
    <div class="alert alert-success mb-0">Import completed successfully — no errors.</div>
<?php endif; ?>

<?= $this->endSection() ?>
