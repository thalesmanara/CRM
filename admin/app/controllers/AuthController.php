<?php

declare(strict_types=1);

namespace Revita\Crm\Controllers;

use Revita\Crm\Core\Auth;
use Revita\Crm\Core\Csrf;
use Revita\Crm\Core\Request;
use Revita\Crm\Core\Response;
use Revita\Crm\Core\Session;
use Revita\Crm\Core\View;
use Revita\Crm\Helpers\Url;
use Revita\Crm\Models\User;

final class AuthController
{
    private const MAX_ATTEMPTS = 8;

    private const LOCK_SECONDS = 120;

    public function root(Request $request): void
    {
        if (Auth::check()) {
            Url::redirect('/dashboard');
        }
        Url::redirect('/login');
    }

    public function showLogin(Request $request): void
    {
        if (Auth::check()) {
            Url::redirect('/dashboard');
        }
        $error = Session::flash('auth_error');
        $html = View::layout('guest', 'auth/login', [
            'title' => 'Entrar — Revita CRM',
            'csrfToken' => Csrf::token(),
            'error' => $error,
        ]);
        Response::html($html);
    }

    public function login(Request $request): void
    {
        if (!Csrf::validate((string) $request->post('_csrf'))) {
            Session::flash('auth_error', 'Sessão expirada. Tente novamente.');
            Url::redirect('/login');
        }

        if ($this->isLocked()) {
            Session::flash('auth_error', 'Muitas tentativas. Aguarde alguns minutos e tente de novo.');
            Url::redirect('/login');
        }

        $login = trim((string) $request->post('login', ''));
        $password = (string) $request->post('password', '');

        if ($login === '' || $password === '') {
            Session::flash('auth_error', 'Informe login e senha.');
            Url::redirect('/login');
        }

        $model = new User();
        $row = $model->findByLogin($login);
        if ($row === null || !(int) ($row['is_active'] ?? 0)) {
            $this->registerFailure();
            Session::flash('auth_error', 'Login ou senha inválidos.');
            Url::redirect('/login');
        }

        if (!password_verify($password, (string) $row['password_hash'])) {
            $this->registerFailure();
            Session::flash('auth_error', 'Login ou senha inválidos.');
            Url::redirect('/login');
        }

        $this->clearFailures();
        Auth::login([
            'id' => (int) $row['id'],
            'login' => (string) $row['login'],
            'level' => (int) $row['level'],
            'email' => (string) $row['email'],
        ]);
        Url::redirect('/dashboard');
    }

    public function logout(Request $request): void
    {
        Auth::logout();
        Url::redirect('/login');
    }

    private function isLocked(): bool
    {
        $until = (int) Session::get('auth_lock_until', 0);
        return $until > time();
    }

    private function registerFailure(): void
    {
        $n = (int) Session::get('auth_fail_count', 0) + 1;
        Session::set('auth_fail_count', $n);
        if ($n >= self::MAX_ATTEMPTS) {
            Session::set('auth_lock_until', time() + self::LOCK_SECONDS);
            Session::set('auth_fail_count', 0);
        }
    }

    private function clearFailures(): void
    {
        Session::remove('auth_fail_count');
        Session::remove('auth_lock_until');
    }
}
