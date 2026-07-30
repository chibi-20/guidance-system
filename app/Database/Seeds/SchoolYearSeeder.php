<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SchoolYearSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $this->db->table('school_years')->insert([
            'name'       => '2026-2027',
            'start_date' => '2026-06-01',
            'end_date'   => '2027-03-31',
            'is_current' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
