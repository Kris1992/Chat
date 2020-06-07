<?php
declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\{Constraint, ConstraintValidator};
use Symfony\Component\Validator\Exception\{UnexpectedTypeException, UnexpectedValueException};

class ContainsAlphanumericValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ContainsAlphanumeric) {
            throw new UnexpectedTypeException($constraint, ContainsAlphanumeric::class);
        }

        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) take care of that
        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        preg_match_all('/[a-zA-Z]/', $value, $lettersMatches);
        preg_match_all('/[a-z]/', $value, $lowercaseMatches);
        preg_match_all('/[A-Z]/', $value, $uppercaseMatches);
        preg_match_all('/[0-9]/', $value, $numberMatches);

        if(
            sizeof($lettersMatches[0]) < 3 || 
            sizeof($lowercaseMatches[0]) < 1 || 
            sizeof($uppercaseMatches[0]) < 1 || 
            sizeof($numberMatches[0]) < 2
        ) {
            /* @var $constraint \App\Validator\ContainsAlphanumeric */
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }

    }
}
