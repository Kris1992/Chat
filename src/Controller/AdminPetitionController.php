<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\{Response, JsonResponse, Request};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use App\Model\Petition\PetitionConstants;
use App\Repository\PetitionRepository;
use App\Entity\Petition;

/**
* @IsGranted("ROLE_ADMIN")
**/
class AdminPetitionController extends AbstractController
{

    /**
     * @param   PetitionRepository      $petitionRepository
     * @param   PaginatorInterface      $paginator
     * @param   Request                 $request
     * @return  Response
     * @Route("/admin/petition", name="admin_petition", methods={"GET"})
     */
    public function list(PetitionRepository $petitionRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $searchTerms = $request->query->getAlnum('searchValue');
        $petitionQuery = $petitionRepository->findAllQuery($searchTerms);

        $pagination = $paginator->paginate(
            $petitionQuery, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            $request->query->getInt('perPage', 5)/*limit per page*/
        );
        
        return $this->render('admin_petition/list.html.twig', [
            'pagination' => $pagination,
            'petitionTypes' => PetitionConstants::VALID_TYPES
        ]);
    }

    /**
     * @param   Petition      $petition
     * @return  Response
     * @Route("/admin/petition/{id}", name="admin_petition_show", methods={"GET", "POST"})
     */
    public function show(Petition $petition): Response
    {
        
        return $this->render('admin_petition/show.html.twig', [
            'petition' => $petition
        ]);
    }

}
