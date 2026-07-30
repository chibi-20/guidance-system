<?php

namespace App\Controllers;

use App\Models\CaseModel;
use App\Models\EnrollmentModel;
use App\Models\SchoolYearModel;
use App\Models\SectionModel;
use App\Models\StudentModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Database;

class StudentController extends BaseController
{
    protected $helpers = ['form', 'url'];

    public function index()
    {
        $studentModel = new StudentModel();

        $keyword        = $this->request->getGet('q');
        $sort           = $this->request->getGet('sort') ?? 'last_name';
        $dir            = $this->request->getGet('dir') ?? 'asc';
        $onlyUnassigned = $this->request->getGet('section') === 'unassigned';

        $students = $studentModel->searchPaginated($keyword, 25, $sort, $dir, $onlyUnassigned);

        return view('students/index', [
            'students'       => $students,
            'pager'          => $studentModel->pager,
            'keyword'        => $keyword,
            'sort'           => $sort,
            'dir'            => $dir,
            'onlyUnassigned' => $onlyUnassigned,
        ]);
    }

    public function create()
    {
        return view('students/create', [
            'student'         => [],
            'groupedSections' => (new SectionModel())->getGroupedByGrade(),
        ]);
    }

    public function store()
    {
        $rules = $this->buildValidationRules();

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput();
        }

        $schoolYearModel = new SchoolYearModel();
        $currentYear     = $schoolYearModel->where('is_current', 1)->first();

        if ($currentYear === null) {
            return redirect()->back()->withInput()
                ->with('error', 'No current school year is configured. Please contact an administrator before enrolling students.');
        }

        $db = Database::connect();
        $db->transStart();

        $studentModel = new StudentModel();
        $id           = $studentModel->insert($this->collectPostData());

        $enrollmentModel = new EnrollmentModel();
        $enrollmentModel->insert([
            'student_id'     => $id,
            'section_id'     => (int) $this->request->getPost('section_id'),
            'school_year_id' => $currentYear['id'],
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to save the student record. Please try again.');
        }

        return redirect()->to('/students/' . $id)->with('message', 'Student added successfully.');
    }

    public function show($id)
    {
        $studentModel = new StudentModel();
        $student      = $studentModel->getWithCurrentEnrollment((int) $id);

        if ($student === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $caseModel       = new CaseModel();
        $enrollmentModel = new EnrollmentModel();

        return view('students/show', [
            'student'           => $student,
            'cases'             => $caseModel->getCaseHistory((int) $id),
            'enrollmentHistory' => $enrollmentModel->getHistoryForStudent((int) $id),
        ]);
    }

    public function edit($id)
    {
        $studentModel = new StudentModel();
        $student      = $studentModel->getWithCurrentEnrollment((int) $id);

        if ($student === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('students/edit', [
            'student'         => $student,
            'groupedSections' => (new SectionModel())->getGroupedByGrade(),
        ]);
    }

    public function update($id)
    {
        $studentModel = new StudentModel();
        $student      = $studentModel->find($id);

        if ($student === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $rules = $this->buildValidationRules((int) $id);

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput();
        }

        $schoolYearModel = new SchoolYearModel();
        $currentYear     = $schoolYearModel->where('is_current', 1)->first();

        if ($currentYear === null) {
            return redirect()->back()->withInput()
                ->with('error', 'No current school year is configured. Please contact an administrator before updating enrollment.');
        }

        $db = Database::connect();
        $db->transStart();

        $studentModel->update($id, $this->collectPostData(true));

        $enrollmentModel    = new EnrollmentModel();
        $sectionId          = (int) $this->request->getPost('section_id');
        $existingEnrollment = $enrollmentModel
            ->where('student_id', $id)
            ->where('school_year_id', $currentYear['id'])
            ->first();

        if ($existingEnrollment !== null) {
            if ((int) $existingEnrollment['section_id'] !== $sectionId) {
                $enrollmentModel->update($existingEnrollment['id'], ['section_id' => $sectionId]);
            }
        } else {
            $enrollmentModel->insert([
                'student_id'     => $id,
                'section_id'     => $sectionId,
                'school_year_id' => $currentYear['id'],
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to update the student record. Please try again.');
        }

        return redirect()->to('/students/' . $id)->with('message', 'Student updated successfully.');
    }

    public function delete($id)
    {
        $studentModel = new StudentModel();
        $student      = $studentModel->find($id);

        if ($student === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $studentModel->delete($id);

        return redirect()->to('/students')->with('message', 'Student record deleted.');
    }

    private function buildValidationRules(?int $ignoreId = null): array
    {
        $lrnRule = 'permit_empty|max_length[20]';
        if ($this->request->getPost('lrn')) {
            $lrnRule .= '|is_unique[students.lrn' . ($ignoreId !== null ? ',id,' . $ignoreId : '') . ']';
        }

        return [
            'lrn'              => $lrnRule,
            'section_id'       => 'required|is_natural_no_zero|is_not_unique[sections.id]',
            'last_name'        => 'required|max_length[100]',
            'first_name'       => 'required|max_length[100]',
            'middle_name'      => 'permit_empty|max_length[100]',
            'suffix'           => 'permit_empty|max_length[10]',
            'gender'           => 'required|in_list[Male,Female]',
            'birthdate'        => 'permit_empty|valid_date',
            'place_of_birth'   => 'permit_empty|max_length[150]',
            'address'          => 'permit_empty|max_length[255]',
            'citizenship'      => 'permit_empty|max_length[100]',
            'religion'         => 'permit_empty|max_length[100]',
            'height_cm'        => 'permit_empty|decimal',
            'weight_kg'        => 'permit_empty|decimal',
            'guardian_name'    => 'permit_empty|max_length[150]',
            'guardian_contact' => 'permit_empty|max_length[50]',
        ];
    }

    private function collectPostData(bool $includeStatus = false): array
    {
        $data = [
            'lrn'              => $this->request->getPost('lrn') ?: null,
            'last_name'        => $this->request->getPost('last_name'),
            'first_name'       => $this->request->getPost('first_name'),
            'middle_name'      => $this->request->getPost('middle_name') ?: null,
            'suffix'           => $this->request->getPost('suffix') ?: null,
            'gender'           => $this->request->getPost('gender'),
            'birthdate'        => $this->request->getPost('birthdate') ?: null,
            'place_of_birth'   => $this->request->getPost('place_of_birth') ?: null,
            'address'          => $this->request->getPost('address') ?: null,
            'citizenship'      => $this->request->getPost('citizenship') ?: null,
            'religion'         => $this->request->getPost('religion') ?: null,
            'height_cm'        => $this->request->getPost('height_cm') ?: null,
            'weight_kg'        => $this->request->getPost('weight_kg') ?: null,
            'guardian_name'    => $this->request->getPost('guardian_name') ?: null,
            'guardian_contact' => $this->request->getPost('guardian_contact') ?: null,
        ];

        if ($includeStatus) {
            $status = $this->request->getPost('status');
            if ($status !== null) {
                $data['status'] = $status;
            }
        }

        return $data;
    }
}
