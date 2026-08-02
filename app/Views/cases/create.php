<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">File a Case</h2>
    <a href="<?= site_url('students/' . $student['id']) ?>" class="btn btn-outline-secondary">Back to Student</a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title mb-2">Student</h5>
        <p class="mb-1">
            <strong><?= esc(trim($student['last_name'] . ', ' . $student['first_name'] . ' ' . ($student['middle_name'] ?? ''))) ?></strong>
        </p>
        <p class="text-muted mb-0">
            <?php if (! empty($student['grade_level'])) : ?>
                Grade <?= esc((string) $student['grade_level']) ?> - <?= esc($student['section_name']) ?>
            <?php else : ?>
                Not enrolled for the current school year
            <?php endif; ?>
        </p>
    </div>
</div>

<?php if (validation_errors()) : ?>
    <div class="alert alert-danger"><?= validation_list_errors() ?></div>
<?php endif; ?>

<div class="card">
<div class="card-body p-4">
<form method="post" action="<?= site_url('students/' . $student['id'] . '/cases') ?>" id="caseForm">
    <?= csrf_field() ?>

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Offense Type <span class="required-asterisk">*</span></label>
            <select name="offense_type_id" id="offense_type_id" class="form-select" required>
                <option value="">-- Select --</option>
                <?php foreach (['severe' => 'Severe', 'serious' => 'Serious', 'minor' => 'Minor'] as $categoryValue => $categoryLabel) : ?>
                    <optgroup label="<?= $categoryLabel ?>" id="optgroup-<?= $categoryValue ?>">
                        <?php foreach ($offenseTypes as $offenseType) : ?>
                            <?php if ($offenseType['category'] === $categoryValue) : ?>
                                <option value="<?= (int) $offenseType['id'] ?>" <?= (string) old('offense_type_id') === (string) $offenseType['id'] ? 'selected' : '' ?>>
                                    <?= esc($offenseType['name']) ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select>
            <p class="footnote mt-1">Offense classifications (Minor, Serious, Severe) are based on DepEd Order No. 6, s. 2026.</p>

            <button type="button" id="toggleQuickAdd" class="btn btn-outline-gold btn-sm mt-2">+ Add new offense type</button>

            <div id="quickAddForm" class="border rounded p-3 mt-2 d-none">
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label small mb-1">Category</label>
                        <select id="quickAddCategory" class="form-select form-select-sm">
                            <option value="minor">Minor</option>
                            <option value="serious">Serious</option>
                            <option value="severe">Severe</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small mb-1">New Offense Name</label>
                        <input type="text" id="quickAddName" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" id="quickAddSubmit" class="btn btn-sm btn-primary w-100">Add</button>
                    </div>
                </div>
                <div id="quickAddError" class="text-danger small mt-2 d-none"></div>
            </div>
        </div>

        <div class="col-md-3">
            <label class="form-label">Date of Incident <span class="required-asterisk">*</span></label>
            <input type="date" name="date_of_incident" class="form-control" required
                   value="<?= esc(old('date_of_incident')) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Time of Incident</label>
            <input type="time" name="time_of_incident" class="form-control"
                   value="<?= esc(old('time_of_incident')) ?>">
        </div>

        <div class="col-12">
            <label class="form-label">Location</label>
            <input type="text" name="location" class="form-control" maxlength="255"
                   value="<?= esc(old('location')) ?>">
        </div>

        <div class="col-12">
            <label class="form-label">Incident Report <span class="required-asterisk">*</span></label>
            <textarea name="incident_report" class="form-control" rows="4" required><?= esc(old('incident_report')) ?></textarea>
        </div>

        <div class="col-12">
            <label class="form-label">Narrative</label>
            <textarea name="narrative" class="form-control" rows="4"><?= esc(old('narrative')) ?></textarea>
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary">File Case</button>
        <a href="<?= site_url('students/' . $student['id']) ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>
</div>
</div>

<script>
(function () {
    function getCookie(name) {
        const match = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'));
        return match ? decodeURIComponent(match[1]) : null;
    }

    const toggleBtn      = document.getElementById('toggleQuickAdd');
    const quickAddForm   = document.getElementById('quickAddForm');
    const quickAddError  = document.getElementById('quickAddError');
    const quickAddSubmit = document.getElementById('quickAddSubmit');
    const quickAddName   = document.getElementById('quickAddName');
    const select          = document.getElementById('offense_type_id');

    toggleBtn.addEventListener('click', function () {
        quickAddForm.classList.toggle('d-none');
    });

    quickAddSubmit.addEventListener('click', function () {
        const category = document.getElementById('quickAddCategory').value;
        const name = quickAddName.value.trim();

        quickAddError.classList.add('d-none');
        quickAddError.textContent = '';

        if (name === '') {
            quickAddError.textContent = 'Please enter a name for the new offense type.';
            quickAddError.classList.remove('d-none');
            return;
        }

        quickAddSubmit.disabled = true;

        fetch('<?= site_url('offense-types/quick-add') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCookie('csrf_cookie_name'),
            },
            body: JSON.stringify({ category: category, name: name }),
        })
            .then(function (response) {
                return response.json().then(function (data) {
                    return { ok: response.ok, data: data };
                });
            })
            .then(function (result) {
                quickAddSubmit.disabled = false;

                if (! result.ok || result.data.error) {
                    quickAddError.textContent = result.data.message || 'Could not add the offense type.';
                    quickAddError.classList.remove('d-none');
                    return;
                }

                const option = document.createElement('option');
                option.value = result.data.id;
                option.textContent = result.data.name;

                document.getElementById('optgroup-' + result.data.category).appendChild(option);

                select.value = result.data.id;

                // The CSRF cookie rotates on every verified request (including
                // this one), so the main case form's hidden token — rendered
                // at page load — must be refreshed or its later submit will
                // be rejected as a stale/invalid CSRF token.
                const csrfField = document.querySelector('#caseForm input[name="csrf_test_name"]');
                if (csrfField) {
                    csrfField.value = getCookie('csrf_cookie_name');
                }

                quickAddName.value = '';
                quickAddForm.classList.add('d-none');
            })
            .catch(function () {
                quickAddSubmit.disabled = false;
                quickAddError.textContent = 'Network error — please try again.';
                quickAddError.classList.remove('d-none');
            });
    });
})();
</script>

<?= $this->endSection() ?>
