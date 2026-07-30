<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Users</h2>
    <a href="<?= site_url('users/create') ?>" class="btn btn-highlight">+ Add User</a>
</div>

<div class="card table-card">
    <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead>
            <tr>
                <th>Name</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)) : ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No users found.</td>
                </tr>
            <?php else : ?>
                <?php
                $roleBadge = [
                    'admin'              => 'dark',
                    'guidance'           => 'primary',
                    'discipline_officer' => 'info',
                    'adviser'            => 'secondary',
                    'principal'          => 'success',
                ];
                ?>
                <?php foreach ($users as $user) : ?>
                    <tr class="<?= $user['is_active'] ? '' : 'text-muted' ?>">
                        <td><?= esc($user['full_name']) ?></td>
                        <td><?= esc($user['username']) ?></td>
                        <td><?= esc($user['email']) ?></td>
                        <td>
                            <?php if ($user['role'] === 'admin') : ?>
                                <span class="badge badge-gold text-capitalize"><?= esc(str_replace('_', ' ', $user['role'])) ?></span>
                            <?php else : ?>
                                <span class="badge text-bg-<?= $roleBadge[$user['role']] ?? 'secondary' ?> text-capitalize">
                                    <?= esc(str_replace('_', ' ', $user['role'])) ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge text-bg-<?= $user['is_active'] ? 'success' : 'secondary' ?>">
                                <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <?php if ($user['is_active']) : ?>
                                <a href="<?= site_url('users/' . $user['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form action="<?= site_url('users/' . $user['id'] . '/reset-password') ?>" method="post" class="d-inline"
                                      onsubmit="return confirm('Generate a new temporary password for this user? Their current password will stop working immediately.');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-warning">Reset Password</button>
                                </form>
                                <form action="<?= site_url('users/' . $user['id'] . '/toggle') ?>" method="post" class="d-inline"
                                      onsubmit="return confirm('Deactivate this user? They will no longer be able to log in.');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Deactivate</button>
                                </form>
                            <?php else : ?>
                                <form action="<?= site_url('users/' . $user['id'] . '/toggle') ?>" method="post" class="d-inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-success">Reactivate</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<?= $this->endSection() ?>
