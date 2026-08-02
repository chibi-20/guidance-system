<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * The DepEd escalation matrix. Rows normally key off CATEGORY + a repeat-
 * offense tier (offense_number 1/2/3) rather than an individual offense
 * type, since the same escalation applies to every offense within a level.
 * offense_type_id is nullable and exists only so a specific offense type
 * can later get its own override row if ever needed — none are seeded
 * today. One row (offense_type_id, category, and offense_number all NULL)
 * holds the single general informational note shown on the Offense Matrix
 * page, not tied to any tier.
 */
class CreateOffenseConsequenceMatrixTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'offense_type_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'category' => [
                'type'       => 'ENUM',
                'constraint' => ['minor', 'serious', 'severe'],
                'null'       => true,
            ],
            'offense_number' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'recommended_action' => [
                'type' => 'TEXT',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('offense_type_id');
        $this->forge->addKey(['category', 'offense_number']);

        $this->forge->addForeignKey('offense_type_id', 'offense_types', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('offense_consequence_matrix', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_general_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('offense_consequence_matrix', true);
    }
}
