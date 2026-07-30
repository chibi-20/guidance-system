<?php $section = $section ?? []; ?>

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Grade Level <span class="required-asterisk">*</span></label>
        <?php $gradeVal = old('grade_level', (string) ($section['grade_level'] ?? '')); ?>
        <select name="grade_level" class="form-select" required>
            <option value="">-- Select --</option>
            <?php foreach ([7, 8, 9, 10] as $gradeOption) : ?>
                <option value="<?= $gradeOption ?>" <?= $gradeVal === (string) $gradeOption ? 'selected' : '' ?>>
                    Grade <?= $gradeOption ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-8">
        <label class="form-label">Section Name <span class="required-asterisk">*</span></label>
        <input type="text" name="name" class="form-control" maxlength="100" required
               value="<?= esc(old('name', $section['name'] ?? '')) ?>">
    </div>
</div>
