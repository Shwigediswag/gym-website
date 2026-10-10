<?php

declare(strict_types=1);

namespace App\Security\Entity;

use App\Security\Repository\UserRepository;
use App\Shared\Doctrine\TimestampableTrait;
use App\Shared\Doctrine\UlidIdTrait;
use App\Shared\Locale;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Login account (HANDOFF §7). Linked 1:1 to Member or Trainer in later phases.
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'app_user')]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['email'], message: 'user.email.unique')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use UlidIdTrait;
    use TimestampableTrait;

    public const ROLE_USER = 'ROLE_USER';
    public const ROLE_MEMBER = 'ROLE_MEMBER';
    public const ROLE_TRAINER = 'ROLE_TRAINER';
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: 'user.email.blank')]
    #[Assert\Email(message: 'user.email.invalid')]
    private string $email = '';

    /** @var list<string> */
    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column]
    private string $password = '';

    /** Not persisted; hashed into $password by the caller (fixtures, commands, forms). */
    private ?string $plainPassword = null;

    #[ORM\Column(length: 2, options: ['default' => Locale::DEFAULT])]
    private string $locale = Locale::DEFAULT;

    #[ORM\Column(options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $lastLoginAt = null;

    public function __construct()
    {
        $this->initId();
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = mb_strtolower(trim($email));

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /** @return list<string> */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = self::ROLE_USER;

        return array_values(array_unique($roles));
    }

    /** @param list<string> $roles */
    public function setRoles(array $roles): static
    {
        $this->roles = array_values(array_unique(array_filter($roles, static fn (string $r) => $r !== self::ROLE_USER)));

        return $this;
    }

    public function addRole(string $role): static
    {
        return $this->setRoles([...$this->roles, $role]);
    }

    public function hasRole(string $role): bool
    {
        return \in_array($role, $this->getRoles(), true);
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $hashedPassword): static
    {
        $this->password = $hashedPassword;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): static
    {
        $this->locale = Locale::normalize($locale);

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getLastLoginAt(): ?\DateTimeImmutable
    {
        return $this->lastLoginAt;
    }

    public function recordLogin(): static
    {
        $this->lastLoginAt = new \DateTimeImmutable();

        return $this;
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }
}
