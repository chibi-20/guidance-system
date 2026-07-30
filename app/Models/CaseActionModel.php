<?php

namespace App\Models;

use CodeIgniter\Model;

class CaseActionModel extends Model
{
    protected $table         = 'case_actions';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $allowedFields = [
        'case_id',
        'action_prior',
        'perceived_motivation',
        'disciplinary_action',
        'parents_notified_thru',
        'conference_with',
        'referred_to',
        'behavior_contract',
        'exclusion_transfer',
        'remarks',
        'resolved_by',
        'resolved_at',
    ];

    public function findByCaseId(int $caseId): ?array
    {
        return $this->where('case_id', $caseId)->first();
    }

    /**
     * case_id is unique on this table, so resolving the same case twice
     * (e.g. correcting a mistake) overwrites the existing row instead of
     * violating the constraint or leaving stale duplicates.
     */
    public function upsertForCase(int $caseId, array $data): void
    {
        $existing = $this->findByCaseId($caseId);

        if ($existing !== null) {
            $this->update($existing['id'], $data);

            return;
        }

        $data['case_id'] = $caseId;
        $this->insert($data);
    }
}
