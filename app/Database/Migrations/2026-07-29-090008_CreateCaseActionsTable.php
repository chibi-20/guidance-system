<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCaseActionsTable extends Migration
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
            'case_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'action_prior' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'perceived_motivation' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'disciplinary_action' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'parents_notified_thru' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'conference_with' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'referred_to' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'behavior_contract' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'exclusion_transfer' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'remarks' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'resolved_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'resolved_at' => [
                'type' => 'DATETIME',
                'null' => true,
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
        $this->forge->addUniqueKey('case_id');
        $this->forge->addKey('resolved_by');

        $this->forge->addForeignKey('case_id', 'cases', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('resolved_by', 'users', 'id', 'CASCADE', 'SET NULL');

        $this->forge->createTable('case_actions', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_general_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('case_actions', true);
    }
}
