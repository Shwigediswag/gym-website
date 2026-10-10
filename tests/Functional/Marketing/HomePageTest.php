<?php

declare(strict_types=1);

namespace App\Tests\Functional\Marketing;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class HomePageTest extends WebTestCase
{
    use Factories;
    use ResetDatabase;

    public function testDutchHomeIsServedAtRoot(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('html[lang="nl"]');
        self::assertSelectorTextContains('h1', 'Op afspraak. Op naam.');
        self::assertSelectorExists('main#main');
    }

    public function testEnglishHomeIsServedUnderEnPrefix(): void
    {
        $client = static::createClient();
        $client->request('GET', '/en');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('html[lang="en"]');
        self::assertSelectorTextContains('h1', 'By appointment. By name.');
    }

    public function testUnsupportedLocaleIsNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', '/fr');

        self::assertResponseStatusCodeSame(404);
    }

    public function testPlaceholderSettingsRenderUntilFilledIn(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        // No settings seeded: the [PLACEHOLDER] defaults from SettingDefinitions must show, never invented values.
        self::assertSelectorTextContains('footer', '[Adres]');
    }
}
