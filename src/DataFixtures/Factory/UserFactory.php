<?php

declare(strict_types=1);

namespace App\DataFixtures\Factory;

use App\Security\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<User>
 */
final class UserFactory extends PersistentObjectFactory
{
    public function __construct(private readonly UserPasswordHasherInterface $hasher)
    {
        parent::__construct();
    }

    public static function class(): string
    {
        return User::class;
    }

    /** @return array<string, mixed> */
    protected function defaults(): array
    {
        return [
            'email' => self::faker()->unique()->safeEmail(),
            'plainPassword' => 'password',
            'roles' => [],
            'locale' => 'nl',
            'isActive' => true,
        ];
    }

    public function superadmin(): static
    {
        return $this->with(['roles' => [User::ROLE_SUPER_ADMIN]]);
    }

    public function admin(): static
    {
        return $this->with(['roles' => [User::ROLE_ADMIN]]);
    }

    public function member(): static
    {
        return $this->with(['roles' => [User::ROLE_MEMBER]]);
    }

    protected function initialize(): static
    {
        return $this->afterInstantiate(function (User $user): void {
            $plain = $user->getPlainPassword();
            if (null !== $plain && '' !== $plain) {
                $user->setPassword($this->hasher->hashPassword($user, $plain));
                $user->eraseCredentials();
            }
        });
    }
}
