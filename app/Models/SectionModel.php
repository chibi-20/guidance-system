<?php

namespace App\Models;

use CodeIgniter\Model;

class SectionModel extends Model
{
    protected $table         = 'sections';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $allowedFields = [
        'grade_level',
        'name',
    ];

    /**
     * All sections ordered by grade level then name, for the management list.
     */
    public function getAll(): array
    {
        return $this->orderBy('grade_level', 'asc')
            ->orderBy('name', 'asc')
            ->findAll();
    }

    /**
     * All sections grouped into an array keyed by grade_level, for
     * dropdowns rendered with one <optgroup> per grade.
     *
     * @return array<int, list<array<string, mixed>>>
     */
    public function getGroupedByGrade(): array
    {
        $grouped = [];

        foreach ($this->getAll() as $section) {
            $grouped[(int) $section['grade_level']][] = $section;
        }

        return $grouped;
    }

    /**
     * Count of active students currently enrolled in this section for the
     * CURRENT school year — used to warn before editing a section that's
     * actually in use right now.
     */
    public function countStudents(int $sectionId): int
    {
        return (new EnrollmentModel())
            ->join('school_years', 'school_years.id = enrollments.school_year_id')
            ->join('students', 'students.id = enrollments.student_id')
            ->where('enrollments.section_id', $sectionId)
            ->where('school_years.is_current', 1)
            ->where('students.status', 'active')
            ->countAllResults();
    }

    /**
     * Count of distinct students with ANY enrollment history in this
     * section — any school year, any status — not just the current year.
     * Used to block deleting a section that would corrupt historical
     * enrollment records if removed.
     */
    public function countEnrollmentHistory(int $sectionId): int
    {
        return count(
            (new EnrollmentModel())
                ->select('student_id')
                ->where('section_id', $sectionId)
                ->distinct()
                ->findAll()
        );
    }
}
