<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        // Password: Admin@12345
        $this->db->table('users')->insert([
            'employee_no'   => null,
            'full_name'     => 'System Administrator',
            'email'         => 'admin@guidance.local',
            'username'      => 'admin',
            'password_hash' => password_hash('Admin@12345', PASSWORD_DEFAULT),
            'role'          => 'admin',
            'is_active'     => 1,
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);

        // Password: Officer@12345
        $this->db->table('users')->insert([
            'employee_no'   => 'EMP-0002',
            'full_name'     => 'Juan Dela Cruz',
            'email'         => 'discipline.officer@guidance.local',
            'username'      => 'discipline_officer',
            'password_hash' => password_hash('Officer@12345', PASSWORD_DEFAULT),
            'role'          => 'discipline_officer',
            'is_active'     => 1,
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);

        // Password: Guidance@12345
        $this->db->table('users')->insert([
            'employee_no'   => 'EMP-0003',
            'full_name'     => 'Maria Santos',
            'email'         => 'guidance.counselor@guidance.local',
            'username'      => 'guidance_counselor',
            'password_hash' => password_hash('Guidance@12345', PASSWORD_DEFAULT),
            'role'          => 'guidance',
            'is_active'     => 1,
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);
    }
}
