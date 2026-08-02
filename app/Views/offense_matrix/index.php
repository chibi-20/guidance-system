<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Offense Consequence Matrix</h2>
    <a href="<?= site_url('offense-types') ?>" class="btn btn-outline-secondary">Back to Offense Types</a>
</div>

<p class="text-muted">
    Escalation applies per offense category, not per individual offense type — every offense within a level
    follows the same schedule below, based on how many offenses at that level the student has on record.
</p>

<?php
$categoryBadge  = ['minor' => 'warning', 'serious' => 'serious', 'severe' => 'danger'];
$categoryLabels = ['minor' => 'Minor (Level 1)', 'serious' => 'Serious (Level 2)', 'severe' => 'Severe (Level 3)'];
?>

<div class="row g-4 mb-4">
    <?php foreach (['minor', 'serious', 'severe'] as $category) : ?>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header">
                    <span class="badge text-bg-<?= $categoryBadge[$category] ?>"><?= esc($categoryLabels[$category]) ?></span>
                </div>
                <div class="card-body">
                    <?php if (empty($groupedMatrix[$category])) : ?>
                        <p class="text-muted mb-0">No escalation tiers defined.</p>
                    <?php else : ?>
                        <dl class="mb-0">
                            <?php foreach ($groupedMatrix[$category] as $row) : ?>
                                <dt class="small text-uppercase text-muted mt-2">
                                    <?= esc((string) $row['offense_number']) ?><?= $row['offense_number'] == 1 ? 'st' : ($row['offense_number'] == 2 ? 'nd' : 'rd') ?> Offense
                                </dt>
                                <dd><?= esc($row['recommended_action']) ?></dd>
                            <?php endforeach; ?>
                        </dl>
                        <?php if ($category === 'minor') : ?>
                            <p class="text-muted small mb-0">No 4th+ tier defined — the 3rd offense action applies to any offense beyond it.</p>
                        <?php elseif ($category === 'severe') : ?>
                            <p class="text-muted small mb-0">No 3rd tier defined — the 2nd offense is already the maximum consequence and applies to any offense beyond it.</p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php if (! empty($generalNote)) : ?>
    <div class="alert alert-warning mb-0"><?= esc($generalNote) ?></div>
<?php endif; ?>

<p class="footnote">Offense classifications and corresponding disciplinary actions are based on DepEd Order No. 6, s. 2026.</p>

<?= $this->endSection() ?>
