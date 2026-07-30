<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Students</h2>
    <div>
        <?php if (in_array(session('role'), ['admin', 'guidance'], true)) : ?>
            <a href="<?= site_url('students/import') ?>" class="btn btn-outline-primary">Bulk Import</a>
        <?php endif; ?>
        <a href="<?= site_url('students/create') ?>" class="btn btn-highlight">+ Add Student</a>
    </div>
</div>

<?php if (! empty($onlyUnassigned)) : ?>
    <div class="alert alert-warning d-flex justify-content-between align-items-center">
        <span>Showing only students with no section assigned for the current school year (e.g. left unassigned after a promotion with no matching section name).</span>
        <a href="<?= site_url('students') ?>" class="alert-link">Clear filter</a>
    </div>
<?php endif; ?>

<form method="get" action="<?= site_url('students') ?>" class="row g-2 mb-3">
    <?php if ($sort !== 'last_name') : ?>
        <input type="hidden" name="sort" value="<?= esc($sort) ?>">
    <?php endif; ?>
    <?php if ($dir !== 'asc') : ?>
        <input type="hidden" name="dir" value="<?= esc($dir) ?>">
    <?php endif; ?>
    <div class="col-auto flex-grow-1">
        <input type="text" name="q" class="form-control" placeholder="Search by name or LRN..."
               value="<?= esc($keyword ?? '') ?>">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-outline-secondary">Search</button>
    </div>
</form>

<?php
$lrnDir   = ($sort === 'lrn' && $dir === 'asc') ? 'desc' : 'asc';
$lrnQuery = array_filter(['q' => $keyword, 'sort' => 'lrn', 'dir' => $lrnDir]);

$nameDir   = ($sort === 'last_name' && $dir === 'asc') ? 'desc' : 'asc';
$nameQuery = array_filter(['q' => $keyword, 'sort' => 'last_name', 'dir' => $nameDir]);
?>

<div class="card table-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>
                        <a class="text-decoration-none text-dark" href="<?= site_url('students') ?>?<?= http_build_query($lrnQuery) ?>">
                            LRN <?= $sort === 'lrn' ? ($dir === 'asc' ? '&uarr;' : '&darr;') : '' ?>
                        </a>
                    </th>
                    <th>
                        <a class="text-decoration-none text-dark" href="<?= site_url('students') ?>?<?= http_build_query($nameQuery) ?>">
                            Name <?= $sort === 'last_name' ? ($dir === 'asc' ? '&uarr;' : '&darr;') : '' ?>
                        </a>
                    </th>
                    <th>Grade &amp; Section</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($students)) : ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No students found.</td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($students as $student) : ?>
                        <tr>
                            <td><?= esc($student['lrn'] ?? '—') ?></td>
                            <td>
                                <a href="<?= site_url('students/' . $student['id']) ?>">
                                    <?= esc($student['last_name'] . ', ' . $student['first_name']) ?>
                                </a>
                            </td>
                            <td>
                                <?php if (! empty($student['grade_level'])) : ?>
                                    Grade <?= esc((string) $student['grade_level']) ?> - <?= esc($student['section_name']) ?>
                                <?php else : ?>
                                    <span class="text-muted">Not enrolled</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge text-bg-<?= $student['status'] === 'active' ? 'success' : 'secondary' ?> text-capitalize">
                                    <?= esc($student['status']) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="<?= site_url('students/' . $student['id']) ?>" class="btn btn-sm btn-outline-secondary">View</a>
                                <a href="<?= site_url('students/' . $student['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                <?php if (in_array(session('role'), ['admin', 'guidance'], true)) : ?>
                                    <form action="<?= site_url('students/' . $student['id'] . '/delete') ?>" method="post"
                                          class="d-inline"
                                          onsubmit="return confirm('Delete this student record? This can be restored later by an administrator.');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
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

<div class="mt-3">
    <?= $pager->links('default', 'bootstrap_full') ?>
</div>

<?= $this->endSection() ?>
