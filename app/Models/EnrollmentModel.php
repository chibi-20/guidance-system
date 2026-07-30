<?php

namespace App\Models;

use CodeIgniter\Model;

class EnrollmentModel extends Model
{
    protected $table         = 'enrollments';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $allowedFields = [
        'student_id',
        'section_id',
        'school_year_id',
        'adviser_id',
    ];

    /**
     * Every enrollment row for a student across all school years — not just
     * the current one — newest school year first. Promotion only ever adds
     * a new row here; it never edits or removes an existing one, so this is
     * what proves a student's Grade 7/8/9 history (and the cases filed
     * during it) stays exactly as it was after they're promoted.
     */
    public function getHistoryForStudent(int $studentId): array
    {
        return $this->select(
            'enrollments.*, school_years.name AS school_year_name, school_years.start_date, '
            . 'sections.grade_level, sections.name AS section_name, users.full_name AS adviser_name'
        )
            ->join('school_years', 'school_years.id = enrollments.school_year_id', 'left')
            ->join('sections', 'sections.id = enrollments.section_id', 'left')
            ->join('users', 'users.id = enrollments.adviser_id', 'left')
            ->where('enrollments.student_id', $studentId)
            ->orderBy('school_years.start_date', 'desc')
            ->orderBy('enrollments.id', 'desc')
            ->findAll();
    }
}
