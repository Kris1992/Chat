<?php declare(strict_types=1);

namespace App\Services\ModelValidator;

use App\Services\Factory\Message\MessageFactory;

/** 
 *  Choose group of validation
 */
class ModelValidatorChooser 
{
    public function chooseValidationGroup(string $type): array
    {   
        switch ($type) {
            case MessageFactory::CHAT_MESSAGE_FACTORY:
                return ['chat:message'];
            case MessageFactory::PETITION_MESSAGE_FACTORY:
                return ['petition:message'];
            default:
                return [];
        }
    }

}
