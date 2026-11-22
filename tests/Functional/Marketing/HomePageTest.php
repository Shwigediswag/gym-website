<?php

declare(strict_types=1);

namespace App\Tests\Functional\Marketing;

use PHPUnit\Framework\Attributes\DataProvider;
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
        self::assertSelectorTextContains('#trainen', '[PRIJS]');
    }

    public function testHomeHasAllSectionsInReferenceOrder(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $ids = $crawler->filter('main > section[id]')->each(static fn ($node) => $node->attr('id'));
        self::assertSame(['top', 'verhaal', 'trainen', 'team', 'ervaringen'], $ids);
        self::assertSelectorTextContains('main', 'Kom eerst een kop koffie drinken.');
        self::assertSelectorExists('[data-controller="slider"]');
        self::assertSelectorExists('[data-controller="sticky-cta"]');
    }

    /**
     * @return iterable<string, array{string, string, string}>
     */
    public static function pages(): iterable
    {
        yield 'about nl' => ['/over-ons', 'nl', 'Je trainer kent je, en je rechterknie.'];
        yield 'about en' => ['/en/about', 'en', 'Your trainer knows you, and your right knee.'];
        yield 'studio nl' => ['/de-studio', 'nl', 'Kijk even binnen.'];
        yield 'studio en' => ['/en/the-studio', 'en', 'Take a look inside.'];
        yield 'signup nl' => ['/aanmelden', 'nl', 'Vertel ons wie je bent. Wij regelen de rest.'];
        yield 'signup en' => ['/en/sign-up', 'en', 'Tell us who you are. We take care of the rest.'];
        yield 'thanks nl' => ['/aanmelden/bedankt', 'nl', 'We bellen je binnen één werkdag.'];
        yield 'thanks en' => ['/en/sign-up/thanks', 'en', 'We will call you within one working day.'];
        yield 'portal nl' => ['/mijn/boeken', 'nl', 'Boeken'];
        yield 'portal en' => ['/en/my/book', 'en', 'Book'];
    }

    #[DataProvider('pages')]
    public function testStaticPageRendersInLocale(string $url, string $locale, string $heading): void
    {
        $client = static::createClient();
        $client->request('GET', $url);

        self::assertResponseIsSuccessful();
        self::assertSelectorExists(sprintf('html[lang="%s"]', $locale));
        self::assertSelectorTextSame('h1', $heading);
        self::assertSelectorCount(1, 'h1');
    }

    public function testLocaleSwitchPointsToTheSamePageInTheOtherLocale(): void
    {
        $client = static::createClient();
        $client->request('GET', '/over-ons');

        self::assertSelectorExists('footer a[hreflang="en"][href="/en/about"]');
        self::assertSelectorExists('footer a[hreflang="nl"][href="/over-ons"]');
    }

    public function testSignupHasNoStickyCtaAndPostsToThanks(): void
    {
        $client = static::createClient();
        $client->request('GET', '/aanmelden');

        self::assertSelectorNotExists('[data-controller="sticky-cta"]');
        self::assertSelectorExists('form#intake-form[method="post"]');
        self::assertSelectorCount(18, 'form#intake-form input[name^="availability"]');
        self::assertSelectorCount(1, 'form#intake-form input[name^="availability"][disabled]');

        // Static pass: POST is accepted, discarded and redirected to the thank-you page.
        $client->request('POST', '/aanmelden', ['first_name' => 'Test']);
        self::assertResponseRedirects('/aanmelden/bedankt', 303);
    }

    public function testStudioGalleryHasFiltersAndTenPhotos(): void
    {
        $client = static::createClient();
        $client->request('GET', '/de-studio');

        self::assertSelectorCount(5, '[data-gallery-target="filter"]');
        self::assertSelectorCount(10, '[data-gallery-target="item"]');
        self::assertSelectorExists('dialog[data-lightbox-target="dialog"]');
    }
}
