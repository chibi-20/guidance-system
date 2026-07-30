<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCasesTable extends Migration
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
            'case_no' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'student_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'enrollment_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'offense_type_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'category' => [
                'type'       => 'ENUM',
                'constraint' => ['grave', 'minor'],
            ],
            'date_of_incident' => [
                'type' => 'DATE',
            ],
            'time_of_incident' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'location' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'incident_report' => [
                'type' => 'TEXT',
            ],
            'narrative' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'referred_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'adviser_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'offense_count_type' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1,
            ],
            'offense_count_overall' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['open', 'ongoing', 'resolved', 'escalated'],
                'default'    => 'open',
            ],
            'school_year_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'created_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('case_no');
        $this->forge->addKey('student_id');
        $this->forge->addKey('date_of_incident');
        $this->forge->addKey('enrollment_id');
        $this->forge->addKey('offense_type_id');
        $this->forge->addKey('referred_by');
        $this->forge->addKey('adviser_id');
        $this->forge->addKey('school_year_id');
        $this->forge->addKey('created_by');

        $this->forge->addForeignKey('student_id', 'students', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('enrollment_id', 'enrollments', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('offense_type_id', 'offense_types', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('referred_by', 'users', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('adviser_id', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('school_year_id', 'school_years', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'RESTRICT');

        $this->forge->createTable('cases', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_general_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('cases', true);
    }
}
