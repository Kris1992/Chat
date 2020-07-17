<?php declare(strict_types=1);

namespace App\Services\JsonErrorResponse;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Creates JsonErrorResponse object
 */
class JsonErrorResponseFactory
{

    public function createResponse(int $statusCode, string $type, $data = null, $customTitle = null): JsonResponse
    {

        //Data is more important if someone pass data and customTitle 
        if ($data) {
            
            if(is_array($data)){
                $jsonError = new JsonErrorResponse($statusCode, $type, null);
                $jsonError->setArrayExtraData($data);
            } else {
                $jsonError = new JsonErrorResponse(400, JsonErrorResponseTypes::TYPE_ACTION_FAILED, $data);
            }

        } else {
            $jsonError = new JsonErrorResponse($statusCode, $type, $customTitle);
        }

        $data = $jsonError->toArray();

        $response = new JsonResponse(
            $data,
            $jsonError->getStatusCode()
        );
        $response->headers->set('Content-Type', 'application/problem+json');

        return $response;
    }
}
