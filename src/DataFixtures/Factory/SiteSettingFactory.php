<?php

declare(strict_types=1);

namespace App\DataFixtures\Factory;

use App\Settings\Entity\SiteSetting;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<SiteSetting>
 */
final class SiteSettingFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return SiteSetting::class;
    }

    /** @return array<string, mixed> */
    protected function defaults(): array
    {
        return [
            'key' => 'studio.city',
            'value' => '[STAD]',
        ];
    }
}
