<?php

namespace App\Controllers;

use App\Models\AuditLogModel;
use App\Models\EnrollmentModel;
use App\Models\SchoolYearModel;
use App\Models\SectionModel;
use App\Models\StudentModel;
use Config\Database;

class PromotionController extends BaseController
{
    protected $helpers = ['form', 'url'];

    private const PROMOTABLE_GRADES = [7, 8, 9];
    private const GRADUATING_GRADE  = 10;

    public function showForm()
    {
        $schoolYearModel = new SchoolYearModel();
        $sourceYear      = $schoolYearModel->where('is_current', 1)->first();

        return view('promotion/form', [
            'sourceYear'  => $sourceYear,
            'gradeCounts' => $sourceYear !== null ? $this->activeGradeCounts((int) $sourceYear['id']) : [],
            'otherYears'  => $schoolYearModel->where('is_current', 0)->orderBy('start_date', 'desc')->findAll(),
        ]);
    }

    public function preview()
    {
        $sourceYearId    = (int) $this->request->getPost('source_school_year_id');
        $schoolYearModel = new SchoolYearModel();
        $sourceYear      = $schoolYearModel->find($sourceYearId);

        if ($sourceYear === null || (int) $sourceYear['is_current'] !== 1) {
            return redirect()->to('/promotion')->with('error', 'The source school year is invalid or is no longer the current school year.');
        }

        $gradeCounts = $this->activeGradeCounts($sourceYearId);

        if (array_sum($gradeCounts) === 0) {
            return redirect()->to('/promotion')->with('error', 'The current school year has no active enrolled students to promote.');
        }

        $target = $this->resolveTargetFromRequest($sourceYearId, false);

        if (isset($target['error'])) {
            return redirect()->to('/promotion')->withInput()->with('error', $target['error']);
        }

        return view('promotion/preview', [
            'sourceYear'   => $sourceYear,
            'targetMode'   => $target['mode'],
            'targetYearId' => $target['id'],
            'newYearName'  => $target['name'],
            'newYearStart' => $target['start'],
            'newYearEnd'   => $target['end'],
            'gradeCounts'  => $gradeCounts,
            'breakdown'    => $this->buildSectionBreakdown($sourceYearId),
        ]);
    }

    public function execute()
    {
        if ($this->request->getPost('confirm') !== '1') {
            return redirect()->to('/promotion')->with('error', 'You must check the confirmation box to run a promotion.');
        }

        $sourceYearId    = (int) $this->request->getPost('source_school_year_id');
        $schoolYearModel = new SchoolYearModel();
        $sourceYear      = $schoolYearModel->find($sourceYearId);

        if ($sourceYear === null || (int) $sourceYear['is_current'] !== 1) {
            return redirect()->to('/promotion')->with('error', 'The source school year is invalid or is no longer the current school year.');
        }

        $gradeCounts = $this->activeGradeCounts($sourceYearId);

        if (array_sum($gradeCounts) === 0) {
            return redirect()->to('/promotion')->with('error', 'The current school year has no active enrolled students to promote.');
        }

        $db = Database::connect();
        $db->transStart();

        $target = $this->resolveTargetFromRequest($sourceYearId, true);

        if (isset($target['error'])) {
            $db->transRollback();

            return redirect()->to('/promotion')->withInput()->with('error', $target['error']);
        }

        $targetYearId = $target['id'];

        // Exactly one school year is ever current at a time.
        $schoolYearModel->where('is_current', 1)->set(['is_current' => 0])->update();
        $schoolYearModel->update($targetYearId, ['is_current' => 1]);

        [$promoted, $graduated, $unassigned] = $this->promoteStudents($sourceYearId, $targetYearId);

        $this->logPromotion($sourceYear, $targetYearId, $promoted, $graduated, $unassigned);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to('/promotion')->with('error', 'Promotion failed due to a database error. No changes were saved.');
        }

