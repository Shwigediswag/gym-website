<?php

declare(strict_types=1);

namespace App\Settings\Twig;

use App\Settings\SiteSettings;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * {{ setting('studio.phone') }} / {{ setting('studio.opening_hours', app.request.locale) }}
 */
final class SettingsExtension extends AbstractExtension
{
    public function __construct(private readonly SiteSettings $settings)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('setting', $this->settings->get(...)),
            new TwigFunction('setting_is_placeholder', $this->settings->isPlaceholder(...)),
        ];
    }
}
