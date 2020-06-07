<?php
declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\{Constraint, ConstraintValidator};
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use App\Repository\UserRepository;

class UniqueUserValidator extends ConstraintValidator
{
    /** @var UserRepository */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function validate($object, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueUser) {
            throw new UnexpectedTypeException($constraint, UniqueUser::class);
        }

        $field = $constraint->field;

        if ($field === 'email') {
            $fieldName = 'e-mail adress';
        } else {
            $fieldName = $field;
        }

        $method = 'get';
        $method .= ucfirst($field);
        
        $array[$field] = $object->$method();

        //property cannot be null live it to notBlank assert
        if ($array[$field] === null || $array[$field] === '') {
            return;
        }
        
        $existingUser = $this->userRepository->findOneBy($array);

        if (!$existingUser || $existingUser->getId() === $object->getId()) {
            return;
        }

        /* @var $constraint \App\Validator\UniqueUser */
        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ fieldName }}', $fieldName)
            ->atPath($constraint->errorPath)
            ->addViolation();
    }
}
