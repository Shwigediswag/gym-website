<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\DataFixtures\Factory\UserFactory;
use App\Settings\SiteSettings;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Dev/test baseline: every site setting with its [PLACEHOLDER] default + the superadmin from .env.
 */
final class AppFixtures extends Fixture
{
    public function __construct(
        private readonly SiteSettings $settings,
        #[Autowire('%app.superadmin_email%')] private readonly string $superadminEmail,
        #[Autowire('%app.superadmin_password%')] private readonly string $superadminPassword,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $this->settings->sync();

        UserFactory::new()->superadmin()->create([
            'email' => $this->superadminEmail,
            'plainPassword' => $this->superadminPassword,
        ]);
    }
}
