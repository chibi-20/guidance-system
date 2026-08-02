<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Offense Types</h2>
    <div>
        <a href="<?= site_url('offense-matrix') ?>" class="btn btn-outline-secondary">View Offense Matrix</a>
        <a href="<?= site_url('offense-types/create') ?>" class="btn btn-highlight">+ Add Offense Type</a>
    </div>
</div>

<?php $categoryBadge = ['minor' => 'warning', 'serious' => 'serious', 'severe' => 'danger']; ?>

<div class="card table-card">
    <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead>
            <tr>
                <th>Category</th>
                <th>Name</th>
                <th>Description</th>
                <th>Default Action</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($offenseTypes)) : ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No offense types defined yet.</td>
                </tr>
            <?php else : ?>
                <?php foreach ($offenseTypes as $offenseType) : ?>
                    <tr class="<?= $offenseType['is_active'] ? '' : 'text-muted' ?>">
                        <td>
                            <span class="badge text-bg-<?= $categoryBadge[$offenseType['category']] ?? 'secondary' ?> text-capitalize">
                                <?= esc($offenseType['category']) ?>
                            </span>
                        </td>
                        <td><?= esc($offenseType['name']) ?></td>
                        <td><?= esc($offenseType['description'] ?? '—') ?></td>
                        <td><?= esc($offenseType['default_action'] ?? '—') ?></td>
                        <td>
                            <span class="badge text-bg-<?= $offenseType['is_active'] ? 'success' : 'secondary' ?>">
                                <?= $offenseType['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <?php if ($offenseType['is_active']) : ?>
                                <a href="<?= site_url('offense-types/' . $offenseType['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form action="<?= site_url('offense-types/' . $offenseType['id'] . '/toggle') ?>" method="post" class="d-inline"
                                      onsubmit="return confirm('Deactivate this offense type? It will no longer appear as an option for new cases, but existing case history is preserved.');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Deactivate</button>
                                </form>
                            <?php else : ?>
                                <form action="<?= site_url('offense-types/' . $offenseType['id'] . '/toggle') ?>" method="post" class="d-inline">
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

<p class="footnote">Offense classifications (Minor, Serious, Severe) are based on DepEd Order No. 6, s. 2026.</p>

<?= $this->endSection() ?>
