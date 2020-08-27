<?php declare(strict_types=1);

namespace App\Controller;

use App\Services\JsonErrorResponse\{JsonErrorResponseFactory, JsonErrorResponseTypes};
use App\Services\AttachmentUploadSystem\AttachmentUploadSystemInterface;
use Symfony\Component\HttpFoundation\{Response, JsonResponse, Request};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Exception\Api\ApiBadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use JMS\Serializer\SerializationContext;
use Hateoas\HateoasBuilder;

/**
* @IsGranted("ROLE_USER")
**/
class AttachmentController extends AbstractController
{

    /**
     * @param   string                              $type
     * @param   Request                             $request
     * @param   AttachmentUploadSystemInterface     $attachmentUploadSystem
     * @param   JsonErrorResponseFactory            $jsonErrorFactory
     * @return  Response
     * @throws  ApiBadRequestHttpException
     * @Route("/api/attachment/{type}", name="api_upload_attachment", methods={"POST"})
     */
    public function upload(string $type, Request $request, AttachmentUploadSystemInterface $attachmentUploadSystem, JsonErrorResponseFactory $jsonErrorFactory): Response
    {

        $submittedToken = $request->request->get('token');

        if (!$this->isCsrfTokenValid('upload', $submittedToken)) {
            return $jsonErrorFactory->createResponse(404, JsonErrorResponseTypes::TYPE_ACTION_FAILED, null, 'Wrong token.');
        }
        
        try {
            $attachment = $attachmentUploadSystem->upload($this->getUser(), null , $request, $type);
        } catch (\UnexpectedValueException $e) {
            return $jsonErrorFactory->createResponse(404, JsonErrorResponseTypes::TYPE_INVALID_REQUEST_BODY_FORMAT, null, $e->getMessage());
        } catch (ApiBadRequestHttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            return $jsonErrorFactory->createResponse(404, JsonErrorResponseTypes::TYPE_ACTION_FAILED, null, $e->getMessage());
        }

        $hateoasBuilder = HateoasBuilder::create()->build();
        $serializedAttachment = $hateoasBuilder->serialize($attachment, 'json', SerializationContext::create()->setGroups(['attachment:show']));
        
        return new JsonResponse($serializedAttachment, Response::HTTP_OK, ['content-type' => 'application/hal+json']);
    }

}
