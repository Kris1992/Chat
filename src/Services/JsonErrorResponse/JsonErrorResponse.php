<?php
declare(strict_types=1);

namespace App\Services\JsonErrorResponse;

/**
 * Handles api responses with json+problem header (To standarize errors response)
 */
class JsonErrorResponse
{

    static private $titles = [
        JsonErrorResponseTypes::TYPE_INVALID_REQUEST_BODY_FORMAT => 'Invalid JSON format sent',
        JsonErrorResponseTypes::TYPE_MODEL_VALIDATION_ERROR => 'There was a model validation error.',
        JsonErrorResponseTypes::TYPE_FORM_VALIDATION_ERROR => 'There was a form validation error.',
        JsonErrorResponseTypes::TYPE_NOT_FOUND_ERROR => 'Object not found.',
        JsonErrorResponseTypes::TYPE_ACTION_FAILED => 'Action failed.',
        JsonErrorResponseTypes::TYPE_CONFLICT_ERROR => 'Resource already exist.',
    ];

    /** @var integer */
    private $statusCode;

    /** @var string */
    private $type;

    /** @var string */
    private $title;

    /** @var array Array with extra data of error */
    private $extraData = [];

    public function __construct(int $statusCode, string $type, ?string $customTitle)
    {
        $this->statusCode = $statusCode;
        $this->type = $type;

        if ($customTitle) {
            $this->title = $customTitle;
        } else {
            if (!isset(self::$titles[$type])) {
                throw new \InvalidArgumentException('No title defined for type '.$type);
            }

            $this->title = self::$titles[$type];
        }
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function setSingleExtraData(string $name, string $value)
    {
        $this->extraData[$name] = $value;
    }

    public function setArrayExtraData(array $extraData)
    {
        $this->extraData = $extraData;
    }

    public function toArray()
    {
        return array_merge(
            [
                'invalid-params' => $this->extraData
            ],
            [
                'status' => $this->statusCode,
                'type' => $this->type,
                'title' => $this->title,
            ]
        );
    }   

}
