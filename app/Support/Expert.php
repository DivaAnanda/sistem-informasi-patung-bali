<?php

namespace App\Support;

use App\Models\Statue;

final class Expert
{
    public const SESSION_KEY = 'expert';

    /**
     * Public pages are cached by the CDN for anonymous visitors, and the CDN cannot tell an informant's
     * cookie apart from anyone else's. Links in validation mode carry this query so they always reach the app.
     */
    public const BYPASS_QUERY = 'validasi';

    private const MIN_TOKEN_LENGTH = 16;

    public static function statueUrl(Statue $statue): string
    {
        return route('statues.show', ['statue' => $statue, self::BYPASS_QUERY => 1]);
    }

    /**
     * The session holds a fingerprint of the token it was opened with, so rotating
     * EXPERT_ACCESS_TOKEN immediately ends every existing expert session.
     */
    public static function active(): bool
    {
        $fingerprint = self::fingerprint(config('patung.expert_token'));

        return $fingerprint !== null && hash_equals($fingerprint, (string) session(self::SESSION_KEY));
    }

    public static function fingerprint(?string $token): ?string
    {
        return self::usable($token) ? hash('sha256', $token) : null;
    }

    /**
     * Constant-time check of a secret link token; a missing or too-short token never matches.
     */
    public static function tokenMatches(?string $expected, string $given): bool
    {
        return self::usable($expected) && hash_equals((string) $expected, $given);
    }

    private static function usable(?string $token): bool
    {
        return $token !== null && strlen($token) >= self::MIN_TOKEN_LENGTH;
    }
}
