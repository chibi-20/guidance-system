<?php

namespace App\Controllers;

use App\Models\OffenseTypeModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class OffenseTypeController extends BaseController
{
    protected $helpers = ['form', 'url'];

    public function index()
    {
        $offenseTypeModel = new OffenseTypeModel();

        return view('offense_types/index', [
            'offenseTypes' => $offenseTypeModel->getAll(),
        ]);
    }

    public function create()
    {
        return view('offense_types/create', ['offenseType' => []]);
    }

    public function store()
    {
        if (! $this->validate($this->buildValidationRules())) {
            return redirect()->back()->withInput();
        }

        $offenseTypeModel = new OffenseTypeModel();
        $offenseTypeModel->insert($this->collectPostData());

        return redirect()->to('/offense-types')->with('message', 'Offense type added successfully.');
    }

    public function edit($id)
    {
        $offenseTypeModel = new OffenseTypeModel();
        $offenseType      = $offenseTypeModel->find($id);

        if ($offenseType === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('offense_types/edit', ['offenseType' => $offenseType]);
    }

    public function update($id)
    {
        $offenseTypeModel = new OffenseTypeModel();
        $offenseType      = $offenseTypeModel->find($id);

        if ($offenseType === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (! $this->validate($this->buildValidationRules((int) $id))) {
            return redirect()->back()->withInput();
        }

        $offenseTypeModel->update($id, $this->collectPostData());

        return redirect()->to('/offense-types')->with('message', 'Offense type updated successfully.');
    }

    /**
     * Flips is_active without ever deleting the row, since existing cases
     * may reference this offense type via a RESTRICT foreign key.
     */
    public function toggleActive($id)
    {
        $offenseTypeModel = new OffenseTypeModel();
        $offenseType      = $offenseTypeModel->find($id);

        if ($offenseType === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $nowActive = $offenseType['is_active'] ? 0 : 1;
        $offenseTypeModel->update($id, ['is_active' => $nowActive]);

        $message = $nowActive ? 'Offense type reactivated.' : 'Offense type deactivated.';

        return redirect()->to('/offense-types')->with('message', $message);
    }

    /**
     * Lightweight AJAX endpoint for adding an offense type on the fly while
     * filing a case, without leaving that screen. Open to any logged-in
     * staff role (not admin/guidance-restricted like the rest of this
     * controller) since the need can come up mid-conversation with a
     * student regardless of who's at the keyboard.
     */
    public function quickAdd()
    {
        $rules = [
            'category' => 'required|in_list[minor,serious,severe]',
            'name'     => 'required|max_length[150]|is_unique[offense_types.name]',
        ];

        $messages = [
            'name' => [
                'is_unique' => 'An offense type with that name already exists.',
            ],
        ];

        if (! $this->validate($rules, $messages)) {
            return $this->response->setStatusCode(422)->setJSON([
                'error'   => true,
                'message' => implode(' ', $this->validator->getErrors()),
            ]);
        }

        $data = $this->request->getJSON(true) ?? [];

        $offenseTypeModel = new OffenseTypeModel();
        $id               = $offenseTypeModel->insert([
            'category'       => $data['category'],
            'name'           => $data['name'],
            'description'    => null,
            'default_action' => null,
            'is_active'      => 1,
        ]);

        return $this->response->setJSON([
            'id'       => $id,
            'name'     => $data['name'],
            'category' => $data['category'],
        ]);
    }

    private function buildValidationRules(?int $ignoreId = null): array
    {
        $nameRule = 'required|max_length[150]|is_unique[offense_types.name' . ($ignoreId !== null ? ',id,' . $ignoreId : '') . ']';

        return [
            'category'       => 'required|in_list[minor,serious,severe]',
            'name'           => $nameRule,
            'description'    => 'permit_empty',
            'default_action' => 'permit_empty',
        ];
    }

    private function collectPostData(): array
    {
        return [
            'category'       => $this->request->getPost('category'),
            'name'           => $this->request->getPost('name'),
            'description'    => $this->request->getPost('description') ?: null,
            'default_action' => $this->request->getPost('default_action') ?: null,
            'is_active'      => $this->request->getPost('is_active') ? 1 : 0,
        ];
    }
}
