<?php

namespace App\Models;

use CodeIgniter\Model;

class OffenseTypeModel extends Model
{
    protected $table         = 'offense_types';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $allowedFields = [
        'category',
        'name',
        'description',
        'default_action',
        'is_active',
    ];

    /**
     * Active offense types only, grouped/ordered by category then name —
     * for use in case-filing dropdowns.
     */
    public function getActive(): array
    {
        return $this->where('is_active', 1)
            ->orderBy('category', 'asc')
            ->orderBy('name', 'asc')
            ->findAll();
    }

    /**
     * All offense types, including inactive ones, for the management list.
     */
    public function getAll(): array
    {
        return $this->orderBy('category', 'asc')
            ->orderBy('name', 'asc')
            ->findAll();
    }
}
