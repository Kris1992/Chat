<?php declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Exception\{UnexpectedTypeException, InvalidOptionsException};
use Symfony\Component\Validator\{Constraint, ConstraintValidator};
use Doctrine\ORM\EntityManagerInterface;

class UniquePropertiesValidator extends ConstraintValidator
{

    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * UniquePropertiesValidator Constructor
     * 
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function validate($object, Constraint $constraint)
    {

        if (!$constraint instanceof UniqueProperties) {
            throw new UnexpectedTypeException($constraint, UniqueProperties::class);
        }

        if (count($constraint->fields) < 2) {
            throw new InvalidOptionsException('Expected array got string', $constraint->fields);
        }
        
        foreach ($constraint->fields as $key => $field) {
            $method = 'get';
            $method .= ucfirst($field);
        
            $array[$field] = $object->$method();
            
            //property cannot be null live it to notBlank assert
            if (null === $array[$field] || '' === $array[$field]) {
                return;
            }
        }

        $repository = $this->entityManager->getRepository('App\Entity\\'.$constraint->entityClass);

        $fieldsPairExist = $repository->findOneBy($array);

        if (!$fieldsPairExist || $fieldsPairExist->getId() == $object->getId()) {
            return;
        }

        /* @var $constraint \App\Validator\UniqueProperties */
        $this->context->buildViolation($constraint->message)
            ->setParameters([
                '{{ fieldName[0] }}' => $constraint->fields[0],
                '{{ fieldName[1] }}' => $constraint->fields[1],
            ])
            ->atPath($constraint->errorPath)
            ->addViolation();
    }
}