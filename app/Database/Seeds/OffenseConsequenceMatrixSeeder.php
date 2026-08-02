<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class OffenseConsequenceMatrixSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $rows = [
            // Minor -- student
            ['category' => 'minor', 'offense_number' => 1, 'recommended_action' => 'Written reprimand; parent/guardian notified'],
            ['category' => 'minor', 'offense_number' => 2, 'recommended_action' => 'Written reprimand; formal parent conference called'],
            ['category' => 'minor', 'offense_number' => 3, 'recommended_action' => 'Suspension not exceeding 5 days; alternative learning modalities'],

            // Serious -- student
            ['category' => 'serious', 'offense_number' => 1, 'recommended_action' => 'Suspension not exceeding 5 days; alternative learning modalities; referral to Social Welfare Development Office'],
            ['category' => 'serious', 'offense_number' => 2, 'recommended_action' => 'Non-readmission next school year; referral to Social Welfare Development Office'],
            ['category' => 'serious', 'offense_number' => 3, 'recommended_action' => 'Exclusion (immediately dropped from class list); referral to Social Welfare Development Office'],

            // Severe -- student (no 3rd tier: 2nd offense is already the maximum consequence)
            ['category' => 'severe', 'offense_number' => 1, 'recommended_action' => 'Non-readmission next school year; referral to PNP or Social Welfare Development Office'],
            ['category' => 'severe', 'offense_number' => 2, 'recommended_action' => 'Exclusion (immediately dropped from class list); referral to PNP or Social Welfare Development Office'],

            // General note -- not tied to a category or offense number, shown once on the matrix page.
            [
                'category'           => null,
                'offense_number'     => null,
                'recommended_action' => 'For school personnel: appropriate administrative sanction applies, '
                    . 'without prejudice to possible civil or criminal liability.',
            ],
        ];

        foreach ($rows as &$row) {
            $row['offense_type_id'] = null;
            $row['created_at']      = $now;
            $row['updated_at']      = $now;
        }
        unset($row);

        $this->db->table('offense_consequence_matrix')->insertBatch($rows);
    }
}
