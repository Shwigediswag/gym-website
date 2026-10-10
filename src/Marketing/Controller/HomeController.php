<?php

declare(strict_types=1);

namespace App\Marketing\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Foundation smoke page. Replaced by the block page builder in Phase 1.5/1.7.
 */
final class HomeController extends AbstractController
{
    #[Route(path: '/', name: 'marketing_home', methods: ['GET'])]
    public function home(): Response
    {
        return $this->render('marketing/home.html.twig');
    }
}
