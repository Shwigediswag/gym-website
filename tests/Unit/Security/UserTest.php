<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Security\Entity\User;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testAlwaysHasRoleUserAndDeduplicates(): void
    {
        $user = (new User())->setRoles([User::ROLE_ADMIN, User::ROLE_ADMIN, User::ROLE_USER]);

        self::assertSame([User::ROLE_ADMIN, User::ROLE_USER], $user->getRoles());
        self::assertTrue($user->hasRole(User::ROLE_USER));
    }

    public function testEmailIsNormalised(): void
    {
        $user = (new User())->setEmail('  Admin@Gym.TEST ');

        self::assertSame('admin@gym.test', $user->getEmail());
        self::assertSame('admin@gym.test', $user->getUserIdentifier());
    }

    public function testUnsupportedLocaleFallsBackToDutch(): void
    {
        self::assertSame('nl', (new User())->setLocale('fr')->getLocale());
        self::assertSame('en', (new User())->setLocale('en')->getLocale());
    }

    public function testIdIsGeneratedOnConstruction(): void
    {
        self::assertNotSame((string) (new User())->getId(), (string) (new User())->getId());
    }

    public function testEraseCredentialsDropsPlainPassword(): void
    {
        $user = (new User())->setPlainPassword('secret');
        $user->eraseCredentials();

        self::assertNull($user->getPlainPassword());
    }
}
