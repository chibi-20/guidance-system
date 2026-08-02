<?php

namespace App\Models;

use CodeIgniter\Model;

class CaseModel extends Model
{
    protected $table          = 'cases';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';
    protected $useTimestamps  = true;
    protected $createdField   = 'created_at';
    protected $updatedField   = 'updated_at';

    protected $allowedFields = [
        'case_no',
        'student_id',
        'enrollment_id',
        'offense_type_id',
        'category',
        'date_of_incident',
        'time_of_incident',
        'location',
        'incident_report',
        'narrative',
        'referred_by',
        'adviser_id',
        'offense_count_type',
        'offense_count_overall',
        'status',
        'school_year_id',
        'created_by',
    ];

    /**
     * Count of existing (non-deleted) cases for this student against this
     * specific offense type. Callers add 1 to include the case being filed.
     */
    public function countByStudentAndOffenseType(int $studentId, int $offenseTypeId): int
    {
        return $this->where('student_id', $studentId)
            ->where('offense_type_id', $offenseTypeId)
            ->countAllResults();
    }

    /**
     * Total existing (non-deleted) case count for this student across all
     * offense types. Callers add 1 to include the case being filed.
     */
    public function countByStudent(int $studentId): int
    {
        return $this->where('student_id', $studentId)
            ->countAllResults();
    }

    /**
     * Which tier (1st, 2nd, 3rd...) this specific case represents among all
     * of a student's cases in the same CATEGORY (minor/serious/severe),
     * counting only cases filed at or before it. The offense consequence
     * matrix escalates per category rather than per specific offense type,
     * so this — not countByStudentAndOffenseType — is the offense number to
     * feed into OffenseConsequenceModel::getRecommendation(). Computed by
     * id ordering (filing order) rather than stored on the case itself, so
     * an earlier case's displayed tier never shifts just because a later
     * case of the same category gets added afterward.
     */
    public function categoryOffenseNumberAsOf(int $studentId, string $category, int $caseId): int
    {
        return $this->where('student_id', $studentId)
            ->where('category', $category)
            ->where('id <=', $caseId)
            ->countAllResults();
    }

    /**
     * All cases for a student, newest incident first, with the offense
     * type's name and category joined in.
     */
    public function getCaseHistory(int $studentId): array
    {
        return $this->select('cases.*, offense_types.name AS offense_type_name, offense_types.category AS offense_type_category')
            ->join('offense_types', 'offense_types.id = cases.offense_type_id', 'left')
            ->where('cases.student_id', $studentId)
            ->orderBy('cases.date_of_incident', 'desc')
            ->orderBy('cases.id', 'desc')
            ->findAll();
    }

    /**
     * Everything the Discipline Action Form PDF needs in one row: the case,
     * offense type, student, and the section/adviser the student had at the
     * time the case was filed (via the case's own stored enrollment_id and
     * adviser_id, not whatever the student's enrollment is *today*).
     */
    public function getFullDetailsForPdf(int $caseId): ?array
    {
        return $this->select(
            'cases.*, '
            . 'offense_types.name AS offense_type_name, offense_types.category AS offense_type_category, '
            . 'offense_types.description AS offense_type_description, '
            . 'students.last_name AS student_last_name, students.first_name AS student_first_name, '
            . 'students.middle_name AS student_middle_name, students.lrn AS student_lrn, '
            . 'sections.grade_level, sections.name AS section_name, '
            . 'adviser.full_name AS adviser_name, '
            . 'referrer.full_name AS referred_by_name'
        )
            ->join('offense_types', 'offense_types.id = cases.offense_type_id', 'left')
            ->join('students', 'students.id = cases.student_id', 'left')
            ->join('enrollments', 'enrollments.id = cases.enrollment_id', 'left')
            ->join('sections', 'sections.id = enrollments.section_id', 'left')
            ->join('users AS adviser', 'adviser.id = cases.adviser_id', 'left')
            ->join('users AS referrer', 'referrer.id = cases.referred_by', 'left')
            ->where('cases.id', $caseId)
            ->first();
    }

