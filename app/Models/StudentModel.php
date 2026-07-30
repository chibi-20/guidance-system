<?php

namespace App\Models;

use CodeIgniter\Model;

class StudentModel extends Model
{
    protected $table          = 'students';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';
    protected $useTimestamps  = true;
    protected $createdField   = 'created_at';
    protected $updatedField   = 'updated_at';

    protected $allowedFields = [
        'lrn',
        'last_name',
        'first_name',
        'middle_name',
        'suffix',
        'gender',
        'birthdate',
        'place_of_birth',
        'address',
        'citizenship',
        'religion',
        'height_cm',
        'weight_kg',
        'guardian_name',
        'guardian_contact',
        'photo_path',
        'status',
    ];

    /**
     * Joins an enrollment row to the given student, restricted to the
     * currently active school year via a scalar subquery (rather than an
     * ON-clause filter), so a student with enrollment history from past
     * years still returns exactly one row.
     */
    private const CURRENT_ENROLLMENT_JOIN = 'enrollments.student_id = students.id'
        . ' AND enrollments.school_year_id = (SELECT id FROM school_years WHERE is_current = 1 LIMIT 1)';

    /**
     * Returns a single student with grade level, section name, and adviser
     * name from their current-school-year enrollment (if any), or null if
     * the student doesn't exist.
     */
    public function getWithCurrentEnrollment(int $studentId): ?array
    {
        return $this->select('students.*, enrollments.id AS enrollment_id, enrollments.section_id, enrollments.adviser_id, sections.grade_level, sections.name AS section_name, users.full_name AS adviser_name')
            ->join('enrollments', self::CURRENT_ENROLLMENT_JOIN, 'left')
            ->join('sections', 'sections.id = enrollments.section_id', 'left')
            ->join('users', 'users.id = enrollments.adviser_id', 'left')
            ->where('students.id', $studentId)
            ->first();
    }

    /**
     * Searches last_name, first_name, and lrn, joined with current
     * enrollment section info, sorted and paginated. After calling this,
     * $this->pager holds the Pager instance for rendering page links.
     *
     * $onlyUnassigned restricts to students who have a current-year
     * enrollment row but no section on it (left that way by promotion when
     * no same-named section existed at the next grade level) — the "needs
     * manual section assignment" cleanup list.
     */
    public function searchPaginated(?string $keyword = null, int $perPage = 25, string $sortBy = 'last_name', string $sortDir = 'asc', bool $onlyUnassigned = false): array
    {
        $allowedSorts = ['last_name', 'first_name', 'lrn'];
        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'last_name';
        }
        $sortDir = strtolower($sortDir) === 'desc' ? 'desc' : 'asc';

        $builder = $this->select('students.*, sections.grade_level, sections.name AS section_name')
            ->join('enrollments', self::CURRENT_ENROLLMENT_JOIN, 'left')
            ->join('sections', 'sections.id = enrollments.section_id', 'left');

        if ($keyword !== null && $keyword !== '') {
            $builder->groupStart()
                ->like('students.last_name', $keyword)
                ->orLike('students.first_name', $keyword)
                ->orLike('students.lrn', $keyword)
                ->groupEnd();
        }

        if ($onlyUnassigned) {
            $builder->where('enrollments.id IS NOT NULL')
                ->where('enrollments.section_id', null);
        }

        $builder->orderBy('students.' . $sortBy, $sortDir);

        return $builder->paginate($perPage);
    }

    /**
     * Count of students currently marked active (for dashboard cards).
     */
    public function countActive(): int
    {
        return $this->where('status', 'active')->countAllResults();
    }
}
