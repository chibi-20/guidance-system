<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateParentCommunicationsTable extends Migration
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
            'method' => [
                'type'       => 'ENUM',
                'constraint' => ['messenger', 'text', 'phone', 'email', 'letter', 'conference'],
            ],
            'communicated_on' => [
                'type' => 'DATETIME',
            ],
            'communicated_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'acknowledged' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'acknowledged_on' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'remarks' => [
                'type' => 'TEXT',
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
        $this->forge->addKey('case_id');
        $this->forge->addKey('communicated_by');

        $this->forge->addForeignKey('case_id', 'cases', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('communicated_by', 'users', 'id', 'CASCADE', 'RESTRICT');

        $this->forge->createTable('parent_communications', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_general_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('parent_communications', true);
    }
}
