<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<h2 class="mb-4">Add User</h2>

<?php if (validation_errors()) : ?>
    <div class="alert alert-danger"><?= validation_list_errors() ?></div>
<?php endif; ?>

<p class="text-muted">A random temporary password will be generated and shown once after saving — share it with the staff member right away.</p>

<div class="card">
    <div class="card-body p-4">
        <form method="post" action="<?= site_url('users') ?>">
            <?= csrf_field() ?>
            <?= view('users/_form', ['user' => $user ?? []]) ?>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Create User</button>
                <a href="<?= site_url('users') ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
