<?php declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use App\Exception\Api\ApiBadRequestHttpException;
use App\Services\JsonErrorResponse\{JsonErrorResponseFactory, JsonErrorResponseTypes};

/*
 ApiExceptionSubscriber will handle all api exceptions and transform it to JsonErrorResponse
 */
class ApiExceptionSubscriber implements EventSubscriberInterface
{

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        if ($exception instanceof ApiBadRequestHttpException) {

            $responseFactory = new JsonErrorResponseFactory();
            $response = $responseFactory->createResponse(
                $exception->getStatusCode(),
                JsonErrorResponseTypes::TYPE_INVALID_REQUEST_BODY_FORMAT,
                ['detail' => $exception->getMessage()]
            );

            $event->setResponse($response);
        }

        return;
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.exception' => 'onKernelException',
        ];
    }
}
