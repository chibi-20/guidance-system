<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class OffenseTypeSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $offenses = [
            // Minor offenses
            [
                'category'       => 'minor',
                'name'           => 'Tardiness',
                'description'    => 'Arriving late to class or school without valid reason.',
                'default_action' => 'Verbal warning; parent notification on 3rd offense.',
                'is_active'      => 1,
            ],
            [
                'category'       => 'minor',
                'name'           => 'Absenteeism',
                'description'    => 'Repeated unexcused absences from class or school.',
                'default_action' => 'Verbal warning; guidance conference; parent notification on repeated offense.',
                'is_active'      => 1,
            ],
            [
                'category'       => 'minor',
                'name'           => 'Cutting classes',
                'description'    => 'Leaving or skipping class without permission.',
                'default_action' => 'Written warning; parent conference.',
                'is_active'      => 1,
            ],
            [
                'category'       => 'minor',
                'name'           => 'Failure to wear prescribed uniform',
                'description'    => 'Not wearing the required school uniform.',
                'default_action' => 'Verbal warning; uniform compliance reminder sent to parent.',
                'is_active'      => 1,
            ],
            [
                'category'       => 'minor',
                'name'           => 'Improper haircut/grooming',
                'description'    => 'Violation of school grooming standards.',
                'default_action' => 'Verbal warning; grooming compliance reminder sent to parent.',
                'is_active'      => 1,
            ],
            [
                'category'       => 'minor',
                'name'           => 'Loitering',
                'description'    => 'Lingering in unauthorized areas during class hours.',
                'default_action' => 'Verbal warning; supervised study hall.',
                'is_active'      => 1,
            ],
            [
                'category'       => 'minor',
                'name'           => 'Littering',
                'description'    => 'Improper disposal of waste within school premises.',
                'default_action' => 'Verbal warning; assigned cleaning duty.',
                'is_active'      => 1,
            ],

            // Grave offenses
            [
                'category'       => 'grave',
                'name'           => 'Bullying',
                'description'    => 'Repeated aggressive behavior intended to harm or intimidate another student.',
                'default_action' => 'Guidance conference; parent notification; written apology; possible suspension.',
                'is_active'      => 1,
            ],
            [
                'category'       => 'grave',
                'name'           => 'Physical fighting',
                'description'    => 'Engaging in a physical altercation with another student or staff member.',
                'default_action' => 'Immediate parent notification; suspension pending investigation.',
                'is_active'      => 1,
            ],
            [
                'category'       => 'grave',
                'name'           => 'Vandalism',
                'description'    => 'Willful destruction or defacement of school or others\' property.',
                'default_action' => 'Parent notification; restitution for damages; suspension.',
                'is_active'      => 1,
            ],
            [
                'category'       => 'grave',
                'name'           => 'Theft',
                'description'    => 'Taking another person\'s property without consent.',
                'default_action' => 'Parent notification; restitution; suspension pending investigation.',
                'is_active'      => 1,
            ],
            [
                'category'       => 'grave',
                'name'           => 'Possession of prohibited items',
                'description'    => 'Carrying items banned by school policy (e.g. weapons, vape, drugs).',
                'default_action' => 'Confiscation of item; parent notification; suspension.',
                'is_active'      => 1,
            ],
            [
                'category'       => 'grave',
                'name'           => 'Disrespect to authority',
                'description'    => 'Insubordination or disrespectful conduct toward school personnel.',
                'default_action' => 'Guidance conference; parent notification; written apology.',
                'is_active'      => 1,
            ],
        ];

        foreach ($offenses as &$offense) {
            $offense['created_at'] = $now;
            $offense['updated_at'] = $now;
        }
        unset($offense);

        $this->db->table('offense_types')->insertBatch($offenses);
    }
}
