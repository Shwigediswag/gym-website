<?php

declare(strict_types=1);

namespace App\Settings\Repository;

use App\Settings\Entity\SiteSetting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SiteSetting>
 */
class SiteSettingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SiteSetting::class);
    }

    /** @return array<string, SiteSetting> keyed by setting key */
    public function findAllIndexed(): array
    {
        $indexed = [];
        foreach ($this->findBy([], ['key' => 'ASC']) as $setting) {
            $indexed[$setting->getKey()] = $setting;
        }

        return $indexed;
    }

    public function findOneByKey(string $key): ?SiteSetting
    {
        return $this->findOneBy(['key' => $key]);
    }
}
