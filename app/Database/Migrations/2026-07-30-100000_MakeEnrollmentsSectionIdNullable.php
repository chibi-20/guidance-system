<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Student promotion needs to record a new enrollment row for a student even
 * when no section at the next grade level shares the same name (left
 * "Unassigned" for manual cleanup, rather than silently guessing/creating a
 * section). section_id was originally NOT NULL, which made that case
 * impossible to represent.
 */
class MakeEnrollmentsSectionIdNullable extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('enrollments', [
            'section_id' => [
                'name'       => 'section_id',
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('enrollments', [
            'section_id' => [
                'name'       => 'section_id',
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
        ]);
    }
}
