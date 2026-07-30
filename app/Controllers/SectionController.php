<?php

namespace App\Controllers;

use App\Models\SectionModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class SectionController extends BaseController
{
    protected $helpers = ['form', 'url'];

    public function index()
    {
        $sectionModel = new SectionModel();
        $grouped      = $sectionModel->getGroupedByGrade();

        foreach ($grouped as $gradeLevel => $sections) {
            foreach ($sections as $key => $section) {
                $grouped[$gradeLevel][$key]['student_count'] = $sectionModel->countStudents((int) $section['id']);
            }
        }

        return view('sections/index', ['groupedSections' => $grouped]);
    }

    public function create()
    {
        return view('sections/create', ['section' => []]);
    }

    public function store()
    {
        if (! $this->validate($this->buildValidationRules())) {
            return redirect()->back()->withInput();
        }

        $sectionModel = new SectionModel();
        $gradeLevel   = (int) $this->request->getPost('grade_level');
        $name         = trim((string) $this->request->getPost('name'));

        if ($this->duplicateExists($sectionModel, $gradeLevel, $name)) {
            return redirect()->back()->withInput()
                ->with('error', 'A Grade ' . $gradeLevel . ' section named "' . $name . '" already exists.');
        }

        $sectionModel->insert(['grade_level' => $gradeLevel, 'name' => $name]);

        return redirect()->to('/sections')->with('message', 'Section added successfully.');
    }

    public function edit($id)
    {
        $sectionModel = new SectionModel();
        $section      = $sectionModel->find($id);

        if ($section === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('sections/edit', [
            'section'      => $section,
            'studentCount' => $sectionModel->countStudents((int) $id),
        ]);
    }

    public function update($id)
    {
        $sectionModel = new SectionModel();
        $section      = $sectionModel->find($id);

        if ($section === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (! $this->validate($this->buildValidationRules())) {
            return redirect()->back()->withInput();
        }

        $gradeLevel = (int) $this->request->getPost('grade_level');
        $name       = trim((string) $this->request->getPost('name'));

        if ($this->duplicateExists($sectionModel, $gradeLevel, $name, (int) $id)) {
            return redirect()->back()->withInput()
                ->with('error', 'A Grade ' . $gradeLevel . ' section named "' . $name . '" already exists.');
        }

        $sectionModel->update($id, ['grade_level' => $gradeLevel, 'name' => $name]);

        return redirect()->to('/sections')->with('message', 'Section updated successfully.');
    }

    /**
     * Only allowed when no student has ever been enrolled in this section —
     * current year or historical — since removing it would corrupt that
     * enrollment history rather than just stop new use of it.
     */
    public function delete($id)
    {
        $sectionModel = new SectionModel();
        $section      = $sectionModel->find($id);

        if ($section === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $historyCount = $sectionModel->countEnrollmentHistory((int) $id);

        if ($historyCount > 0) {
            return redirect()->to('/sections')->with(
                'error',
                'Cannot delete: ' . $historyCount . ' student' . ($historyCount === 1 ? ' has' : 's have')
                    . ' enrollment history in this section. Consider simply not using it for new enrollments instead of deleting it.'
            );
        }

        $sectionModel->delete($id);

        return redirect()->to('/sections')->with('message', 'Section deleted.');
    }

    private function duplicateExists(SectionModel $sectionModel, int $gradeLevel, string $name, ?int $ignoreId = null): bool
    {
        $builder = $sectionModel->where('grade_level', $gradeLevel)->where('name', $name);

        if ($ignoreId !== null) {
            $builder->where('id !=', $ignoreId);
        }

        return $builder->first() !== null;
    }

    private function buildValidationRules(): array
    {
        return [
            'grade_level' => 'required|in_list[7,8,9,10]',
            'name'        => 'required|max_length[100]',
        ];
    }
}
