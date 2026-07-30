<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Promotion Preview</h2>
    <a href="<?= site_url('promotion') ?>" class="btn btn-outline-secondary">Cancel</a>
</div>

<div class="alert alert-warning">
    <strong>This has not happened yet.</strong> Review the breakdown below, then confirm at the bottom of the page to actually run the promotion.
</div>

<div class="card mb-4">
    <div class="card-header">Summary</div>
    <div class="card-body">
        <p class="mb-1"><strong>From:</strong> <?= esc($sourceYear['name']) ?></p>
        <p class="mb-0">
            <strong>To:</strong>
            <?= $targetMode === 'existing' ? esc($newYearName ?? '') : esc($newYearName) . ' (new school year, ' . esc($newYearStart) . ' to ' . esc($newYearEnd) . ')' ?>
        </p>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">Grade Level Movement</div>
    <div class="card-body">
        <div class="row g-3">
            <?php foreach ([7, 8, 9, 10] as $gradeLevel) : ?>
                <div class="col-sm-6 col-lg-3">
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <div class="stat-icon <?= $gradeLevel === 10 ? 'gold' : 'blue' ?>">
                                <i class="bi <?= $gradeLevel === 10 ? 'bi-mortarboard-fill' : 'bi-arrow-up-circle' ?>"></i>
                            </div>
                            <div>
                                <div class="stat-value"><?= (int) ($gradeCounts[$gradeLevel] ?? 0) ?></div>
                                <div class="stat-label">
                                    Grade <?= $gradeLevel ?>
                                    <?= $gradeLevel === 10 ? '→ Graduating' : '→ Grade ' . ($gradeLevel + 1) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="card table-card mb-4">
    <div class="card-header">Per-Section Breakdown</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Current Section</th>
                    <th class="text-end">Students</th>
                    <th>Becomes</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($breakdown)) : ?>
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">No active enrolled students found.</td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($breakdown as $row) : ?>
                        <tr>
                            <td>Grade <?= (int) $row['grade_level'] ?> - <?= esc($row['section_name']) ?></td>
                            <td class="text-end"><?= (int) $row['count'] ?></td>
                            <td>
                                <?php if ($row['target_label'] === 'Graduating') : ?>
                                    <span class="badge badge-gold">Graduating</span>
                                <?php elseif (str_starts_with($row['target_label'], 'Unassigned')) : ?>
                                    <span class="badge text-bg-warning"><?= esc($row['target_label']) ?></span>
                                <?php else : ?>
                                    <span class="badge text-bg-primary"><?= esc($row['target_label']) ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header">Confirm</div>
    <div class="card-body p-4">
        <form method="post" action="<?= site_url('promotion/execute') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="source_school_year_id" value="<?= (int) $sourceYear['id'] ?>">
            <input type="hidden" name="target_mode" value="<?= esc($targetMode) ?>">
            <?php if ($targetMode === 'existing') : ?>
                <input type="hidden" name="target_school_year_id" value="<?= (int) $targetYearId ?>">
            <?php else : ?>
                <input type="hidden" name="new_year_name" value="<?= esc($newYearName) ?>">
                <input type="hidden" name="new_year_start" value="<?= esc($newYearStart) ?>">
                <input type="hidden" name="new_year_end" value="<?= esc($newYearEnd) ?>">
            <?php endif; ?>

            <div class="form-check mb-3">
                <input type="checkbox" name="confirm" value="1" id="confirmPromotion" class="form-check-input" required>
                <label class="form-check-label" for="confirmPromotion">
                    Yes, I understand this will advance all active students and cannot be undone automatically.
                </label>
            </div>

            <button type="submit" class="btn btn-primary">Confirm &amp; Execute Promotion</button>
            <a href="<?= site_url('promotion') ?>" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
