<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SectionSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $sections = [
            ['grade_level' => 10, 'name' => 'Locsin'],
            ['grade_level' => 10, 'name' => 'Leandro'],
            ['grade_level' => 10, 'name' => 'Mabini'],
            ['grade_level' => 10, 'name' => 'Aguinaldo'],
        ];

        foreach ($sections as &$section) {
            $section['created_at'] = $now;
            $section['updated_at'] = $now;
        }
        unset($section);

        $this->db->table('sections')->insertBatch($sections);
    }
}
