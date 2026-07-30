<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<h2 class="mb-4">Add Offense Type</h2>

<?php if (validation_errors()) : ?>
    <div class="alert alert-danger"><?= validation_list_errors() ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body p-4">
        <form method="post" action="<?= site_url('offense-types') ?>">
            <?= csrf_field() ?>
            <?= view('offense_types/_form', ['offenseType' => $offenseType ?? []]) ?>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Save Offense Type</button>
                <a href="<?= site_url('offense-types') ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
