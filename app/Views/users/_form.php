<?php $user = $user ?? []; ?>

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Full Name <span class="required-asterisk">*</span></label>
        <input type="text" name="full_name" class="form-control" maxlength="150" required
               value="<?= esc(old('full_name', $user['full_name'] ?? '')) ?>">
    </div>
    <div class="col-md-6">
        <label class="form-label">Email <span class="required-asterisk">*</span></label>
        <input type="email" name="email" class="form-control" maxlength="150" required
               value="<?= esc(old('email', $user['email'] ?? '')) ?>">
    </div>
    <div class="col-md-6">
        <label class="form-label">Username <span class="required-asterisk">*</span></label>
        <input type="text" name="username" class="form-control" maxlength="100" required
               value="<?= esc(old('username', $user['username'] ?? '')) ?>">
    </div>
    <div class="col-md-6">
        <label class="form-label">Role <span class="required-asterisk">*</span></label>
        <?php $roleVal = old('role', $user['role'] ?? ''); ?>
        <select name="role" class="form-select" required>
            <option value="">-- Select --</option>
            <?php foreach (['admin', 'guidance', 'discipline_officer', 'adviser', 'principal'] as $roleOption) : ?>
                <option value="<?= $roleOption ?>" <?= $roleVal === $roleOption ? 'selected' : '' ?>>
                    <?= ucwords(str_replace('_', ' ', $roleOption)) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>
