<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Helpers\CsrfHelper;
use App\Helpers\ValidationHelper;
use App\Services\AuthService;

class AuthController extends Controller
{
    private AuthService $auth;

    public function __construct()
    {
        $this->auth = new AuthService();
    }

    /**
     * GET /login
     */
    public function showLoginForm(): void
    {
        // Sudah login -> langsung ke dashboard
        if ($this->auth->check()) {
            $this->redirect(
                AuthService::dashboardPath($this->auth->role())
            );
        }

        $this->render('auth/login', [
            'title' => 'Login',
        ]);
    }

    /**
     * POST /login
     */
    public function login(): void
    {
        if (!CsrfHelper::verify(Request::post('_csrf'))) {
            throw new \Exception('Token keamanan tidak valid.', 403);
        }

        $email    = trim((string) Request::post('email', ''));
        $password = (string) Request::post('password', '');

        $errors = ValidationHelper::validate(
            ['email' => $email, 'password' => $password],
            [
                'email'    => ['required', 'email', 'max:100'],
                'password' => ['required'],
            ],
            ['email' => 'Email', 'password' => 'Password']
        );

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('old', ['email' => $email]);
            $this->redirect('/login');
        }

        $result = $this->auth->attemptLogin($email, $password);

        if (!$result['success']) {
            Session::flash('error', $result['message']);
            Session::flash('old', ['email' => $email]);
            $this->redirect('/login');
        }

        $this->redirect(
            AuthService::dashboardPath($result['role'])
        );
    }

    /**
     * POST /logout
     */
    public function logout(): void
    {
        if (!CsrfHelper::verify(Request::post('_csrf'))) {
            throw new \Exception('Token keamanan tidak valid.', 403);
        }

        $this->auth->logout();

        Session::flash('success', 'Anda berhasil logout.');

        $this->redirect('/login');
    }
}