<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\{Response, JsonResponse, Request, ResponseHeaderBag};
use App\Services\JsonErrorResponse\{JsonErrorResponseFactory, JsonErrorResponseTypes};
use App\Services\PetitionStatusChanger\PetitionStatusChangerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Services\Factory\Petition\PetitionFactoryInterface;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use App\Model\Petition\PetitionConstants;
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
            'pagination' => $pagination,
            'petitionTypes' => PetitionConstants::TYPES_DESC
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

            $petition = $petitionFactory->create($petitionModel, $this->getUser());

            $entityManager->persist($petition);
            $entityManager->flush();
            $this->addFlash('success', 'Petition was created!');
            
            return $this->redirectToRoute('support_petition');
        }

        return $this->render('petition/add.html.twig', [
            'petitionForm' => $form->createView(),
        ]);
    }

    /**
     * @param   Petition                    $petition
     * @return  Response
     * @Route("/petition/{id}", name="support_petition_show", methods={"GET"})
     */
    public function show(Petition $petition): Response
    {
        $this->denyAccessUnlessGranted('PETITION_WRITE', $petition);

        return $this->render('petition/show.html.twig', [
            'petition' => $petition,
        ]);
    }

    /**
     * @param   Petition                        $petition
     * @param   Request                         $request
     * @param   JsonErrorResponseFactory        $jsonErrorFactory
     * @param   EntityManagerInterface          $entityManager
     * @throws  ApiBadRequestHttpException
     * @return  Response
     * @Route("/api/petition/{id}/update", name="api_petition_update", methods={"POST"})
     */
    public function updatePetitionStatusAction(Petition $petition, Request $request, JsonErrorResponseFactory $jsonErrorFactory, PetitionStatusChangerInterface $petitionStatusChanger, EntityManagerInterface $entityManager): Response
    {

        $this->denyAccessUnlessGranted('PETITION_WRITE', $petition);

        $data = json_decode($request->getContent(), true);
        
        if ($data === null) {
            throw new ApiBadRequestHttpException('Invalid JSON.');    
        }
        
        try {
            $petition = $petitionStatusChanger->change(
                $this->getUser(),
                $petition,
                $data['status'],
                true
            );

            $entityManager->flush();
        } catch (\Exception $e) {
            return $jsonErrorFactory->createResponse(400, JsonErrorResponseTypes::TYPE_ACTION_FAILED, null, $e->getMessage());
        }
        
        return new JsonResponse(null, Response::HTTP_OK);
    }
}
