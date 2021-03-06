<?php declare(strict_types=1);

namespace App\Controller;

use App\Services\JsonErrorResponse\{JsonErrorResponseFactory, JsonErrorResponseTypes};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Services\Factory\ChatModel\ChatModelFactoryInterface;
use Symfony\Component\HttpFoundation\{Response, JsonResponse, Request};
use App\Services\ImagesManager\ImagesManagerInterface;
use App\Services\Factory\Chat\ChatFactoryInterface;
use App\Services\Updater\Chat\ChatUpdaterInterface;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ChatRepository;
use App\Form\ChatFormType;
use App\Entity\Chat;

/**
* @IsGranted("ROLE_ADMIN")
**/
class AdminChatController extends AbstractController
{
    
    /**
     * @param   ChatRepository      $chatRepository
     * @param   PaginatorInterface  $paginator
     * @param   Request             $request
     * @return  Response
     * @Route("/admin/chat", name="admin_chat", methods={"GET"})
     */
    public function list(ChatRepository $chatRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $searchTerms = $request->query->getAlnum('filterValue');
        $chatQuery = $chatRepository->findPublicChatsQuery($searchTerms);

        $pagination = $paginator->paginate(
            $chatQuery, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            $request->query->getInt('perPage', 5)/*limit per page*/
        );

        return $this->render('admin_chat/list.html.twig', [
            'pagination' => $pagination
        ]);
    }

    /**
     * @param   Request                 $request
     * @param   EntityManagerInterface  $entityManager
     * @param   ChatFactoryInterface    $chatFactory
     * @return  Response
     * @Route("/admin/chat/add", name="admin_chat_add", methods={"POST","GET"})
     */
    public function add(Request $request, EntityManagerInterface $entityManager, ChatFactoryInterface $chatFactory): Response
    {

        $form = $this->createForm(ChatFormType::class, null , [
            'is_admin' => true
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
                
            /** @var ChatModel $chatModel */
            $chatModel = $form->getData();

            /** @var User $user */
            $user = $this->getUser();
            $chat = $chatFactory->create($chatModel, $user, $form['imageFile']->getData());
            
            $entityManager->persist($chat);
            $entityManager->flush();
            $this->addFlash('success', 'Chat is created!');

            return $this->redirectToRoute('admin_chat');
        }

        return $this->render('admin_chat/add.html.twig', [
            'chatForm' => $form->createView()
        ]);
    }

    /**
     * @param   Chat                        $chat
     * @param   Request                     $request
     * @param   EntityManagerInterface      $entityManager
     * @param   ChatModelFactoryInterface   $chatModelFactory
     * @param   ChatUpdaterInterface        $chatUpdater
     * @return  Response
     * @Route("/admin/chat/{id}/edit", name="admin_chat_edit", methods={"POST","GET"})
     */
    public function edit(Chat $chat, Request $request, EntityManagerInterface $entityManager, ChatModelFactoryInterface $chatModelFactory, ChatUpdaterInterface $chatUpdater): Response
    {
        
        /** @var ChatModel $chatModel */
        $chatModel = $chatModelFactory->create($chat);
        $form = $this->createForm(ChatFormType::class, $chatModel , [
            'is_admin' => true
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $chat = $chatUpdater->update($chatModel, $chat, $form['imageFile']->getData());
            $entityManager->flush();
            $this->addFlash('success', 'Chat is updated!');

            return $this->redirectToRoute('admin_chat');
        }

        return $this->render('admin_chat/edit.html.twig', [
            'chatForm' => $form->createView()
        ]);
    }

    /**
     * @param   Chat                    $chat
     * @param   EntityManagerInterface  $entityManager
     * @return  Response
     * @Route("/admin/chat/{id}/delete", name="admin_chat_delete",  methods={"DELETE"})
     */
    public function delete(Chat $chat, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($chat);
        $entityManager->flush();

        $response = new Response();
        $this->addFlash('success','Chat was deleted!');
        $response->send();
        return $response;
    }

    /**
     * @param   Request                 $request
     * @param   EntityManagerInterface  $entityManager
     * @param   ChatRepository          $chatRepository
     * @return  Response
     * @Route("/admin/chat/delete_selected", name="admin_chat_delete_selected",  methods={"POST", "DELETE"})
     */
    public function deleteSelected(Request $request, EntityManagerInterface $entityManager, ChatRepository $chatRepository): Response
    {
        $submittedToken = $request->request->get('token');
        if($request->request->has('deleteId')) {
            if ($this->isCsrfTokenValid('delete_multiple', $submittedToken)) {

                /* Admin can delete only public chat rooms */
                $chats = $chatRepository->findAllPublicByIds($request->request->get('deleteId'));

                if($chats) {
                    foreach ($chats as $chat) {
                        $entityManager->remove($chat);
                    }

                    $entityManager->flush();

                    $this->addFlash('success','Chat rooms were deleted!');
                    return $this->redirectToRoute('admin_chat');
                }

            } else {
                $this->addFlash('danger','Wrong token.');
                return $this->redirectToRoute('admin_chat');
            }
        }

        $this->addFlash('warning','Nothing to delete.');
        return $this->redirectToRoute('admin_chat');
    }

     /**
     * @param   Request                     $request
     * @param   Chat                        $chat
     * @param   JsonErrorResponseFactory    $jsonErrorFactory
     * @param   ImagesManagerInterface      $attachmentImagesManager
     * @param   EntityManagerInterface      $entityManager
     * @return  Response
     * @throws  ApiBadRequestHttpException
     * @Route("/api/admin/chat/{id}/delete_image", name="api_admin_delete_chat_image",
     * methods={"DELETE"})
     */
    public function deleteChatImageAction(Request $request, Chat $chat, JsonErrorResponseFactory $jsonErrorFactory, ImagesManagerInterface $attachmentImagesManager, EntityManagerInterface $entityManager): Response
    {

        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            throw new ApiBadRequestHttpException('Invalid JSON.');    
        }
        
        //double check that everything is ok
        if($chat->getId() === intval($data['id'])) {
            $imageFilename = $chat->getImageFilename();
            if(!empty($imageFilename)) {
                $result = $attachmentImagesManager->deleteImage($imageFilename, $chat->getOwner()->getLogin());
                if ($result) {
                    $chat->setImageFilename(null);
                    $entityManager->flush();
                    return new JsonResponse(null, Response::HTTP_OK);    
                }
            }
        }

        return $jsonErrorFactory->createResponse(404, JsonErrorResponseTypes::TYPE_NOT_FOUND_ERROR, null, 'Image not found.');
    }

}
