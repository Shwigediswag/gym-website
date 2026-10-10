<?php

declare(strict_types=1);

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    /**
     * The same checkout is served by the Linux php container and, optionally, by Windows PHP (Herd).
     * The compiled container embeds absolute paths, so each OS gets its own cache dir: var/cache/<os>/<env>.
     */
    public function getCacheDir(): string
    {
        return $this->getProjectDir().'/var/cache/'.strtolower(\PHP_OS_FAMILY).'/'.$this->environment;
    }
}
