<?php

declare(strict_types=1);

namespace App\Portal\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * STATIC FRONT-END: the member portal booking screen with the sample data from the design reference.
 * Publicly reachable for now; it goes behind ROLE_MEMBER once login exists (see config/packages/security.yaml).
 */
final class BookingController extends AbstractController
{
    #[Route(path: ['nl' => '/mijn/boeken', 'en' => '/my/book'], name: 'portal_booking', methods: ['GET'])]
    public function booking(): Response
    {
        return $this->render('portal/booking.html.twig');
    }
}
