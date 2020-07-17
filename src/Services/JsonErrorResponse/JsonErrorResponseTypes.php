<?php declare(strict_types=1);

namespace App\Services\JsonErrorResponse;

/**
 * Handler of all constants with types of JsonErrorResponse object
 */
class JsonErrorResponseTypes
{
    const TYPE_INVALID_REQUEST_BODY_FORMAT = 'invalid_body_format';//Exception
    const TYPE_MODEL_VALIDATION_ERROR = 'model_validation_error';
    const TYPE_FORM_VALIDATION_ERROR = 'form_validation_error';
    const TYPE_NOT_FOUND_ERROR = 'not_found_error';
    const TYPE_ACTION_FAILED = 'action_failed';
    const TYPE_CONFLICT_ERROR = 'conflict_error';//409 Request can't be done because of resource (e.g already exist) 
}