    /**
     * Produces the next case number for the current year, e.g. "2026-0001".
     * Searches including soft-deleted rows, since case_no is unique and a
     * deleted case's number must never be reissued.
     */
    public function generateCaseNo(): string
    {
        $prefix = date('Y') . '-';

        $last = $this->withDeleted()
            ->select('case_no')
            ->like('case_no', $prefix, 'after')
            ->orderBy('case_no', 'desc')
            ->first();

        $nextSeq = 1;
        if ($last !== null) {
            $nextSeq = ((int) substr($last['case_no'], strlen($prefix))) + 1;
        }

        return $prefix . str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Global, filterable, paginated case list joined with offense type,
     * student, and the section the student belonged to at the time the
     * case was filed (via the case's own stored enrollment_id, not the
     * student's *current* enrollment, so historical cases keep showing the
     * grade/section they actually happened in).
     *
     * @param array{date_from?: ?string, date_to?: ?string, offense_type_id?: ?string, category?: ?string, status?: ?string, grade_level?: ?string, section_id?: ?string} $filters
     */
    public function getFilteredPaginated(array $filters, int $perPage = 25): array
    {
        $builder = $this->select(
            'cases.*, offense_types.name AS offense_type_name, offense_types.category AS offense_type_category, '
            . 'students.last_name AS student_last_name, students.first_name AS student_first_name, '
            . 'sections.grade_level, sections.name AS section_name'
        )
            ->join('offense_types', 'offense_types.id = cases.offense_type_id', 'left')
            ->join('students', 'students.id = cases.student_id', 'left')
            ->join('enrollments', 'enrollments.id = cases.enrollment_id', 'left')
            ->join('sections', 'sections.id = enrollments.section_id', 'left');

        if (! empty($filters['date_from'])) {
            $builder->where('cases.date_of_incident >=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $builder->where('cases.date_of_incident <=', $filters['date_to']);
        }
        if (! empty($filters['offense_type_id'])) {
            $builder->where('cases.offense_type_id', $filters['offense_type_id']);
        }
        if (! empty($filters['category'])) {
            $builder->where('cases.category', $filters['category']);
        }
        if (! empty($filters['status'])) {
            $builder->where('cases.status', $filters['status']);
        }
        if (! empty($filters['grade_level'])) {
            $builder->where('sections.grade_level', $filters['grade_level']);
        }
        if (! empty($filters['section_id'])) {
            $builder->where('sections.id', $filters['section_id']);
        }
        if (! empty($filters['min_count_overall'])) {
            $builder->where('cases.offense_count_overall >=', $filters['min_count_overall']);
        }

        $builder->orderBy('cases.date_of_incident', 'desc')->orderBy('cases.id', 'desc');

        return $builder->paginate($perPage);
    }

    /**
     * Count of open/ongoing cases, for the dashboard card.
     */
    public function countOpen(): int
    {
        return $this->whereIn('status', ['open', 'ongoing'])->countAllResults();
    }

    /**
     * Count of cases whose incident date falls within the current
     * calendar month, for the dashboard card.
     */
    public function countThisMonth(): int
    {
        return $this->where('date_of_incident >=', date('Y-m-01'))
            ->where('date_of_incident <=', date('Y-m-t'))
            ->countAllResults();
    }

    /**
     * Count of distinct students whose highest offense_count_overall value
     * across all their (non-deleted) cases has reached $threshold. Written
     * as one raw grouped query rather than pulling case rows into PHP, since
     * this needs to run over a growing cases table on every dashboard load.
     */
    public function countFlaggedRepeatOffenders(int $threshold = 3): int
    {
        $sql = 'SELECT COUNT(*) AS flagged_count FROM ('
            . "SELECT student_id, MAX(offense_count_overall) AS max_overall FROM {$this->table} "
            . 'WHERE deleted_at IS NULL GROUP BY student_id HAVING max_overall >= ?'
            . ') AS flagged_students';

        $row = $this->db->query($sql, [$threshold])->getRowArray();

        return (int) ($row['flagged_count'] ?? 0);
    }

    /**
     * Latest filed cases (by created_at, i.e. when the case was recorded,
     * not necessarily the incident date), for the dashboard activity feed.
     */
    public function getRecentCases(int $limit = 10): array
    {
        return $this->select(
            'cases.id, cases.case_no, cases.date_of_incident, cases.status, '
            . 'students.last_name AS student_last_name, students.first_name AS student_first_name, '
            . 'offense_types.name AS offense_type_name, offense_types.category AS offense_type_category'
        )
            ->join('students', 'students.id = cases.student_id', 'left')
            ->join('offense_types', 'offense_types.id = cases.offense_type_id', 'left')
            ->orderBy('cases.created_at', 'desc')
            ->orderBy('cases.id', 'desc')
            ->findAll($limit);
    }

    /**
     * Case counts grouped by category (minor/serious/severe) for the
     * current calendar month, for the dashboard chart. A category with zero
     * cases this month simply won't appear in the result — callers should
     * default missing categories to 0.
     */
    public function getCasesByCategoryThisMonth(): array
    {
        return $this->select('category, COUNT(*) AS total')
            ->where('date_of_incident >=', date('Y-m-01'))
            ->where('date_of_incident <=', date('Y-m-t'))
            ->groupBy('category')
            ->findAll();
    }

    /**
     * The most frequently cited offense types across all cases, for the
     * dashboard chart.
     */
    public function getTopOffenseTypes(int $limit = 5): array
    {
        return $this->select('cases.offense_type_id, offense_types.name AS offense_type_name, COUNT(*) AS total')
            ->join('offense_types', 'offense_types.id = cases.offense_type_id', 'left')
            ->groupBy('cases.offense_type_id')
            ->orderBy('total', 'desc')
            ->findAll($limit);
    }
}
