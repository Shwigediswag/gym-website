<?php

declare(strict_types=1);

namespace App\Shared\Doctrine;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;

/**
 * ULID primary key, generated in the constructor so ids exist before flush (HANDOFF §7).
 */
trait UlidIdTrait
{
    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME, unique: true)]
    private Ulid $id;

    public function getId(): Ulid
    {
        return $this->id;
    }

    private function initId(): void
    {
        $this->id = new Ulid();
    }
}
