<?php
$student         = $student ?? [];
$groupedSections = $groupedSections ?? [];
?>

<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label">LRN</label>
        <input type="text" name="lrn" class="form-control" maxlength="20"
               value="<?= esc(old('lrn', $student['lrn'] ?? '')) ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label">Last Name <span class="required-asterisk">*</span></label>
        <input type="text" name="last_name" class="form-control" maxlength="100" required
               value="<?= esc(old('last_name', $student['last_name'] ?? '')) ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label">First Name <span class="required-asterisk">*</span></label>
        <input type="text" name="first_name" class="form-control" maxlength="100" required
               value="<?= esc(old('first_name', $student['first_name'] ?? '')) ?>">
    </div>
    <div class="col-md-2">
        <label class="form-label">Middle Name</label>
        <input type="text" name="middle_name" class="form-control" maxlength="100"
               value="<?= esc(old('middle_name', $student['middle_name'] ?? '')) ?>">
    </div>
    <div class="col-md-1">
        <label class="form-label">Suffix</label>
        <input type="text" name="suffix" class="form-control" maxlength="10"
               value="<?= esc(old('suffix', $student['suffix'] ?? '')) ?>">
    </div>

    <div class="col-md-3">
        <label class="form-label">Gender <span class="required-asterisk">*</span></label>
        <?php $genderVal = old('gender', $student['gender'] ?? ''); ?>
        <select name="gender" class="form-select" required>
            <option value="">-- Select --</option>
            <option value="Male" <?= $genderVal === 'Male' ? 'selected' : '' ?>>Male</option>
            <option value="Female" <?= $genderVal === 'Female' ? 'selected' : '' ?>>Female</option>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Birthdate</label>
        <input type="date" name="birthdate" class="form-control"
               value="<?= esc(old('birthdate', $student['birthdate'] ?? '')) ?>">
    </div>
    <div class="col-md-6">
        <label class="form-label">Place of Birth</label>
        <input type="text" name="place_of_birth" class="form-control" maxlength="150"
               value="<?= esc(old('place_of_birth', $student['place_of_birth'] ?? '')) ?>">
    </div>

    <div class="col-md-4">
        <label class="form-label">Grade Level &amp; Section <span class="required-asterisk">*</span></label>
        <?php $sectionIdVal = old('section_id', (string) ($student['section_id'] ?? '')); ?>
        <select name="section_id" class="form-select" required>
            <option value="">-- Select --</option>
            <?php foreach ($groupedSections as $gradeLevel => $gradeSections) : ?>
                <optgroup label="Grade <?= (int) $gradeLevel ?>">
                    <?php foreach ($gradeSections as $section) : ?>
                        <option value="<?= (int) $section['id'] ?>" <?= $sectionIdVal === (string) $section['id'] ? 'selected' : '' ?>>
                            <?= esc($section['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </optgroup>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-8">
        <label class="form-label">Address</label>
        <input type="text" name="address" class="form-control" maxlength="255"
               value="<?= esc(old('address', $student['address'] ?? '')) ?>">
    </div>
    <div class="col-md-4">
        <label class="form-label">Citizenship</label>
        <input type="text" name="citizenship" class="form-control" maxlength="100"
               value="<?= esc(old('citizenship', $student['citizenship'] ?? '')) ?>">
    </div>

    <div class="col-md-4">
        <label class="form-label">Religion</label>
        <input type="text" name="religion" class="form-control" maxlength="100"
               value="<?= esc(old('religion', $student['religion'] ?? '')) ?>">
    </div>
    <div class="col-md-2">
        <label class="form-label">Height (cm)</label>
        <input type="number" step="0.01" name="height_cm" class="form-control"
               value="<?= esc(old('height_cm', $student['height_cm'] ?? '')) ?>">
    </div>
    <div class="col-md-2">
        <label class="form-label">Weight (kg)</label>
        <input type="number" step="0.01" name="weight_kg" class="form-control"
               value="<?= esc(old('weight_kg', $student['weight_kg'] ?? '')) ?>">
    </div>
    <div class="col-md-4">
        <label class="form-label">Guardian Name</label>
        <input type="text" name="guardian_name" class="form-control" maxlength="150"
               value="<?= esc(old('guardian_name', $student['guardian_name'] ?? '')) ?>">
    </div>

    <div class="col-md-4">
        <label class="form-label">Guardian Contact</label>
        <input type="text" name="guardian_contact" class="form-control" maxlength="50"
               value="<?= esc(old('guardian_contact', $student['guardian_contact'] ?? '')) ?>">
    </div>

    <?php if (isset($student['id'])) : ?>
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <?php $statusVal = old('status', $student['status'] ?? 'active'); ?>
            <select name="status" class="form-select">
                <?php foreach (['active', 'transferred', 'graduated', 'dropped', 'archived'] as $statusOption) : ?>
                    <option value="<?= $statusOption ?>" <?= $statusVal === $statusOption ? 'selected' : '' ?>>
                        <?= ucfirst($statusOption) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    <?php endif; ?>
</div>
