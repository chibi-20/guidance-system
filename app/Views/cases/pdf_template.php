<?php
// This template renders the HTML that mPDF converts to PDF in
// CasePdfController::generate(). Adjust spacing/wording here freely — it's
// kept separate from the controller for exactly that reason.

$studentName = trim($case['student_last_name'] . ', ' . $case['student_first_name'] . ' ' . ($case['student_middle_name'] ?? ''));
$gradeSection = ! empty($case['grade_level'])
    ? 'Grade ' . $case['grade_level'] . ' - ' . $case['section_name']
    : 'Not enrolled for the current school year';

$actionPriorList       = $caseAction['action_prior'] ?? [];
$disciplinaryActionList = $caseAction['disciplinary_action'] ?? [];
$offenseCountType       = (int) $case['offense_count_type'];

$mark = static fn (bool $checked): string => $checked ? '[X]' : '[ ]';
?>
<!DOCTYPE html>
<html>
<head>
<style>
    body { font-family: sans-serif; font-size: 10pt; color: #000; }
    table { border-collapse: collapse; }
    td, th { vertical-align: top; }
    .section-title { font-weight: bold; margin: 10px 0 4px 0; }
    .boxed { border: 1px solid #000; padding: 6px; min-height: 30px; }
    .discipline-row { margin: 0 0 10px 0; line-height: 1.5; }
    .discipline-row:last-child { margin-bottom: 0; }
    .signature-block { margin-top: 30px; }
    .signature-cell { width: 33.33%; text-align: center; padding: 0 10px; }
    .signature-space { border-top: 1px solid #000; margin-top: 45px; height: 1px; }
    .signature-label { padding-top: 4px; font-size: 9.5pt; }
    .citation-note { font-size: 7.5pt; font-style: italic; color: #555; margin: 2px 0 0 0; }
</style>
</head>
<body>

<div style="text-align: center; margin-bottom: 8px;">
    <p style="margin: 0;">Republic of the Philippines</p>
    <p style="margin: 0;">Department of Education</p>
    <p style="margin: 0; font-weight: bold;">Region IV-A CALABARZON</p>
    <!-- Add your Schools Division Office name below if you want it on the form, e.g.:
         <p style="margin: 0;">Schools Division Office of ___</p> -->
    <p style="margin: 4px 0 0 0; font-weight: bold; font-size: 12pt;">JACOBO Z. GONZALES MEMORIAL NATIONAL HIGH SCHOOL</p>
    <p style="margin: 0;">School ID: 307901</p>
</div>

<h2 style="text-align: center; text-decoration: underline; margin: 12px 0;">STUDENT DISCIPLINE ACTION FORM</h2>

<table width="100%">
    <tr>
        <td style="border: 1px solid #000; padding: 5px; width: 35%;">
            <strong>Case No.:</strong> <?= esc($case['case_no']) ?>
        </td>
        <td style="border: 1px solid #000; padding: 5px;">
            <?= $mark($offenseCountType === 1) ?> 1st Offense
            &nbsp;&nbsp;
            <?= $mark($offenseCountType === 2) ?> 2nd Offense
            &nbsp;&nbsp;
            <?= $mark($offenseCountType >= 3) ?> 3rd Offense
        </td>
    </tr>
</table>

<table width="100%" style="margin-top: 6px;">
    <tr>
        <td style="padding: 3px 4px; width: 55%;"><strong>Name of Student:</strong> <?= esc($studentName) ?></td>
        <td style="padding: 3px 4px;"><strong>Grade &amp; Section:</strong> <?= esc($gradeSection) ?></td>
    </tr>
    <tr>
        <td style="padding: 3px 4px;"><strong>Adviser:</strong> <?= esc($case['adviser_name'] ?? '_____________________') ?></td>
        <td style="padding: 3px 4px;"><strong>Referred By:</strong> <?= esc($case['referred_by_name'] ?? '_____________________') ?></td>
    </tr>
    <tr>
        <td style="padding: 3px 4px;"><strong>Date of Incident:</strong> <?= esc(date('F j, Y', strtotime($case['date_of_incident']))) ?></td>
        <td style="padding: 3px 4px;"><strong>Date Filed:</strong> <?= esc(date('F j, Y', strtotime($case['created_at']))) ?></td>
    </tr>
</table>

<p style="text-align: justify; margin: 10px 0;">
    Dear Parent/Guardian,
    <br><br>
    This is to inform you that your child/ward, <strong><?= esc($studentName) ?></strong>,
    Grade &amp; Section <strong><?= esc($gradeSection) ?></strong>, committed the following
    violation(s) of school rules and regulations on
    <strong><?= esc(date('F j, Y', strtotime($case['date_of_incident']))) ?></strong>:
</p>

<table width="100%">
    <tr>
        <td style="padding: 3px 4px;">
            <strong>Offense Level:</strong>
            <?= $mark($case['offense_type_category'] === 'minor') ?> Minor
            &nbsp;&nbsp;
            <?= $mark($case['offense_type_category'] === 'serious') ?> Serious
            &nbsp;&nbsp;
            <?= $mark($case['offense_type_category'] === 'severe') ?> Severe
        </td>
    </tr>
    <tr>
        <td style="padding: 3px 4px;">
            <strong>Offense:</strong> <?= esc($case['offense_type_name']) ?>
        </td>
    </tr>
</table>
<p class="citation-note">Offense classification based on DepEd Order No. 6, s. 2026.</p>

<p class="section-title">Details of the Incident</p>
<p class="boxed"><?= nl2br(esc($case['incident_report'])) ?></p>

<?php if (! empty($case['narrative'])) : ?>
    <p class="section-title">Narrative</p>
    <p class="boxed"><?= nl2br(esc($case['narrative'])) ?></p>
<?php endif; ?>

<p class="section-title">Action Prior to Referral</p>
<table width="100%">
    <tr>
        <?php foreach (['Verbal warning', 'Alternative seating', 'Communicated with parent', 'Consulted counselor'] as $option) : ?>
            <td style="padding: 2px 4px;"><?= $mark(in_array($option, $actionPriorList, true)) ?> <?= esc($option) ?></td>
        <?php endforeach; ?>
    </tr>
</table>

<p class="section-title">Others Involved</p>
<?php if (empty($othersInvolved)) : ?>
    <p style="margin: 0 0 8px 0;">None recorded.</p>
<?php else : ?>
    <table width="100%">
        <tr>
            <th style="border: 1px solid #000; padding: 3px;">Type</th>
            <th style="border: 1px solid #000; padding: 3px;">Name</th>
        </tr>
        <?php foreach ($othersInvolved as $person) : ?>
            <tr>
                <td style="border: 1px solid #000; padding: 3px; text-transform: capitalize;"><?= esc($person['involved_type']) ?></td>
                <td style="border: 1px solid #000; padding: 3px;"><?= esc($person['name']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<p class="section-title">Perceived Motivation</p>
<p><?= esc($caseAction['perceived_motivation'] ?? '_____________________________________________') ?></p>

<p class="section-title">Disciplinary Action</p>
<div class="discipline-block">
    <p class="discipline-row">
        <?php foreach (['Reprimand (oral)', 'Reprimand (written)'] as $option) : ?>
            <?= $mark(in_array($option, $disciplinaryActionList, true)) ?> <?= esc($option) ?>&nbsp;&nbsp;&nbsp;&nbsp;
        <?php endforeach; ?>
    </p>

    <p class="discipline-row">
        <strong>Parents Notified Thru:</strong>
        <?php foreach (['Messenger', 'Text', 'Phone', 'Email'] as $option) : ?>
            <?= $mark(! empty($caseAction['parents_notified_thru']) && str_contains($caseAction['parents_notified_thru'], $option)) ?> <?= esc($option) ?>&nbsp;&nbsp;
        <?php endforeach; ?>
    </p>

    <p class="discipline-row">
        <strong>Conference With:</strong>
        <?php foreach (['Parent', 'Teacher', 'Counselor', 'Principal'] as $option) : ?>
            <?= $mark(! empty($caseAction['conference_with']) && str_contains($caseAction['conference_with'], $option)) ?> <?= esc($option) ?>&nbsp;&nbsp;
        <?php endforeach; ?>
    </p>

    <p class="discipline-row">
        <strong>Behavior Contract:</strong>
        <?= $mark(! empty($caseAction['behavior_contract'])) ?> Yes
        &nbsp;&nbsp;
        <?= $mark(empty($caseAction['behavior_contract'])) ?> No
    </p>

    <p class="discipline-row">
        <strong>Exclusion / Disciplinary Transfer:</strong>
        <?= $mark(! empty($caseAction['exclusion_transfer'])) ?> Yes
        &nbsp;&nbsp;
        <?= $mark(empty($caseAction['exclusion_transfer'])) ?> No
    </p>
</div>

<?php if (! empty($caseAction['remarks'])) : ?>
    <p class="section-title">Remarks</p>
    <p class="boxed"><?= nl2br(esc($caseAction['remarks'])) ?></p>
<?php endif; ?>

<table width="100%" class="signature-block">
    <tr>
        <td class="signature-cell">
            <div class="signature-space">&nbsp;</div>
            <div class="signature-label">Student's Signature over Printed Name</div>
        </td>
        <td class="signature-cell">
            <div class="signature-space">&nbsp;</div>
            <div class="signature-label">Parent/Guardian's Signature over Printed Name</div>
        </td>
        <td class="signature-cell">
            <div class="signature-space">&nbsp;</div>
            <div class="signature-label">School Head/Discipline Designate</div>
        </td>
    </tr>
</table>

</body>
</html>
