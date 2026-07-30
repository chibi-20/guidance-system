<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Promote Students</h2>
</div>

<?php if ($sourceYear === null) : ?>
    <div class="alert alert-danger">
        No current school year is configured. Please contact an administrator before running a promotion.
    </div>
<?php else : ?>

    <div class="card mb-4">
        <div class="card-header">Current School Year: <?= esc($sourceYear['name']) ?></div>
        <div class="card-body">
            <p class="text-muted">Active students currently enrolled, by grade level:</p>
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

    <?php if (array_sum($gradeCounts) === 0) : ?>
        <div class="alert alert-warning">
            There are no active enrolled students in <?= esc($sourceYear['name']) ?> to promote.
        </div>
    <?php else : ?>

        <div class="card">
            <div class="card-header">Start a Promotion</div>
            <div class="card-body p-4">
                <?php if (session('error')) : ?>
                    <div class="alert alert-danger"><?= esc(session('error')) ?></div>
                <?php endif; ?>

                <form method="post" action="<?= site_url('promotion/preview') ?>" id="promotionForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="source_school_year_id" value="<?= (int) $sourceYear['id'] ?>">

                    <label class="form-label d-block">Target School Year <span class="required-asterisk">*</span></label>

                    <div class="form-check mb-2">
                        <input type="radio" name="target_mode" value="existing" id="mode_existing" class="form-check-input"
                               <?= $otherYears === [] ? 'disabled' : '' ?> <?= $otherYears !== [] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="mode_existing">Use an existing school year</label>
                    </div>
                    <div id="existingYearFields" class="mb-3 ps-4 <?= $otherYears === [] ? 'd-none' : '' ?>">
                        <select name="target_school_year_id" class="form-select">
                            <option value="">-- Select --</option>
                            <?php foreach ($otherYears as $year) : ?>
                                <option value="<?= (int) $year['id'] ?>"><?= esc($year['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-check mb-2">
                        <input type="radio" name="target_mode" value="new" id="mode_new" class="form-check-input"
                               <?= $otherYears === [] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="mode_new">Create a new school year</label>
                    </div>
                    <div id="newYearFields" class="row g-2 mb-3 ps-4 <?= $otherYears !== [] ? 'd-none' : '' ?>">
                        <div class="col-md-4">
                            <label class="form-label small mb-1">Name (e.g. 2027-2028)</label>
                            <input type="text" name="new_year_name" class="form-control" maxlength="20" value="<?= esc(old('new_year_name')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small mb-1">Start Date</label>
                            <input type="date" name="new_year_start" class="form-control" value="<?= esc(old('new_year_start')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small mb-1">End Date</label>
                            <input type="date" name="new_year_end" class="form-control" value="<?= esc(old('new_year_end')) ?>">
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Preview Promotion</button>
                    </div>
                </form>
            </div>
        </div>

    <?php endif; ?>
<?php endif; ?>

<script>
(function () {
    const modeExisting     = document.getElementById('mode_existing');
    const modeNew          = document.getElementById('mode_new');
    const existingFields   = document.getElementById('existingYearFields');
    const newFields        = document.getElementById('newYearFields');

    if (! modeExisting || ! modeNew) {
        return;
    }

    function sync() {
        existingFields.classList.toggle('d-none', ! modeExisting.checked);
        newFields.classList.toggle('d-none', ! modeNew.checked);
    }

    modeExisting.addEventListener('change', sync);
    modeNew.addEventListener('change', sync);
})();
</script>

<?= $this->endSection() ?>
