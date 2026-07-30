<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<h2 class="mb-4">Edit Section</h2>

<?php if (validation_errors()) : ?>
    <div class="alert alert-danger"><?= validation_list_errors() ?></div>
<?php endif; ?>

<?php if ($studentCount > 0) : ?>
    <div class="alert alert-warning">
        This section has <?= (int) $studentCount ?> active student<?= $studentCount === 1 ? '' : 's' ?> —
        renaming it will update how it's displayed for all of them.
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body p-4">
        <form method="post" action="<?= site_url('sections/' . $section['id']) ?>">
            <?= csrf_field() ?>
            <?= view('sections/_form', ['section' => $section]) ?>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Update Section</button>
                <a href="<?= site_url('sections') ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
