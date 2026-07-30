<?php

namespace App\Models;

use CodeIgniter\Model;

class OthersInvolvedModel extends Model
{
    protected $table         = 'others_involved';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $allowedFields = [
        'case_id',
        'involved_type',
        'name',
        'staff_id',
    ];

    public function getForCase(int $caseId): array
    {
        return $this->where('case_id', $caseId)->findAll();
    }
}
