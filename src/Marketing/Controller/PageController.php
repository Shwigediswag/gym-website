<?php

declare(strict_types=1);

namespace App\Marketing\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * STATIC FRONT-END: the public pages rendered straight from Twig templates and translation files.
 * How these become editable content pages/blocks is decided after the static pass (PLANNING §1.5/1.7).
 *
 * Routes get the locale prefix from config/routes.yaml (nl at the root, en under /en), HANDOFF §9.
 */
final class PageController extends AbstractController
{
    #[Route(path: '/', name: 'marketing_home', methods: ['GET'])]
    public function home(): Response
    {
        return $this->render('marketing/home.html.twig');
    }

    #[Route(path: ['nl' => '/over-ons', 'en' => '/about'], name: 'marketing_about', methods: ['GET'])]
    public function about(): Response
    {
        return $this->render('marketing/about.html.twig');
    }

    #[Route(path: ['nl' => '/de-studio', 'en' => '/the-studio'], name: 'marketing_studio', methods: ['GET'])]
    public function studio(): Response
    {
        return $this->render('marketing/studio.html.twig');
    }

    #[Route(path: ['nl' => '/aanmelden', 'en' => '/sign-up'], name: 'intake_new', methods: ['GET', 'POST'])]
    public function signup(Request $request): Response
    {
        // Static pass: the submitted data is deliberately discarded. Storing an IntakeRequest,
        // honeypot, rate limit and mails follow in PLANNING §1.8.
        if ($request->isMethod('POST')) {
            return $this->redirectToRoute('intake_thanks', status: Response::HTTP_SEE_OTHER);
        }

        return $this->render('marketing/signup.html.twig');
    }

    #[Route(path: ['nl' => '/aanmelden/bedankt', 'en' => '/sign-up/thanks'], name: 'intake_thanks', methods: ['GET'])]
    public function thanks(): Response
    {
        return $this->render('marketing/signup_thanks.html.twig');
    }
}
