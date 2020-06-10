<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
* @IsGranted("ROLE_ADMIN")
**/
class AdminController extends AbstractController
{

    /**
     * @Route("/admin/dashboard", name="admin_dashboard", methods={"GET"}))
     */
    public function dashboard(): Response
    {
        return $this->render('admin/dashboard.html.twig', [
        ]);
    }
}
