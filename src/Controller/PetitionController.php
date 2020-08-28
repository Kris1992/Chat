<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\{Response, JsonResponse, Request, ResponseHeaderBag};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\PetitionRepository;
use App\Entity\Petition;

/**
* @IsGranted("ROLE_USER")
**/
class PetitionController extends AbstractController
{

    /**
     * @param   PetitionRepository      $petitionRepository
     * @param   PaginatorInterface      $paginator
     * @param   Request                 $request
     * @return  Response
     * @Route("/petition", name="support_petition", methods={"GET"})
     */
    public function list(PetitionRepository $petitionRepository, PaginatorInterface $paginator, Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $searchTerms = $request->query->getAlnum('searchValue');
        $petitionQuery = $petitionRepository->findAllByUserQuery($user, $searchTerms);

        $pagination = $paginator->paginate(
            $petitionQuery, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            $request->query->getInt('perPage', 5)/*limit per page*/
        );
        
        return $this->render('petition/list.html.twig', [
            'pagination' => $pagination
        ]);
    }
}
