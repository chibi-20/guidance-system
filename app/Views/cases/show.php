<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php
$statusBadge   = ['open' => 'primary', 'ongoing' => 'warning', 'resolved' => 'success', 'escalated' => 'danger'];
$categoryBadge = ['minor' => 'warning', 'serious' => 'serious', 'severe' => 'danger'];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Case <?= esc($case['case_no']) ?></h2>
    <div>
        <a href="<?= site_url('cases/' . $case['id'] . '/pdf') ?>" class="btn btn-outline-secondary" target="_blank">Print / Download Form</a>
        <a href="<?= site_url('students/' . $case['student_id']) ?>" class="btn btn-outline-secondary">Back to Student</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">Incident Details</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Student</dt>
                    <dd class="col-sm-8">
                        <a href="<?= site_url('students/' . $case['student_id']) ?>">
                            <?= esc(trim($case['student_last_name'] . ', ' . $case['student_first_name'] . ' ' . ($case['student_middle_name'] ?? ''))) ?>
                        </a>
                        <?php if (! empty($case['student_lrn'])) : ?>
                            <span class="text-muted">(LRN: <?= esc($case['student_lrn']) ?>)</span>
                        <?php endif; ?>
                    </dd>

                    <dt class="col-sm-4">Offense Type</dt>
                    <dd class="col-sm-8">
                        <span class="badge text-bg-<?= $categoryBadge[$case['offense_type_category']] ?? 'secondary' ?> text-capitalize me-1">
                            <?= esc($case['offense_type_category']) ?>
                        </span>
                        <?= esc($case['offense_type_name']) ?>
                    </dd>

                    <dt class="col-sm-4">Date / Time of Incident</dt>
                    <dd class="col-sm-8">
                        <?= esc($case['date_of_incident']) ?>
                        <?php if (! empty($case['time_of_incident'])) : ?>
                            at <?= esc($case['time_of_incident']) ?>
                        <?php endif; ?>
                    </dd>

                    <dt class="col-sm-4">Location</dt>
                    <dd class="col-sm-8"><?= esc($case['location'] ?? '—') ?></dd>

                    <dt class="col-sm-4">Incident Report</dt>
                    <dd class="col-sm-8" style="white-space: pre-wrap;"><?= esc($case['incident_report']) ?></dd>

                    <dt class="col-sm-4">Narrative</dt>
                    <dd class="col-sm-8" style="white-space: pre-wrap;"><?= esc($case['narrative'] ?? '—') ?></dd>

                    <dt class="col-sm-4">Referred By</dt>
                    <dd class="col-sm-8"><?= esc($case['referred_by_name'] ?? '—') ?></dd>

                    <dt class="col-sm-4">Status</dt>
                    <dd class="col-sm-8">
                        <span class="badge text-bg-<?= $statusBadge[$case['status']] ?? 'secondary' ?> text-capitalize"><?= esc($case['status']) ?></span>
                    </dd>
                </dl>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Disciplinary Action</div>
            <div class="card-body">
                <?php if ($caseAction !== null) : ?>
                    <dl class="row mb-0">
                        <?php if (! empty($caseAction['action_prior'])) : ?>
                            <dt class="col-sm-4">Action Prior to Referral</dt>
                            <dd class="col-sm-8"><?= esc(implode('; ', $caseAction['action_prior'])) ?></dd>
                        <?php endif; ?>

                        <?php if (! empty($caseAction['perceived_motivation'])) : ?>
                            <dt class="col-sm-4">Perceived Motivation</dt>
                            <dd class="col-sm-8"><?= esc($caseAction['perceived_motivation']) ?></dd>
                        <?php endif; ?>

                        <?php if (! empty($caseAction['disciplinary_action'])) : ?>
                            <dt class="col-sm-4">Disciplinary Action</dt>
                            <dd class="col-sm-8"><?= esc(implode('; ', $caseAction['disciplinary_action'])) ?></dd>
                        <?php endif; ?>

                        <?php if (! empty($caseAction['parents_notified_thru'])) : ?>
                            <dt class="col-sm-4">Parents Notified Thru</dt>
                            <dd class="col-sm-8"><?= esc($caseAction['parents_notified_thru']) ?></dd>
                        <?php endif; ?>

                        <?php if (! empty($caseAction['conference_with'])) : ?>
                            <dt class="col-sm-4">Conference With</dt>
                            <dd class="col-sm-8"><?= esc($caseAction['conference_with']) ?></dd>
                        <?php endif; ?>

                        <dt class="col-sm-4">Behavior Contract</dt>
                        <dd class="col-sm-8"><?= $caseAction['behavior_contract'] ? 'Yes' : 'No' ?></dd>

                        <dt class="col-sm-4">Exclusion / Disciplinary Transfer</dt>
                        <dd class="col-sm-8"><?= $caseAction['exclusion_transfer'] ? 'Yes' : 'No' ?></dd>

                        <?php if (! empty($caseAction['remarks'])) : ?>
                            <dt class="col-sm-4">Remarks</dt>
                            <dd class="col-sm-8" style="white-space: pre-wrap;"><?= esc($caseAction['remarks']) ?></dd>
                        <?php endif; ?>

                        <dt class="col-sm-4">Resolved By</dt>
                        <dd class="col-sm-8">
                            <?= esc($caseAction['resolved_by'] ? session('full_name') : '—') ?>
                            <?php if (! empty($caseAction['resolved_at'])) : ?>
                                on <?= esc($caseAction['resolved_at']) ?>
                            <?php endif; ?>
                        </dd>
                    </dl>
                <?php else : ?>
                    <p class="text-muted mb-0">Not yet resolved.</p>
                <?php endif; ?>
            </div>
        </div>

        <?php if (in_array($case['status'], ['open', 'ongoing'], true)) : ?>
            <div class="card">
                <div class="card-header">Resolve This Case</div>
                <div class="card-body">
                    <?php if (validation_errors()) : ?>
                        <div class="alert alert-danger"><?= validation_list_errors() ?></div>
                    <?php endif; ?>

                    <form method="post" action="<?= site_url('cases/' . $case['id'] . '/resolve') ?>">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Action Prior to Referral</label>
                            <?php foreach (['Verbal warning', 'Alternative seating', 'Communicated with parent', 'Consulted counselor'] as $option) : ?>
                                <div class="form-check">
                                    <input type="checkbox" name="action_prior[]" value="<?= esc($option) ?>" class="form-check-input" id="ap_<?= md5($option) ?>">
                                    <label class="form-check-label" for="ap_<?= md5($option) ?>"><?= esc($option) ?></label>
                                </div>
                            <?php endforeach; ?>
                            <div class="form-check">
                                <input type="checkbox" name="action_prior[]" value="Other" class="form-check-input" id="ap_other">
                                <label class="form-check-label" for="ap_other">Other</label>
                            </div>
                            <input type="text" name="action_prior_other" class="form-control form-control-sm mt-1" placeholder="Specify other prior action">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Perceived Motivation</label>
                            <input type="text" name="perceived_motivation" class="form-control" maxlength="150">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Disciplinary Action</label>
                            <?php foreach (['Reprimand (oral)', 'Reprimand (written)'] as $option) : ?>
                                <div class="form-check">
                                    <input type="checkbox" name="disciplinary_action[]" value="<?= esc($option) ?>" class="form-check-input" id="da_<?= md5($option) ?>">
                                    <label class="form-check-label" for="da_<?= md5($option) ?>"><?= esc($option) ?></label>
                                </div>
                            <?php endforeach; ?>
                            <div class="form-check">
                                <input type="checkbox" name="disciplinary_action[]" value="Other" class="form-check-input" id="da_other">
                                <label class="form-check-label" for="da_other">Other</label>
                            </div>
                            <input type="text" name="disciplinary_action_other" class="form-control form-control-sm mt-1" placeholder="Specify other disciplinary action">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Parents Notified Thru</label>
                            <?php foreach (['Messenger', 'Text', 'Phone', 'Email'] as $option) : ?>
                                <div class="form-check form-check-inline">
                                    <input type="checkbox" name="parents_notified_thru[]" value="<?= esc($option) ?>" class="form-check-input" id="pn_<?= md5($option) ?>">
                                    <label class="form-check-label" for="pn_<?= md5($option) ?>"><?= esc($option) ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Conference With</label>
                            <?php foreach (['Parent', 'Teacher', 'Counselor', 'Principal'] as $option) : ?>
                                <div class="form-check form-check-inline">
                                    <input type="checkbox" name="conference_with[]" value="<?= esc($option) ?>" class="form-check-input" id="cw_<?= md5($option) ?>">
                                    <label class="form-check-label" for="cw_<?= md5($option) ?>"><?= esc($option) ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold d-block">Behavior Contract</label>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="behavior_contract" value="1" class="form-check-input" id="bc_yes">
                                    <label class="form-check-label" for="bc_yes">Yes</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="behavior_contract" value="0" class="form-check-input" id="bc_no" checked>
                                    <label class="form-check-label" for="bc_no">No</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold d-block">Exclusion / Disciplinary Transfer</label>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="exclusion_transfer" value="1" class="form-check-input" id="et_yes">
                                    <label class="form-check-label" for="et_yes">Yes</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="exclusion_transfer" value="0" class="form-check-input" id="et_no" checked>
                                    <label class="form-check-label" for="et_no">No</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="3"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Resolve Case</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">Offense Counts</div>
            <div class="card-body text-center">
                <div class="mb-3">
                    <div class="text-muted small text-uppercase mb-1">This Offense Type</div>
                    <span class="badge fs-5 text-bg-<?= $case['offense_count_type'] >= 3 ? 'danger' : 'secondary' ?>">
                        <?= (int) $case['offense_count_type'] ?>
                    </span>
                </div>
                <div>
                    <div class="text-muted small text-uppercase mb-1">Overall</div>
                    <span class="badge fs-5 text-bg-<?= $case['offense_count_overall'] >= 3 ? 'danger' : 'secondary' ?>">
                        <?= (int) $case['offense_count_overall'] ?>
                    </span>
                </div>
            </div>
        </div>

        <?php if (! empty($recommendation)) : ?>
            <div class="card">
                <div class="card-header">Recommended Consequence</div>
                <div class="card-body">
                    <p class="mb-2">
                        <span class="badge text-bg-<?= $categoryBadge[$case['offense_type_category']] ?? 'secondary' ?> text-capitalize"><?= esc($case['offense_type_category']) ?></span>
                        This is the student's <strong><?= esc($ordinalCategoryOffense ?? '') ?></strong> offense at this severity level.
                    </p>
                    <p class="mb-0"><?= esc($recommendation['recommended_action']) ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if (! empty($generalNote)) : ?>
    <p class="text-muted small mt-4 mb-0"><?= esc($generalNote) ?></p>
<?php endif; ?>

<?= $this->endSection() ?>
