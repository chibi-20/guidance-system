<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class OffenseTypeSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $minorNote   = 'Addressed via written reprimand; escalates to suspension on repeated offenses per the offense consequence matrix.';
        $seriousNote = 'Addressed via suspension; escalates to non-readmission or exclusion on repeated offenses per the offense consequence matrix.';
        $severeNote  = 'Addressed via non-readmission; may involve PNP/DSWD referral per the offense consequence matrix.';

        $offenses = [
            // ---- MINOR (Level 1) -- official DepEd list ----
            ['category' => 'minor', 'name' => 'Cursing or use of vulgar language', 'default_action' => $minorNote],
            ['category' => 'minor', 'name' => 'Pranks inside the classroom', 'default_action' => $minorNote],
            ['category' => 'minor', 'name' => 'Spreading false information or fake news', 'default_action' => $minorNote],
            ['category' => 'minor', 'name' => 'Vandalism', 'default_action' => $minorNote],
            ['category' => 'minor', 'name' => 'Other similar violations (Minor)', 'default_action' => $minorNote],

            // ---- SERIOUS (Level 2) -- official DepEd list ----
            ['category' => 'serious', 'name' => 'Stalking within school premises', 'default_action' => $seriousNote],
            ['category' => 'serious', 'name' => 'Gambling or possession of gambling paraphernalia', 'default_action' => $seriousNote],
            ['category' => 'serious', 'name' => 'Threats or intimidation of a fellow student or staff, in person or via social media', 'default_action' => $seriousNote],
            ['category' => 'serious', 'name' => 'Theft', 'default_action' => $seriousNote],
            ['category' => 'serious', 'name' => 'Smoking', 'default_action' => $seriousNote],
            ['category' => 'serious', 'name' => 'Other similar violations (Serious)', 'default_action' => $seriousNote],

            // ---- SEVERE (Level 3) -- official DepEd list ----
            ['category' => 'severe', 'name' => 'Possession of deadly weapons', 'default_action' => $severeNote],
            ['category' => 'severe', 'name' => 'Bomb threat or bomb joke', 'default_action' => $severeNote],
            ['category' => 'severe', 'name' => 'Serious physical harm to a student or school personnel', 'default_action' => $severeNote],
            ['category' => 'severe', 'name' => 'Cheating during examination', 'default_action' => $severeNote],
            ['category' => 'severe', 'name' => 'Joining, organizing, or recruiting for a fraternity, sorority, or street gang', 'default_action' => $severeNote],
            ['category' => 'severe', 'name' => 'Homicide or murder', 'default_action' => $severeNote],
            ['category' => 'severe', 'name' => 'Use, possession, or sale of prohibited drugs', 'default_action' => $severeNote],
            ['category' => 'severe', 'name' => 'Other similar violations (Severe)', 'default_action' => $severeNote],

            // ---- Existing attendance/conduct issues DepEd's circular doesn't
            //      itemize but the school still tracks -- kept as minor. ----
            ['category' => 'minor', 'name' => 'Tardiness', 'default_action' => 'Verbal warning; parent notification on 3rd offense.'],
            ['category' => 'minor', 'name' => 'Absenteeism', 'default_action' => 'Verbal warning; guidance conference; parent notification on repeated offense.'],
            ['category' => 'minor', 'name' => 'Cutting classes', 'default_action' => 'Written warning; parent conference.'],
            ['category' => 'minor', 'name' => 'Failure to wear prescribed uniform', 'default_action' => 'Verbal warning; uniform compliance reminder sent to parent.'],
            ['category' => 'minor', 'name' => 'Improper haircut/grooming', 'default_action' => 'Verbal warning; grooming compliance reminder sent to parent.'],
            ['category' => 'minor', 'name' => 'Loitering', 'default_action' => 'Verbal warning; supervised study hall.'],
            ['category' => 'minor', 'name' => 'Littering', 'default_action' => 'Verbal warning; assigned cleaning duty.'],

            // ---- Existing school-specific entries kept from before the
            //      three-tier migration, reclassified per the official list
            //      (see the ReclassifyOffenseCategoriesToThreeTier and
            //      CorrectOffenseSeverityClassifications migrations for the
            //      full old->new mapping history). ----
            ['category' => 'serious', 'name' => 'Bullying', 'default_action' => 'Guidance conference; parent notification; written apology; possible suspension.'],
            ['category' => 'severe', 'name' => 'Physical fighting', 'default_action' => 'Immediate parent notification; suspension pending investigation.'],
            ['category' => 'serious', 'name' => 'Possession of vape/e-cigarette', 'default_action' => 'Confiscation of item; parent notification; suspension.'],
            ['category' => 'severe', 'name' => 'Possession/use of prohibited drugs', 'default_action' => 'Confiscation of item; parent notification; suspension.'],
            ['category' => 'serious', 'name' => 'Disrespect to authority', 'default_action' => 'Guidance conference; parent notification; written apology.'],
        ];

        foreach ($offenses as &$offense) {
            $offense['description']    = $offense['description'] ?? null;
            $offense['is_active']      = 1;
            $offense['created_at']     = $now;
            $offense['updated_at']     = $now;
        }
        unset($offense);

        $this->db->table('offense_types')->insertBatch($offenses);
    }
}