        return view('promotion/results', [
            'sourceYear' => $sourceYear,
            'targetYear' => $schoolYearModel->find($targetYearId),
            'promoted'   => $promoted,
            'graduated'  => $graduated,
            'unassigned' => $unassigned,
        ]);
    }

    /**
     * Reads target_mode ("existing" or "new") from the request and resolves
     * it to a school_year_id, creating the row when $allowCreate is true and
     * the admin chose to define a brand new year. Preview calls this with
     * $allowCreate = false so it never writes to the database — it just
     * validates the submitted new-year fields and echoes them back through
     * for the confirmation step to resubmit verbatim.
     *
     * @return array{mode: string, id: ?int, name: ?string, start: ?string, end: ?string}|array{error: string}
     */
    private function resolveTargetFromRequest(int $sourceYearId, bool $allowCreate): array
    {
        $schoolYearModel = new SchoolYearModel();
        $targetMode      = $this->request->getPost('target_mode');

        if ($targetMode === 'existing') {
            $targetYearId = (int) $this->request->getPost('target_school_year_id');
            $targetYear   = $schoolYearModel->find($targetYearId);

            if ($targetYear === null || $targetYearId === $sourceYearId) {
                return ['error' => 'Please choose a valid target school year, different from the current one.'];
            }

            return ['mode' => 'existing', 'id' => $targetYearId, 'name' => $targetYear['name'], 'start' => null, 'end' => null];
        }

        $name  = trim((string) $this->request->getPost('new_year_name'));
        $start = $this->request->getPost('new_year_start');
        $end   = $this->request->getPost('new_year_end');

        $rules = [
            'new_year_name'  => 'required|max_length[20]|is_unique[school_years.name]',
            'new_year_start' => 'required|valid_date',
            'new_year_end'   => 'required|valid_date',
        ];

        if (! $this->validate($rules)) {
            return ['error' => 'Please correct the new school year details: ' . implode(' ', $this->validator->getErrors())];
        }

        $targetYearId = null;
        if ($allowCreate) {
            $targetYearId = $schoolYearModel->insert([
                'name'       => $name,
                'start_date' => $start,
                'end_date'   => $end,
                'is_current' => 0,
            ]);
        }

        return ['mode' => 'new', 'id' => $targetYearId, 'name' => $name, 'start' => $start, 'end' => $end];
    }

    /**
     * For every active student with a Grade 7/8/9 enrollment in the source
     * year, adds a new enrollment row for the target year (never touching
     * the old one). Grade 10 students are graduated instead of promoted.
     * Students with no section on their current enrollment are skipped —
     * their grade level can't be determined, so they're left exactly as-is
     * rather than guessed at.
     *
     * @return array{0: int, 1: int, 2: int} [promoted, graduated, unassigned]
     */
    private function promoteStudents(int $sourceYearId, int $targetYearId): array
    {
        $studentModel    = new StudentModel();
        $enrollmentModel = new EnrollmentModel();
        $sectionModel    = new SectionModel();

        $sourceEnrollments = $enrollmentModel
            ->select('enrollments.*, sections.grade_level, sections.name AS section_name')
            ->join('sections', 'sections.id = enrollments.section_id')
            ->join('students', 'students.id = enrollments.student_id')
            ->where('enrollments.school_year_id', $sourceYearId)
            ->where('students.status', 'active')
            ->findAll();

        $promoted   = 0;
        $graduated  = 0;
        $unassigned = 0;

        foreach ($sourceEnrollments as $enrollment) {
            $gradeLevel = (int) $enrollment['grade_level'];
            $studentId  = (int) $enrollment['student_id'];

            if ($gradeLevel === self::GRADUATING_GRADE) {
                $studentModel->update($studentId, ['status' => 'graduated']);
                $graduated++;

                continue;
            }

            if (! in_array($gradeLevel, self::PROMOTABLE_GRADES, true)) {
                continue;
            }

            $targetSection = $sectionModel
                ->where('grade_level', $gradeLevel + 1)
                ->where('name', $enrollment['section_name'])
                ->first();

            $targetSectionId = $targetSection['id'] ?? null;

            if ($targetSectionId === null) {
                $unassigned++;
            } else {
                $promoted++;
            }

            $enrollmentModel->insert([
                'student_id'     => $studentId,
                'section_id'     => $targetSectionId,
                'school_year_id' => $targetYearId,
                'adviser_id'     => $this->resolveConfirmedAdviser($enrollmentModel, $targetSectionId, $enrollment['adviser_id'] ?? null),
            ]);
        }

        return [$promoted, $graduated, $unassigned];
    }

    /**
     * Only carries a student's adviser over to their new enrollment if that
     * same adviser is already on record for the target section (via some
     * other enrollment row) — never guessed.
     */
    private function resolveConfirmedAdviser(EnrollmentModel $enrollmentModel, ?int $targetSectionId, ?int $oldAdviserId): ?int
    {
        if ($targetSectionId === null || $oldAdviserId === null) {
            return null;
        }

        $confirmed = $enrollmentModel
            ->where('section_id', $targetSectionId)
            ->where('adviser_id', $oldAdviserId)
            ->first();

        return $confirmed !== null ? $oldAdviserId : null;
    }

    /**
     * Active-student counts per grade level (7-10) for a given school year,
     * used for the "current counts" preview table.
     *
     * @return array<int, int>
     */
    private function activeGradeCounts(int $schoolYearId): array
    {
        $rows = (new EnrollmentModel())
            ->select('sections.grade_level, COUNT(*) AS total')
            ->join('sections', 'sections.id = enrollments.section_id')
            ->join('students', 'students.id = enrollments.student_id')
            ->where('enrollments.school_year_id', $schoolYearId)
            ->where('students.status', 'active')
            ->groupBy('sections.grade_level')
            ->findAll();

        $counts = [7 => 0, 8 => 0, 9 => 0, 10 => 0];
        foreach ($rows as $row) {
            $counts[(int) $row['grade_level']] = (int) $row['total'];
        }

        return $counts;
    }

    /**
     * Per current section, how many active students will move and to which
     * target section (or "Unassigned" / "Graduating").
     */
    private function buildSectionBreakdown(int $sourceYearId): array
    {
        $rows = (new EnrollmentModel())
            ->select('sections.grade_level, sections.name AS section_name, COUNT(*) AS total')
            ->join('sections', 'sections.id = enrollments.section_id')
            ->join('students', 'students.id = enrollments.student_id')
            ->where('enrollments.school_year_id', $sourceYearId)
            ->where('students.status', 'active')
            ->groupBy(['sections.grade_level', 'sections.id'])
            ->orderBy('sections.grade_level', 'asc')
            ->orderBy('sections.name', 'asc')
            ->findAll();

        $sectionModel = new SectionModel();
        $breakdown    = [];

        foreach ($rows as $row) {
            $gradeLevel = (int) $row['grade_level'];
            $entry      = [
                'grade_level'  => $gradeLevel,
                'section_name' => $row['section_name'],
                'count'        => (int) $row['total'],
            ];

            if ($gradeLevel === self::GRADUATING_GRADE) {
                $entry['target_label'] = 'Graduating';
            } else {
                $target = $sectionModel
                    ->where('grade_level', $gradeLevel + 1)
                    ->where('name', $row['section_name'])
                    ->first();

                $entry['target_label'] = $target !== null
                    ? 'Grade ' . ($gradeLevel + 1) . ' - ' . $target['name']
                    : 'Unassigned — needs manual section assignment';
            }

            $breakdown[] = $entry;
        }

        return $breakdown;
    }

    private function logPromotion(array $sourceYear, int $targetYearId, int $promoted, int $graduated, int $unassigned): void
    {
        $targetYear = (new SchoolYearModel())->find($targetYearId);

        (new AuditLogModel())->insert([
            'user_id'    => session('user_id'),
            'action'     => 'promote_students',
            'table_name' => 'school_years',
            'record_id'  => $targetYearId,
            'old_values' => json_encode(['source_school_year' => $sourceYear['name']]),
            'new_values' => json_encode([
                'target_school_year' => $targetYear['name'] ?? null,
                'promoted'           => $promoted,
                'graduated'          => $graduated,
                'unassigned'         => $unassigned,
            ]),
            'ip_address' => $this->request->getIPAddress(),
        ]);
    }
}
