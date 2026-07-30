<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Sections</h2>
    <a href="<?= site_url('sections/create') ?>" class="btn btn-highlight">+ Add Section</a>
</div>

<div class="card table-card">
    <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead>
            <tr>
                <th>Section Name</th>
                <th class="text-end">Current Students</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($groupedSections)) : ?>
                <tr>
                    <td colspan="3" class="text-center text-muted py-4">No sections defined yet.</td>
                </tr>
            <?php else : ?>
                <?php foreach ($groupedSections as $gradeLevel => $sections) : ?>
                    <tr class="table-light">
                        <th colspan="3" class="text-uppercase small text-muted">Grade <?= (int) $gradeLevel ?></th>
                    </tr>
                    <?php foreach ($sections as $section) : ?>
                        <tr>
                            <td><?= esc($section['name']) ?></td>
                            <td class="text-end">
                                <span class="badge text-bg-<?= $section['student_count'] > 0 ? 'primary' : 'secondary' ?>">
                                    <?= (int) $section['student_count'] ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="<?= site_url('sections/' . $section['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form action="<?= site_url('sections/' . $section['id'] . '/delete') ?>" method="post" class="d-inline"
                                      onsubmit="return confirm('Delete this section? This can only succeed if no student has ever been enrolled in it.');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<?= $this->endSection() ?>
