<?php

namespace App\Models;

use CodeIgniter\Model;

class OffenseConsequenceModel extends Model
{
    protected $table         = 'offense_consequence_matrix';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $allowedFields = [
        'offense_type_id',
        'category',
        'offense_number',
        'recommended_action',
    ];

    /**
     * The recommended consequence for a student's Nth offense within a
     * category. Checks for an offense-type-specific override first (none
     * are seeded today, but the mechanism exists for a future exception),
     * then the category-level entry for that exact offense number, then —
     * if beyond the highest tier defined for that category (e.g. a 4th
     * minor offense, or a 3rd severe offense) — reuses the highest defined
     * tier's action instead of returning nothing.
     */
    public function getRecommendation(int $offenseTypeId, string $category, int $offenseNumber): ?array
    {
        $override = $this->where('offense_type_id', $offenseTypeId)
            ->where('offense_number', $offenseNumber)
            ->first();
        if ($override !== null) {
            return $override;
        }

        $exact = $this->where('offense_type_id', null)
            ->where('category', $category)
            ->where('offense_number', $offenseNumber)
            ->first();
        if ($exact !== null) {
            return $exact;
        }

        return $this->where('offense_type_id', null)
            ->where('category', $category)
            ->where('offense_number <=', $offenseNumber)
            ->orderBy('offense_number', 'desc')
            ->first();
    }

    /**
     * The single informational note shown once on the Offense Matrix page,
     * not tied to any category or offense number.
     */
    public function getGeneralNote(): ?string
    {
        $row = $this->where('category', null)->where('offense_number', null)->first();

        return $row['recommended_action'] ?? null;
    }

    /**
     * Category-level matrix rows (no offense-type-specific overrides),
     * grouped by category, each already ordered by offense_number — for the
     * Offense Matrix page. 'minor' < 'serious' < 'severe' sorts correctly in
     * plain alphabetical order, conveniently matching severity order too.
     */
    public function getAllGroupedByCategory(): array
    {
        $rows = $this->where('offense_type_id', null)
            ->where('category IS NOT NULL')
            ->orderBy('category', 'asc')
            ->orderBy('offense_number', 'asc')
            ->findAll();

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['category']][] = $row;
        }

        return $grouped;
    }
}
