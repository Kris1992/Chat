<?php declare(strict_types=1);

namespace spec\App\Services\JsonErrorResponse;

use App\Services\JsonErrorResponse\{JsonErrorResponseFactory, JsonErrorResponseTypes};
use Symfony\Component\HttpFoundation\JsonResponse;
use PhpSpec\ObjectBehavior;

class JsonErrorResponseFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(JsonErrorResponseFactory::class);
    }

    function it_should_be_able_to_create_invalid_request_error()
    {
        $jsonErrorResponse = $this->createResponse(
            400, 
            JsonErrorResponseTypes::TYPE_INVALID_REQUEST_BODY_FORMAT
        );

        $jsonErrorResponse->shouldBeAnInstanceOf(JsonResponse::class);
        $jsonErrorResponse->getStatusCode()->shouldReturn(400);
        $jsonErrorResponse->getContent()->shouldBeString();

    }

}
