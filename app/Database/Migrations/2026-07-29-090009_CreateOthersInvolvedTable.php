<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOthersInvolvedTable extends Migration
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
            'involved_type' => [
                'type'       => 'ENUM',
                'constraint' => ['peer', 'staff', 'teacher', 'other'],
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'staff_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
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
        $this->forge->addKey('staff_id');

        $this->forge->addForeignKey('case_id', 'cases', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('staff_id', 'users', 'id', 'CASCADE', 'SET NULL');

        $this->forge->createTable('others_involved', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_general_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('others_involved', true);
    }
}
