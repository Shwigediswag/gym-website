<?php

declare(strict_types=1);

namespace App\Shared\Doctrine;

use Doctrine\ORM\Mapping as ORM;

/**
 * createdAt / updatedAt for every entity (HANDOFF §7).
 * The using entity must carry #[ORM\HasLifecycleCallbacks].
 */
trait TimestampableTrait
{
    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt ??= new \DateTimeImmutable();
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt ??= $this->getCreatedAt();
    }

    #[ORM\PrePersist]
    public function initTimestamps(): void
    {
        $now = new \DateTimeImmutable();
        $this->createdAt ??= $now;
        $this->updatedAt = $now;
    }

    #[ORM\PreUpdate]
    public function touchUpdatedAt(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
