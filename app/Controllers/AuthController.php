<?php

namespace App\Controllers;

use App\Models\UserModel;

class AuthController extends BaseController
{
    protected $helpers = ['form', 'url'];

    public function login()
    {
        return view('auth/login');
    }

    public function attemptLogin()
    {
        $rules = [
            'username' => 'required',
            'password' => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput();
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $userModel = new UserModel();
        $user      = $userModel->findByUsername($username);

        if ($user === null || ! password_verify($password, $user['password_hash'])) {
            return redirect()->to('/login')
                ->withInput()
                ->with('error', 'Invalid username or password.');
        }

        $session = session();
        $session->regenerate();
        $session->set([
            'user_id'     => $user['id'],
            'full_name'   => $user['full_name'],
            'role'        => $user['role'],
            'isLoggedIn'  => true,
        ]);

        return redirect()->to('/dashboard')->with('message', 'Welcome back, ' . $user['full_name'] . '.');
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to('/login');
    }
}
