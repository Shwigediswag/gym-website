<?php

declare(strict_types=1);

namespace App\Settings;

/**
 * Every site setting the app knows about, with its group, type and default.
 * Defaults are the [PLACEHOLDER] values from HANDOFF §11 — never invent real values here.
 *
 * type: text | textarea | phone | email | url | int | cents | percent
 * translatable: value differs per locale (nl/en)
 */
final class SettingDefinitions
{
    public const GROUP_STUDIO = 'studio';
    public const GROUP_PRICING = 'pricing';
    public const GROUP_BOOKING = 'booking';
    public const GROUP_LEGAL = 'legal';
    public const GROUP_INTAKE = 'intake';
    public const GROUP_SEO = 'seo';

    /**
     * @return array<string, array{group: string, type: string, translatable: bool, default: string|null, todo?: string}>
     */
    public static function all(): array
    {
        return [
            // ---- Studio (HANDOFF §4.1 footer, §4.3 practical strip) ----
            'studio.city' => ['group' => self::GROUP_STUDIO, 'type' => 'text', 'translatable' => false, 'default' => '[STAD]', 'todo' => 'City name'],
            'studio.address' => ['group' => self::GROUP_STUDIO, 'type' => 'textarea', 'translatable' => false, 'default' => '[Adres]', 'todo' => 'Street, postcode, city'],
            'studio.phone' => ['group' => self::GROUP_STUDIO, 'type' => 'phone', 'translatable' => false, 'default' => '[Telefoon]'],
            'studio.email' => ['group' => self::GROUP_STUDIO, 'type' => 'email', 'translatable' => false, 'default' => '[E-mail]'],
            'studio.instagram' => ['group' => self::GROUP_STUDIO, 'type' => 'url', 'translatable' => false, 'default' => '[Instagram-url]'],
            'studio.opening_hours' => ['group' => self::GROUP_STUDIO, 'type' => 'textarea', 'translatable' => true, 'default' => '[Trainingstijden]', 'todo' => 'Training hours per day'],

            // ---- Pricing ("vanaf" prices shown on the site, HANDOFF §4.1) ----
            'pricing.small_group_from' => ['group' => self::GROUP_PRICING, 'type' => 'cents', 'translatable' => false, 'default' => null, 'todo' => '[PRIJS] small group per month, in cents'],
            'pricing.one_on_one_from' => ['group' => self::GROUP_PRICING, 'type' => 'cents', 'translatable' => false, 'default' => null, 'todo' => '[PRIJS] 1-op-1 per session, in cents'],

            // ---- Booking rules (HANDOFF §8) ----
            'booking.max_group_size' => ['group' => self::GROUP_BOOKING, 'type' => 'int', 'translatable' => false, 'default' => '6', 'todo' => 'Confirm [6]'],
            'booking.cancel_window_hours' => ['group' => self::GROUP_BOOKING, 'type' => 'int', 'translatable' => false, 'default' => '24'],
            'booking.credit_expiry_days' => ['group' => self::GROUP_BOOKING, 'type' => 'int', 'translatable' => false, 'default' => '90', 'todo' => 'Confirm [90]'],
            'booking.waitlist_offer_hours' => ['group' => self::GROUP_BOOKING, 'type' => 'int', 'translatable' => false, 'default' => '2'],

            // ---- Legal (footer + invoices) ----
            'legal.company_name' => ['group' => self::GROUP_LEGAL, 'type' => 'text', 'translatable' => false, 'default' => 'Gym'],
            'legal.kvk' => ['group' => self::GROUP_LEGAL, 'type' => 'text', 'translatable' => false, 'default' => '[KvK-nummer]'],
            'legal.vat_number' => ['group' => self::GROUP_LEGAL, 'type' => 'text', 'translatable' => false, 'default' => '[Btw-nummer]'],
            'legal.vat_rate' => ['group' => self::GROUP_LEGAL, 'type' => 'percent', 'translatable' => false, 'default' => null, 'todo' => 'VAT percentage for invoices'],

            // ---- Intake ----
            'intake.notification_email' => ['group' => self::GROUP_INTAKE, 'type' => 'email', 'translatable' => false, 'default' => null, 'todo' => 'Falls back to INTAKE_NOTIFICATION_EMAIL env'],

            // ---- SEO defaults (per-page values live on Page) ----
            'seo.title_suffix' => ['group' => self::GROUP_SEO, 'type' => 'text', 'translatable' => false, 'default' => ' · Gym'],
            'seo.default_description' => ['group' => self::GROUP_SEO, 'type' => 'textarea', 'translatable' => true, 'default' => null, 'todo' => 'Default meta description nl/en'],
        ];
    }

    /** @return list<string> */
    public static function keys(): array
    {
        return array_keys(self::all());
    }

    public static function has(string $key): bool
    {
        return isset(self::all()[$key]);
    }
}
