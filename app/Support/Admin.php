<?php

namespace App\Support;

use Illuminate\Support\Str;

final class Admin
{
    public const SESSION_KEY = 'admin';

    public static function enabled(): bool
    {
        return filled(config('patung.admin.email')) && filled(config('patung.admin.password_hash'));
    }

    /**
     * The session stores a fingerprint of the configured credentials, so changing the password
     * (ADMIN_PASSWORD_HASH) signs out every open admin session at once.
     */
    public static function active(): bool
    {
        $fingerprint = self::fingerprint();

        return $fingerprint !== null && hash_equals($fingerprint, (string) session(self::SESSION_KEY));
    }

    public static function fingerprint(): ?string
    {
        return self::enabled()
            ? hash('sha256', config('patung.admin.email').'|'.config('patung.admin.password_hash'))
            : null;
    }

    public static function attempt(string $email, string $password): bool
    {
        if (! self::enabled()) {
            return false;
        }

        // Both checks always run so a wrong email and a wrong password take the same time.
        $emailMatches = hash_equals(Str::lower((string) config('patung.admin.email')), Str::lower(trim($email)));
        $passwordMatches = password_verify($password, (string) config('patung.admin.password_hash'));

        return $emailMatches && $passwordMatches;
    }
}
