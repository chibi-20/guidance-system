<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<h2 class="mb-4">Add Student</h2>

<?php if (validation_errors()) : ?>
    <div class="alert alert-danger"><?= validation_list_errors() ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body p-4">
        <form method="post" action="<?= site_url('students') ?>">
            <?= csrf_field() ?>
            <?= view('students/_form', ['student' => $student ?? [], 'groupedSections' => $groupedSections ?? []]) ?>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Save Student</button>
                <a href="<?= site_url('students') ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
