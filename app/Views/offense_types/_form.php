<?php $offenseType = $offenseType ?? []; ?>

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Category <span class="required-asterisk">*</span></label>
        <?php $categoryVal = old('category', $offenseType['category'] ?? ''); ?>
        <select name="category" class="form-select" required>
            <option value="">-- Select --</option>
            <option value="grave" <?= $categoryVal === 'grave' ? 'selected' : '' ?>>Grave</option>
            <option value="minor" <?= $categoryVal === 'minor' ? 'selected' : '' ?>>Minor</option>
        </select>
    </div>
    <div class="col-md-8">
        <label class="form-label">Name <span class="required-asterisk">*</span></label>
        <input type="text" name="name" class="form-control" maxlength="150" required
               value="<?= esc(old('name', $offenseType['name'] ?? '')) ?>">
    </div>

    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="2"><?= esc(old('description', $offenseType['description'] ?? '')) ?></textarea>
    </div>

    <div class="col-12">
        <label class="form-label">Default Action</label>
        <textarea name="default_action" class="form-control" rows="2"
                  placeholder="e.g. Verbal warning; parent notification on 3rd offense"><?= esc(old('default_action', $offenseType['default_action'] ?? '')) ?></textarea>
    </div>

    <div class="col-12">
        <?php $isActiveChecked = old('is_active', array_key_exists('is_active', $offenseType) ? $offenseType['is_active'] : 1); ?>
        <div class="form-check">
            <input type="checkbox" name="is_active" value="1" id="is_active" class="form-check-input" <?= $isActiveChecked ? 'checked' : '' ?>>
            <label class="form-check-label" for="is_active">Active</label>
        </div>
    </div>
</div>
