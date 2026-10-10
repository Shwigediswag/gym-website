<?php

declare(strict_types=1);

namespace App\Settings;

use App\Settings\Entity\SiteSetting;
use App\Settings\Repository\SiteSettingRepository;
use App\Shared\Locale;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Read side for site settings with a single cached load. Writes go through set()/sync() which invalidate the cache.
 */
final class SiteSettings
{
    private const CACHE_KEY = 'site_settings.all';

    /** @var array<string, array{value: string|null, translations: array<string, string|null>|null}>|null */
    private ?array $loaded = null;

    public function __construct(
        private readonly SiteSettingRepository $repository,
        private readonly EntityManagerInterface $em,
        private readonly CacheInterface $cache,
        private readonly string $defaultLocale = Locale::DEFAULT,
    ) {
    }

    public function get(string $key, ?string $locale = null): ?string
    {
        $row = $this->load()[$key] ?? null;
        $locale = Locale::normalize($locale ?? $this->defaultLocale);

        if (null === $row) {
            return SettingDefinitions::all()[$key]['default'] ?? null;
        }

        if (null !== $row['translations']) {
            $v = $row['translations'][$locale] ?? null;
            if (null !== $v && '' !== $v) {
                return $v;
            }

            return $row['translations'][$this->defaultLocale] ?? $row['value'];
        }

        return $row['value'];
    }

    public function getInt(string $key, int $default = 0): int
    {
        $v = $this->get($key);

        return null === $v || !is_numeric($v) ? $default : (int) $v;
    }

    public function has(string $key): bool
    {
        $v = $this->get($key);

        return null !== $v && '' !== $v;
    }

    /** True when the stored value is still a [PLACEHOLDER] from the handoff. */
    public function isPlaceholder(string $key, ?string $locale = null): bool
    {
        $v = $this->get($key, $locale);

        return null === $v || '' === $v || 1 === preg_match('/^\[.+\]$/', trim($v));
    }

    /**
     * Set a value (plain or for one locale) and persist it.
     */
    public function set(string $key, ?string $value, ?string $locale = null): SiteSetting
    {
        if (!SettingDefinitions::has($key)) {
            throw new \InvalidArgumentException(sprintf('Unknown site setting "%s".', $key));
        }

        $setting = $this->repository->findOneByKey($key) ?? new SiteSetting($key);
        $definition = SettingDefinitions::all()[$key];

        if ($definition['translatable']) {
            $setting->setTranslation($locale ?? $this->defaultLocale, $value);
        } else {
            $setting->setValue($value);
        }

        $this->em->persist($setting);
        $this->em->flush();
        $this->invalidate();

        return $setting;
    }

    /**
     * Insert every defined key that does not exist yet with its placeholder default. Safe to run repeatedly.
     *
     * @return int number of settings created
     */
    public function sync(): int
    {
        $existing = $this->repository->findAllIndexed();
        $created = 0;

        foreach (SettingDefinitions::all() as $key => $definition) {
            if (isset($existing[$key])) {
                continue;
            }
            $setting = new SiteSetting($key);
            if ($definition['translatable']) {
                foreach (Locale::SUPPORTED as $locale) {
                    $setting->setTranslation($locale, $definition['default']);
                }
            } else {
                $setting->setValue($definition['default']);
            }
            $this->em->persist($setting);
            ++$created;
        }

        if ($created > 0) {
            $this->em->flush();
            $this->invalidate();
        }

        return $created;
    }

    public function invalidate(): void
    {
        $this->loaded = null;
        $this->cache->delete(self::CACHE_KEY);
    }

    /** @return array<string, array{value: string|null, translations: array<string, string|null>|null}> */
    private function load(): array
    {
        return $this->loaded ??= $this->cache->get(self::CACHE_KEY, function (ItemInterface $item): array {
            $item->expiresAfter(3600);
            $rows = [];
            foreach ($this->repository->findAllIndexed() as $key => $setting) {
                $rows[$key] = ['value' => $setting->getValue(), 'translations' => $setting->getTranslations()];
            }

            return $rows;
        });
    }
}
