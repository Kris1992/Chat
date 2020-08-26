<?php declare(strict_types=1);

namespace App\Controller;

use App\Services\JsonErrorResponse\{JsonErrorResponseFactory, JsonErrorResponseTypes};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Response, JsonResponse, Request};
use App\Exception\Api\ApiBadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use App\Services\ReportSystem\ReportSystemInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

class ReportController extends AbstractController
{

    /**
     * @param   User                            $reportedUser
     * @param   Request                         $request
     * @param   ReportSystemInterface           $reportSystem
     * @param   JsonErrorResponseFactory        $jsonErrorFactory
     * @param   EntityManagerInterface          $entityManager
     * @throws  ApiBadRequestHttpException
     * @return  Response
     * @Route("api/report/user/{id}", name="api_report_user", methods={"POST"})
     */
    public function reportUserAction(User $reportedUser, Request $request, ReportSystemInterface $reportSystem, JsonErrorResponseFactory $jsonErrorFactory, EntityManagerInterface $entityManager): Response
    {
        
        $data = json_decode($request->getContent(), true);
        
        if ($data === null) {
            throw new ApiBadRequestHttpException('Invalid JSON.');    
        }

        try {

            $report = $reportSystem->create($this->getUser(), $reportedUser, $data);
            $entityManager->persist($report);
            $entityManager->flush();

        } catch (\Exception $e) {
            return $jsonErrorFactory->createResponse(400, JsonErrorResponseTypes::TYPE_MODEL_VALIDATION_ERROR, null, $e->getMessage());
        }

        return new JsonResponse(null, Response::HTTP_OK);

    }
}
