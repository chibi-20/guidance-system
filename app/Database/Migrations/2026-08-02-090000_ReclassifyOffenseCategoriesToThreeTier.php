<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Moves offense classification from DepEd's old two-tier system
 * (grave/minor) to the official three-tier system (minor/serious/severe).
 *
 * Existing offense_types rows are reclassified by matching their name
 * against the official DepEd list. A couple of pre-existing "grave" rows
 * don't appear on that list and can't be confidently auto-matched — those
 * are defaulted to 'serious' (a conservative middle position, since they
 * were previously the more severe of the old two tiers) and logged as a
 * warning for manual review rather than guessed at silently.
 *
 * cases.category is a point-in-time snapshot of its offense type's category
 * at filing time. Since this migration is a system-wide classification
 * correction (not a retroactive editorial change to any individual case),
 * every case's snapshot is brought in line with its offense type's
 * corrected category.
 */
class ReclassifyOffenseCategoriesToThreeTier extends Migration
{
    /**
     * Existing offense_types.name => new category, for names that cleanly
     * match (or are a clear real-world equivalent of) an item on DepEd's
     * official three-tier list.
     */
    private const CONFIDENT_RECLASSIFICATIONS = [
        'Bullying'                        => 'serious',
        'Physical fighting'               => 'serious',
        'Vandalism'                       => 'minor',
        'Theft'                           => 'serious',
        'Possession of vape/e-cigarette'  => 'serious',
        'Cheating during examination'     => 'severe',
    ];

    /**
     * Existing 'grave' rows with no clean match on the official list.
     * Defaulted to 'serious' pending manual review — see the logged
     * warning and the migration's own summary output.
     */
    private const NEEDS_REVIEW_DEFAULT = [
        'Possession of prohibited items' => 'serious',
        'Disrespect to authority'        => 'serious',
    ];

    public function up()
    {
        // Widen both ENUMs to a superset so old ('grave') and new values
        // can coexist while data below is reclassified row by row.
        $this->widenCategoryEnum('offense_types');
        $this->widenCategoryEnum('cases');

        foreach (self::CONFIDENT_RECLASSIFICATIONS as $name => $category) {
            $this->db->table('offense_types')->where('name', $name)->update(['category' => $category]);
        }

        foreach (self::NEEDS_REVIEW_DEFAULT as $name => $category) {
            $existing = $this->db->table('offense_types')->where('name', $name)->get()->getRowArray();

            if ($existing !== null) {
                log_message(
                    'warning',
                    "Offense type '{$name}' (id {$existing['id']}) had no clean match in the official DepEd "
                        . "three-tier list during the grave->minor/serious/severe migration. Defaulted to "
                        . "'{$category}' -- please review and correct via Offense Types management if needed."
                );
            }

            $this->db->table('offense_types')->where('name', $name)->update(['category' => $category]);
        }

        // Any 'grave' row not covered above (shouldn't happen against the
        // seeded data, but guards against unexpected custom rows a school
        // may have added) — flag it and default to 'serious' rather than
        // leaving a value the narrowed ENUM below would reject outright.
        $stragglers = $this->db->table('offense_types')->where('category', 'grave')->get()->getResultArray();
        foreach ($stragglers as $straggler) {
            log_message(
                'warning',
                "Offense type '{$straggler['name']}' (id {$straggler['id']}) was still 'grave' with no defined "
                    . "reclassification rule. Defaulted to 'serious' -- please review and correct via Offense "
                    . 'Types management.'
            );
        }
        $this->db->table('offense_types')->where('category', 'grave')->update(['category' => 'serious']);

        $this->db->query(
            'UPDATE cases
             INNER JOIN offense_types ON offense_types.id = cases.offense_type_id
             SET cases.category = offense_types.category'
        );

        // Now that no row anywhere uses 'grave', narrow both ENUMs to the
        // final three-tier set.
        $this->narrowCategoryEnum('offense_types');
        $this->narrowCategoryEnum('cases');
    }

    public function down()
    {
        // Not reversible in a data-preserving way — the original
        // grave/minor split can't be reconstructed from minor/serious/
        // severe — so this only restores the old ENUM shape, not the
        // original values.
        $this->forge->modifyColumn('offense_types', [
            'category' => ['name' => 'category', 'type' => 'ENUM', 'constraint' => ['grave', 'minor']],
        ]);
        $this->forge->modifyColumn('cases', [
            'category' => ['name' => 'category', 'type' => 'ENUM', 'constraint' => ['grave', 'minor']],
        ]);
    }

    private function widenCategoryEnum(string $table): void
    {
        $this->forge->modifyColumn($table, [
            'category' => ['name' => 'category', 'type' => 'ENUM', 'constraint' => ['grave', 'minor', 'serious', 'severe']],
        ]);
    }

    private function narrowCategoryEnum(string $table): void
    {
        $this->forge->modifyColumn($table, [
            'category' => ['name' => 'category', 'type' => 'ENUM', 'constraint' => ['minor', 'serious', 'severe']],
        ]);
    }
}
