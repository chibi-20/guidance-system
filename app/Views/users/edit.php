<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<h2 class="mb-4">Edit User</h2>

<?php if (validation_errors()) : ?>
    <div class="alert alert-danger"><?= validation_list_errors() ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body p-4">
        <form method="post" action="<?= site_url('users/' . $user['id']) ?>">
            <?= csrf_field() ?>
            <?= view('users/_form', ['user' => $user]) ?>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Update User</button>
                <a href="<?= site_url('users') ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
