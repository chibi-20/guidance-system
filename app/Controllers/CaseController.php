<?php

namespace App\Controllers;

use App\Models\CaseActionModel;
use App\Models\CaseModel;
use App\Models\OffenseTypeModel;
use App\Models\SchoolYearModel;
use App\Models\StudentModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class CaseController extends BaseController
{
    protected $helpers = ['form', 'url'];

    public function create($studentId)
    {
        $studentModel = new StudentModel();
        $student      = $studentModel->getWithCurrentEnrollment((int) $studentId);

        if ($student === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $offenseTypeModel = new OffenseTypeModel();

        return view('cases/create', [
            'student'      => $student,
            'offenseTypes' => $offenseTypeModel->getActive(),
        ]);
    }

    public function store($studentId)
    {
        $studentModel = new StudentModel();
        $student      = $studentModel->getWithCurrentEnrollment((int) $studentId);

        if ($student === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $rules = [
            'offense_type_id'  => 'required|is_natural_no_zero',
            'date_of_incident' => 'required|valid_date',
            'time_of_incident' => 'permit_empty',
            'location'         => 'permit_empty|max_length[255]',
            'incident_report'  => 'required',
            'narrative'        => 'permit_empty',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput();
        }

        $offenseTypeModel = new OffenseTypeModel();
        $offenseType      = $offenseTypeModel->find((int) $this->request->getPost('offense_type_id'));

        if ($offenseType === null) {
            return redirect()->back()->withInput()->with('error', 'Selected offense type does not exist.');
        }

        $schoolYearModel = new SchoolYearModel();
        $currentYear     = $schoolYearModel->where('is_current', 1)->first();

        if ($currentYear === null) {
            return redirect()->back()->withInput()->with('error', 'No current school year is configured. Please contact an administrator.');
        }

        $caseModel = new CaseModel();

        $offenseCountType    = $caseModel->countByStudentAndOffenseType((int) $studentId, (int) $offenseType['id']) + 1;
        $offenseCountOverall = $caseModel->countByStudent((int) $studentId) + 1;

        $data = [
            'case_no'               => $caseModel->generateCaseNo(),
            'student_id'            => $studentId,
            'enrollment_id'         => $student['enrollment_id'] ?? null,
            'offense_type_id'       => $offenseType['id'],
            'category'              => $offenseType['category'],
            'date_of_incident'      => $this->request->getPost('date_of_incident'),
            'time_of_incident'      => $this->request->getPost('time_of_incident') ?: null,
            'location'              => $this->request->getPost('location') ?: null,
            'incident_report'       => $this->request->getPost('incident_report'),
            'narrative'             => $this->request->getPost('narrative') ?: null,
            'referred_by'           => session('user_id'),
            'adviser_id'            => $student['adviser_id'] ?? null,
            'offense_count_type'    => $offenseCountType,
            'offense_count_overall' => $offenseCountOverall,
            'status'                => 'open',
            'school_year_id'        => $currentYear['id'],
            'created_by'            => session('user_id'),
        ];

        $caseId = $caseModel->insert($data);

        $message = sprintf(
            "Case filed. This is the student's %s %s offense (%s offense overall).",
            $this->ordinal($offenseCountType),
            $offenseType['name'],
            $this->ordinal($offenseCountOverall)
        );

        $response = redirect()->to('/cases/' . $caseId)->with('message', $message);

        if ($offenseCountType >= 3) {
            $response = $response->with('warning', 'Repeat offender alert: recommend escalation per offense matrix.');
        }

        return $response;
    }

    public function show($caseId)
    {
        $caseModel = new CaseModel();

        $case = $caseModel
            ->select(
                'cases.*, offense_types.name AS offense_type_name, offense_types.category AS offense_type_category, '
                . 'offense_types.default_action, students.last_name AS student_last_name, '
                . 'students.first_name AS student_first_name, students.middle_name AS student_middle_name, '
                . 'students.lrn AS student_lrn, referrer.full_name AS referred_by_name'
            )
            ->join('offense_types', 'offense_types.id = cases.offense_type_id', 'left')
            ->join('students', 'students.id = cases.student_id', 'left')
            ->join('users AS referrer', 'referrer.id = cases.referred_by', 'left')
            ->where('cases.id', $caseId)
            ->first();

        if ($case === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $caseActionModel = new CaseActionModel();
        $caseAction      = $caseActionModel->findByCaseId((int) $caseId);

        if ($caseAction !== null) {
            $caseAction['action_prior']        = json_decode((string) $caseAction['action_prior'], true) ?? [];
            $caseAction['disciplinary_action']  = json_decode((string) $caseAction['disciplinary_action'], true) ?? [];
        }

        return view('cases/show', ['case' => $case, 'caseAction' => $caseAction]);
    }

    public function resolve($caseId)
    {
        $caseModel = new CaseModel();
        $case      = $caseModel->find($caseId);

        if ($case === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (! in_array($case['status'], ['open', 'ongoing'], true)) {
            return redirect()->to('/cases/' . $caseId)->with('error', 'This case has already been resolved.');
        }

        $rules = [
            'perceived_motivation' => 'permit_empty|max_length[150]',
            'remarks'              => 'permit_empty',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput();
        }

        $actionPrior = $this->request->getPost('action_prior') ?? [];
        $actionPrior = $this->appendOtherText($actionPrior, trim((string) $this->request->getPost('action_prior_other')));

        $disciplinaryAction = $this->request->getPost('disciplinary_action') ?? [];
        $disciplinaryAction = $this->appendOtherText($disciplinaryAction, trim((string) $this->request->getPost('disciplinary_action_other')));

        $parentsNotifiedThru = $this->request->getPost('parents_notified_thru') ?? [];
        $conferenceWith      = $this->request->getPost('conference_with') ?? [];

        $caseActionModel = new CaseActionModel();
        $caseActionModel->upsertForCase((int) $caseId, [
            'action_prior'          => json_encode(array_values($actionPrior)),
            'perceived_motivation'  => $this->request->getPost('perceived_motivation') ?: null,
            'disciplinary_action'   => json_encode(array_values($disciplinaryAction)),
            'parents_notified_thru' => $parentsNotifiedThru !== [] ? implode(', ', $parentsNotifiedThru) : null,
            'conference_with'       => $conferenceWith !== [] ? implode(', ', $conferenceWith) : null,
            'behavior_contract'     => $this->request->getPost('behavior_contract') ? 1 : 0,
            'exclusion_transfer'    => $this->request->getPost('exclusion_transfer') ? 1 : 0,
            'remarks'               => $this->request->getPost('remarks') ?: null,
            'resolved_by'           => session('user_id'),
            'resolved_at'           => date('Y-m-d H:i:s'),
        ]);

        $caseModel->update($caseId, ['status' => 'resolved']);

        return redirect()->to('/cases/' . $caseId)->with('message', 'Case resolved successfully.');
    }

    /**
     * If the "Other" checkbox is among the selections and free text was
     * given, replaces the literal "Other" entry with "Other: <text>" so the
     * detail is preserved in the stored JSON list.
     *
     * @param list<string> $selections
     *
     * @return list<string>
     */
    private function appendOtherText(array $selections, string $otherText): array
    {
        if ($otherText === '' || ! in_array('Other', $selections, true)) {
            return $selections;
        }

        return array_map(
            static fn ($item): string => $item === 'Other' ? 'Other: ' . $otherText : $item,
            $selections
        );
    }

    private function ordinal(int $number): string
    {
        if (in_array($number % 100, [11, 12, 13], true)) {
            return $number . 'th';
        }

        return $number . match ($number % 10) {
            1       => 'st',
            2       => 'nd',
            3       => 'rd',
            default => 'th',
        };
    }
}
