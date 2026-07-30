<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class UserController extends BaseController
{
    protected $helpers = ['form', 'url'];

    private const ROLES = ['admin', 'guidance', 'discipline_officer', 'adviser', 'principal'];

    public function index()
    {
        $userModel = new UserModel();

        return view('users/index', [
            'users' => $userModel->orderBy('full_name', 'asc')->findAll(),
        ]);
    }

    public function create()
    {
        return view('users/create', ['user' => []]);
    }

    public function store()
    {
        if (! $this->validate($this->buildValidationRules())) {
            return redirect()->back()->withInput();
        }

        $userModel    = new UserModel();
        $tempPassword = $this->generateTempPassword();

        $userId = $userModel->insert([
            'full_name' => $this->request->getPost('full_name'),
            'email'     => $this->request->getPost('email'),
            'username'  => $this->request->getPost('username'),
            'role'      => $this->request->getPost('role'),
            'is_active' => 1,
        ]);

        $userModel->setPassword($userId, $tempPassword);

        return redirect()->to('/users')->with(
            'message',
            'User account "' . $this->request->getPost('username') . '" created. Temporary password: '
                . $tempPassword . ' — share this with them now; it cannot be shown again.'
        );
    }

    public function edit($id)
    {
        $userModel = new UserModel();
        $user      = $userModel->find($id);

        if ($user === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('users/edit', ['user' => $user]);
    }

    public function update($id)
    {
        $userModel = new UserModel();
        $user      = $userModel->find($id);

        if ($user === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (! $this->validate($this->buildValidationRules((int) $id))) {
            return redirect()->back()->withInput();
        }

        $newRole = $this->request->getPost('role');

        if ($this->wouldRemoveLastActiveAdmin($user, $userModel, $user['role'] === 'admin' && $newRole !== 'admin')) {
            return redirect()->back()->withInput()->with('error', 'Cannot demote the last remaining active admin account.');
        }

        $userModel->update($id, [
            'full_name' => $this->request->getPost('full_name'),
            'email'     => $this->request->getPost('email'),
            'username'  => $this->request->getPost('username'),
            'role'      => $newRole,
        ]);

        return redirect()->to('/users')->with('message', 'User updated successfully.');
    }

    /**
     * Admin-only, separate from edit(): issues a brand-new temporary
     * password and shows it once in a flash message.
     */
    public function resetPassword($id)
    {
        $userModel = new UserModel();
        $user      = $userModel->find($id);

        if ($user === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $tempPassword = $this->generateTempPassword();
        $userModel->setPassword((int) $id, $tempPassword);

        return redirect()->to('/users')->with(
            'message',
            'Password reset for "' . $user['username'] . '". New temporary password: '
                . $tempPassword . ' — share this with them now; it cannot be shown again.'
        );
    }

    public function toggleActive($id)
    {
        $userModel = new UserModel();
        $user      = $userModel->find($id);

        if ($user === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $isCurrentlyActive = (bool) $user['is_active'];

        if ($isCurrentlyActive && $this->wouldRemoveLastActiveAdmin($user, $userModel, true)) {
            return redirect()->to('/users')->with('error', 'Cannot deactivate the last remaining active admin account.');
        }

        $userModel->update($id, ['is_active' => $isCurrentlyActive ? 0 : 1]);

        $message = $isCurrentlyActive ? 'User account deactivated.' : 'User account reactivated.';

        return redirect()->to('/users')->with('message', $message);
    }

    /**
     * True if $user is currently an active admin, $conditionForRemoval
     * holds (they're about to be deactivated or demoted), and they're the
     * only active admin left.
     */
    private function wouldRemoveLastActiveAdmin(array $user, UserModel $userModel, bool $conditionForRemoval): bool
    {
        return $conditionForRemoval
            && $user['role'] === 'admin'
            && (bool) $user['is_active']
            && $userModel->countAdmins() <= 1;
    }

    private function generateTempPassword(): string
    {
        // Avoids visually ambiguous characters (0/O, 1/l/I) since an admin
        // has to read this aloud or type it out to hand off to staff.
        $chars    = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
        $password = '';

        for ($i = 0; $i < 10; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $password;
    }

    private function buildValidationRules(?int $ignoreId = null): array
    {
        $ignoreSuffix = $ignoreId !== null ? ',id,' . $ignoreId : '';

        return [
            'full_name' => 'required|max_length[150]',
            'email'     => 'required|valid_email|max_length[150]|is_unique[users.email' . $ignoreSuffix . ']',
            'username'  => 'required|max_length[100]|is_unique[users.username' . $ignoreSuffix . ']',
            'role'      => 'required|in_list[' . implode(',', self::ROLES) . ']',
        ];
    }
}
