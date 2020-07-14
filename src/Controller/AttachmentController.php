<?php declare(strict_types=1);

namespace App\Controller;

use App\Services\JsonErrorResponse\{JsonErrorResponseFactory, JsonErrorResponseTypes};
use Symfony\Component\HttpFoundation\{Response, JsonResponse, Request};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Services\AttachmentManager\AttachmentManagerInterface;
use App\Exception\Api\ApiBadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Hateoas\HateoasBuilder;

/**
* @IsGranted("ROLE_USER")
**/
class AttachmentController extends AbstractController
{
    /**
     * @Route("/api/attachment/image", name="api_upload_image_attachment", methods={"POST"})
     */
    public function uploadImage(Request $request, AttachmentManagerInterface $attachmentManager, JsonErrorResponseFactory $jsonErrorFactory, EntityManagerInterface $entityManager): Response
    {

        /** @var User $user */
        $user = $this->getUser();
        $imageFile = $request->files->get('uploadImage');

        if (!$imageFile) {
            throw new ApiBadRequestHttpException('Invalid JSON.');
        }
        
        try {
            $attachment = $attachmentManager->create($user, null, $imageFile, 'Image');
        } catch (\Exception $e) {

            return $jsonErrorFactory->createResponse(404, JsonErrorResponseTypes::TYPE_ACTION_FAILED, null, $e->getMessage());
        }
        
        $entityManager->persist($attachment);
        $entityManager->flush();

        $hateoasBuilder = HateoasBuilder::create()->build();
        $serializedAttachment = $hateoasBuilder->serialize($attachment, 'json', SerializationContext::create()->setGroups(['attachment:show']));
        
        return new JsonResponse($serializedAttachment, Response::HTTP_OK, ['content-type' => 'application/hal+json']);
        
    }
}
