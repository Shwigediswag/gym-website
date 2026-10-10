<?php

declare(strict_types=1);

namespace App\Settings\Entity;

use App\Settings\Repository\SiteSettingRepository;
use App\Shared\Doctrine\TimestampableTrait;
use App\Shared\Doctrine\UlidIdTrait;
use App\Shared\Locale;
use Doctrine\ORM\Mapping as ORM;

/**
 * Key/value site setting, translatable where needed (HANDOFF §7, §11).
 * Keys and their defaults are declared in SettingDefinitions.
 */
#[ORM\Entity(repositoryClass: SiteSettingRepository::class)]
#[ORM\Table(name: 'site_setting')]
#[ORM\HasLifecycleCallbacks]
class SiteSetting
{
    use UlidIdTrait;
    use TimestampableTrait;

    #[ORM\Column(name: 'setting_key', length: 100, unique: true)]
    private string $key;

    /** Non-translatable value. */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $value = null;

    /**
     * Translatable value, keyed by locale: {"nl": "...", "en": "..."}.
     *
     * @var array<string, string|null>|null
     */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $translations = null;

    public function __construct(string $key)
    {
        $this->initId();
        $this->key = $key;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): static
    {
        $this->value = $value;

        return $this;
    }

    /** @return array<string, string|null>|null */
    public function getTranslations(): ?array
    {
        return $this->translations;
    }

    public function getTranslation(string $locale): ?string
    {
        return $this->translations[$locale] ?? null;
    }

    public function setTranslation(string $locale, ?string $value): static
    {
        $this->translations ??= [];
        $this->translations[Locale::normalize($locale)] = $value;

        return $this;
    }

    public function isTranslatable(): bool
    {
        return null !== $this->translations;
    }

    /**
     * Resolve the value for a locale, falling back to the default locale, then the plain value.
     */
    public function resolve(string $locale, string $fallbackLocale = Locale::DEFAULT): ?string
    {
        if ($this->isTranslatable()) {
            $v = $this->translations[$locale] ?? null;
            if (null !== $v && '' !== $v) {
                return $v;
            }

            return $this->translations[$fallbackLocale] ?? $this->value;
        }

        return $this->value;
    }
}
