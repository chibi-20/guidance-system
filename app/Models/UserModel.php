<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $deletedField     = 'deleted_at';
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    // password_hash is deliberately excluded here so ordinary insert()/update()
    // calls (e.g. from the profile edit form) can never write a raw password
    // over it by accident. Use setPassword() to change it.
    protected $allowedFields = [
        'full_name',
        'email',
        'username',
        'role',
        'is_active',
    ];

    /**
     * Returns the active user matching the given username, or null.
     */
    public function findByUsername(string $username): ?array
    {
        return $this->where('username', $username)
            ->where('is_active', 1)
            ->first();
    }

    /**
     * The only path that may write to password_hash: hashes $plainPassword
     * and saves it, temporarily allowing the field for just this call.
     */
    public function setPassword(int $userId, string $plainPassword): bool
    {
        $originalAllowedFields = $this->allowedFields;
        $this->setAllowedFields([...$originalAllowedFields, 'password_hash']);

        $result = $this->update($userId, [
            'password_hash' => password_hash($plainPassword, PASSWORD_DEFAULT),
        ]);

        $this->setAllowedFields($originalAllowedFields);

        return $result;
    }

    /**
     * Count of active admin accounts — used to block deactivating/demoting
     * the last one.
     */
    public function countAdmins(): int
    {
        return $this->where('role', 'admin')
            ->where('is_active', 1)
            ->countAllResults();
    }
}
