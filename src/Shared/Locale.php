<?php

declare(strict_types=1);

namespace App\Shared;

/**
 * Supported UI locales: nl (default) and en (HANDOFF §2).
 */
final class Locale
{
    public const DEFAULT = 'nl';

    /** @var list<string> */
    public const SUPPORTED = ['nl', 'en'];

    public static function isSupported(?string $locale): bool
    {
        return \in_array($locale, self::SUPPORTED, true);
    }

    public static function normalize(?string $locale): string
    {
        return self::isSupported($locale) ? (string) $locale : self::DEFAULT;
    }
}
