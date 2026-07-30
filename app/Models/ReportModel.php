<?php

namespace App\Models;

use CodeIgniter\Model;

class ReportModel extends Model
{
    protected $table          = 'cases';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';

    /**
     * The case list for a date range, optionally narrowed by category,
     * offense_type_id, grade_level, section_id, status. Joined with the
     * student's name and the section they belonged to when each case was
     * filed (via the case's own stored enrollment_id).
     *
     * @param array{category?: ?string, offense_type_id?: ?string, grade_level?: ?string, section_id?: ?string, status?: ?string} $filters
     */
    public function casesByDateRange(string $startDate, string $endDate, array $filters = []): array
    {
        $this->select(
            'cases.*, offense_types.name AS offense_type_name, offense_types.category AS offense_type_category, '
            . 'students.last_name AS student_last_name, students.first_name AS student_first_name, '
            . 'sections.grade_level, sections.name AS section_name'
        )
            ->join('offense_types', 'offense_types.id = cases.offense_type_id', 'left')
            ->join('students', 'students.id = cases.student_id', 'left')
            ->join('enrollments', 'enrollments.id = cases.enrollment_id', 'left')
            ->join('sections', 'sections.id = enrollments.section_id', 'left')
            ->where('cases.date_of_incident >=', $startDate)
            ->where('cases.date_of_incident <=', $endDate);

        $this->applyFilters($filters);

        return $this->orderBy('cases.date_of_incident', 'desc')
            ->orderBy('cases.id', 'desc')
            ->findAll();
    }

    /**
     * Case counts grouped by offense type, highest first.
     */
    public function summaryByOffenseType(string $startDate, string $endDate, array $filters = []): array
    {
        $this->select('cases.offense_type_id, offense_types.name AS offense_type_name, offense_types.category, COUNT(*) AS total')
            ->join('offense_types', 'offense_types.id = cases.offense_type_id', 'left')
            ->join('enrollments', 'enrollments.id = cases.enrollment_id', 'left')
            ->join('sections', 'sections.id = enrollments.section_id', 'left')
            ->where('cases.date_of_incident >=', $startDate)
            ->where('cases.date_of_incident <=', $endDate);

        $this->applyFilters($filters);

        return $this->groupBy('cases.offense_type_id')
            ->orderBy('total', 'desc')
            ->findAll();
    }

    /**
     * Case counts grouped by category (grave/minor).
     */
    public function summaryByCategory(string $startDate, string $endDate, array $filters = []): array
    {
        $this->select('cases.category, COUNT(*) AS total')
            ->join('enrollments', 'enrollments.id = cases.enrollment_id', 'left')
            ->join('sections', 'sections.id = enrollments.section_id', 'left')
            ->where('cases.date_of_incident >=', $startDate)
            ->where('cases.date_of_incident <=', $endDate);

        $this->applyFilters($filters);

        return $this->groupBy('cases.category')->findAll();
    }

    /**
     * Case counts grouped by section, highest first — useful for spotting
     * which section/adviser needs support. Cases filed while the student
     * had no active enrollment are grouped together with a null section.
     */
    public function summaryBySection(string $startDate, string $endDate, array $filters = []): array
    {
        $this->select('sections.id AS section_id, sections.grade_level, sections.name AS section_name, COUNT(*) AS total')
            ->join('enrollments', 'enrollments.id = cases.enrollment_id', 'left')
            ->join('sections', 'sections.id = enrollments.section_id', 'left')
            ->where('cases.date_of_incident >=', $startDate)
            ->where('cases.date_of_incident <=', $endDate);

        $this->applyFilters($filters);

        return $this->groupBy('sections.id')
            ->orderBy('total', 'desc')
            ->findAll();
    }

    /**
     * Distinct students whose highest offense_count_overall across all
     * their (non-deleted) cases has reached $threshold, with their current
     * section, total case count, and most recent incident date. Not scoped
     * to a date range — this is a standing "who needs attention" list.
     *
     * Written as one raw grouped query (like CaseModel::countFlaggedRepeatOffenders)
     * since GROUP BY + HAVING on an aggregate expression is awkward to get
     * right through the query builder's having() escaping rules.
     */
    public function repeatOffendersSummary(int $threshold = 3): array
    {
        $currentEnrollmentJoin = 'enrollments.student_id = students.id'
            . ' AND enrollments.school_year_id = (SELECT id FROM school_years WHERE is_current = 1 LIMIT 1)';

        $sql = "SELECT
                    students.id AS student_id,
                    students.last_name,
                    students.first_name,
                    sections.grade_level,
                    sections.name AS section_name,
                    COUNT(cases.id) AS total_cases,
                    MAX(cases.offense_count_overall) AS max_overall,
                    MAX(cases.date_of_incident) AS most_recent_case_date
                FROM cases
                INNER JOIN students ON students.id = cases.student_id
                LEFT JOIN enrollments ON {$currentEnrollmentJoin}
                LEFT JOIN sections ON sections.id = enrollments.section_id
                WHERE cases.deleted_at IS NULL
                GROUP BY students.id
                HAVING max_overall >= ?
                ORDER BY total_cases DESC";

        return $this->db->query($sql, [$threshold])->getResultArray();
    }

    /**
     * Resolved vs. open/ongoing case counts for the range, plus the average
     * number of days between date_of_incident and resolved_at for cases
     * resolved within that range.
     *
     * @return array{resolved: int, open: int, avg_resolution_days: ?float}
     */
    public function resolutionStats(string $startDate, string $endDate): array
    {
        $resolved = $this->where('date_of_incident >=', $startDate)
            ->where('date_of_incident <=', $endDate)
            ->where('status', 'resolved')
            ->countAllResults();

        $open = $this->where('date_of_incident >=', $startDate)
            ->where('date_of_incident <=', $endDate)
            ->whereIn('status', ['open', 'ongoing'])
            ->countAllResults();

        $avgRow = $this->db->table('cases')
            ->select('AVG(DATEDIFF(case_actions.resolved_at, cases.date_of_incident)) AS avg_days')
            ->join('case_actions', 'case_actions.case_id = cases.id', 'inner')
            ->where('cases.date_of_incident >=', $startDate)
            ->where('cases.date_of_incident <=', $endDate)
            ->where('cases.status', 'resolved')
            ->where('cases.deleted_at', null)
            ->get()
            ->getRowArray();

        return [
            'resolved'             => $resolved,
            'open'                 => $open,
            'avg_resolution_days'  => $avgRow['avg_days'] !== null ? round((float) $avgRow['avg_days'], 1) : null,
        ];
    }

    private function applyFilters(array $filters): void
    {
        if (! empty($filters['category'])) {
            $this->where('cases.category', $filters['category']);
        }
        if (! empty($filters['offense_type_id'])) {
            $this->where('cases.offense_type_id', $filters['offense_type_id']);
        }
        if (! empty($filters['grade_level'])) {
            $this->where('sections.grade_level', $filters['grade_level']);
        }
        if (! empty($filters['section_id'])) {
            $this->where('sections.id', $filters['section_id']);
        }
        if (! empty($filters['status'])) {
            $this->where('cases.status', $filters['status']);
        }
    }
}
