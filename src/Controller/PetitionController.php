<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\{Response, JsonResponse, Request, ResponseHeaderBag};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Services\Factory\Petition\PetitionFactoryInterface;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PetitionRepository;
use App\Form\PetitionFormType;
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

        $searchTerms = $request->query->getAlnum('filterValue');
        $petitionQuery = $petitionRepository->findAllByUserQuery($user, $searchTerms);

        $pagination = $paginator->paginate(
            $petitionQuery, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            $request->query->getInt('perPage', 6)/*limit per page*/
        );
        
        return $this->render('petition/list.html.twig', [
            'pagination' => $pagination
        ]);
    }

    /**
     * @param   Request                     $request
     * @param   EntityManagerInterface      $entityManager
     * @param   PetitionFactoryInterface    $petitionFactory
     * @return  Response
     * @Route("/petition/add", name="support_petition_add", methods={"POST", "GET"})
     */
    public function add(Request $request, EntityManagerInterface $entityManager, PetitionFactoryInterface $petitionFactory): Response
    {
        $form = $this->createForm(PetitionFormType::class);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $petitionModel = $form->getData();

            $petition = $petitionFactory->create($petitionModel, $this->getUser(), null);
                //$form['attachmentFiles']->getData());

            $entityManager->persist($petition);
            $entityManager->flush();
            $this->addFlash('success', 'Petition was created!');
            
            return $this->redirectToRoute('support_petition');
        }

        return $this->render('petition/add.html.twig', [
            'petitionForm' => $form->createView(),
        ]);
    }
}
