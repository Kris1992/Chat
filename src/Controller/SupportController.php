<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class SupportController extends AbstractController
{
    /**
     * @return Response
     * @Route("/support", name="support", methods={"GET"})
     */
    public function dashboard()
    {
        return $this->render('support/dashboard.html.twig', []);
    }

}
