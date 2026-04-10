<?php

namespace App\Core;

class Auth
{
    public static function user()
    {
        $session = new Session();
        return $session->auth ?? null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function role(): ?string
    {
        $user = self::user();
        return $user->role ?? null;
    }

    public static function logout(): void
    {
        $session = new Session();
        $session->unset('auth');
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            Message::warning("Você precisa fazer login para continuar.");
            redirect("/entrar");
            return;
        }

        SessionTimeoutMiddleware::handle();
    }

    public static function requireRole(string $role): void
    {
        self::requireLogin();

        if (self::role() !== $role) {
            Message::error("Você não tem autorização de acesso a essa página.");
            redirect("/entrar");
            return;
        }
    }
}