<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Follow-up correction to ReclassifyOffenseCategoriesToThreeTier: two rows
 * that migration defaulted to 'serious' pending manual review are now
 * confirmed to actually be 'severe', and four official DepEd Severe-tier
 * offense types were missing entirely.
 *
 * This only ever touches offense_types.category/name. It deliberately does
 * NOT touch the cases table — a case's category is a point-in-time snapshot
 * taken at filing time (see CaseController::store()), and existing cases
 * should keep showing whatever category applied when they were filed. Only
 * NEW cases filed against these offense types going forward will pick up
 * the corrected category.
 */
class CorrectOffenseSeverityClassifications extends Migration
{
    private const NEW_SEVERE_OFFENSES = [
        'Possession of deadly weapons',
        'Bomb threat or bomb joke',
        'Joining, organizing, or recruiting for a fraternity, sorority, or street gang',
        'Homicide or murder',
    ];

    private const SEVERE_DEFAULT_ACTION = 'Non-readmission next school year; referral to PNP or Social Welfare Development Office.';

    public function up()
    {
        $this->db->table('offense_types')
            ->where('name', 'Physical fighting')
            ->update(['category' => 'severe']);

        $this->db->table('offense_types')
            ->where('name', 'Possession of prohibited items')
            ->update(['category' => 'severe', 'name' => 'Possession/use of prohibited drugs']);

        $now = date('Y-m-d H:i:s');

        foreach (self::NEW_SEVERE_OFFENSES as $name) {
            $existing = $this->db->table('offense_types')->where('name', $name)->get()->getRowArray();

            if ($existing !== null) {
                continue;
            }

            $this->db->table('offense_types')->insert([
                'category'       => 'severe',
                'name'           => $name,
                'description'    => null,
                'default_action' => self::SEVERE_DEFAULT_ACTION,
                'is_active'      => 1,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }
    }

    public function down()
    {
        $this->db->table('offense_types')
            ->where('name', 'Physical fighting')
            ->update(['category' => 'serious']);

        $this->db->table('offense_types')
            ->where('name', 'Possession/use of prohibited drugs')
            ->update(['category' => 'serious', 'name' => 'Possession of prohibited items']);

        $this->db->table('offense_types')->whereIn('name', self::NEW_SEVERE_OFFENSES)->delete();
    }
}
