<?php

declare(strict_types=1);

namespace App\Tests\Integration\Settings;

use App\DataFixtures\Factory\SiteSettingFactory;
use App\Settings\SettingDefinitions;
use App\Settings\SiteSettings;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class SiteSettingsTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    private function settings(): SiteSettings
    {
        $service = self::getContainer()->get(SiteSettings::class);
        $service->invalidate();

        return $service;
    }

    public function testFallsBackToDefinitionDefaultWhenNotStored(): void
    {
        self::assertSame('[STAD]', $this->settings()->get('studio.city'));
        self::assertSame(6, $this->settings()->getInt('booking.max_group_size'));
        self::assertTrue($this->settings()->isPlaceholder('studio.city'));
    }

    public function testStoredValueWins(): void
    {
        SiteSettingFactory::createOne(['key' => 'studio.city', 'value' => 'Utrecht']);

        self::assertSame('Utrecht', $this->settings()->get('studio.city'));
        self::assertFalse($this->settings()->isPlaceholder('studio.city'));
    }

    public function testTranslatableValueResolvesPerLocaleWithFallback(): void
    {
        $settings = $this->settings();
        $settings->set('studio.opening_hours', 'Ma–za 07:00–21:00', 'nl');

        self::assertSame('Ma–za 07:00–21:00', $settings->get('studio.opening_hours', 'nl'));
        self::assertSame('Ma–za 07:00–21:00', $settings->get('studio.opening_hours', 'en'), 'en falls back to nl');

        $settings->set('studio.opening_hours', 'Mon–Sat 7am–9pm', 'en');
        self::assertSame('Mon–Sat 7am–9pm', $settings->get('studio.opening_hours', 'en'));
    }

    public function testSyncCreatesEveryDefinedKeyOnce(): void
    {
        $settings = $this->settings();

        self::assertSame(\count(SettingDefinitions::keys()), $settings->sync());
        self::assertSame(0, $settings->sync(), 'second sync is a no-op');
    }

    public function testUnknownKeyCannotBeSet(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->settings()->set('nope.unknown', 'x');
    }
}
