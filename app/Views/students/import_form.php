<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Bulk Import Students</h2>
    <a href="<?= site_url('students') ?>" class="btn btn-outline-secondary">Back to Students</a>
</div>

<?php if (validation_errors()) : ?>
    <div class="alert alert-danger"><?= validation_list_errors() ?></div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title">1. Download the CSV Template</h5>
        <p class="text-muted mb-3">
            Columns must appear in this exact order:
            <code>LRN, LastName, FirstName, MiddleName, Sex, Birthdate, GradeLevel, Section</code>
        </p>
        <a href="<?= site_url('students/import/template') ?>" class="btn btn-outline-secondary">Download Template (.csv)</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h5 class="card-title mb-3">2. Upload Your Completed CSV</h5>
        <ul class="text-muted small">
            <li><strong>LRN</strong> is required on every row — it's used to match existing students (to update them) or create new ones.</li>
            <li><strong>Birthdate</strong>: <code>YYYY-MM-DD</code> or <code>MM/DD/YYYY</code> (optional).</li>
            <li><strong>Sex</strong>: <code>Male</code>, <code>Female</code>, <code>M</code>, or <code>F</code>.</li>
            <li><strong>GradeLevel</strong>: a whole number from 7 to 10.</li>
            <li><strong>Section</strong>: created automatically for that grade level if it doesn't already exist.</li>
            <li>If <strong>any</strong> row fails validation, nothing is imported — fix the listed rows and re-upload.</li>
        </ul>

        <form method="post" action="<?= site_url('students/import') ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">CSV File</label>
                <input type="file" name="csv_file" accept=".csv" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Upload &amp; Import</button>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
