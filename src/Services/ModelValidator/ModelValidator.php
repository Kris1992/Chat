<?php declare(strict_types=1);

namespace App\Services\ModelValidator;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Form\{FormInterface, FormError};
use Symfony\Component\Validator\ConstraintViolationList;

/** 
 *  Validator for model data
 */
class ModelValidator implements ModelValidatorInterface
{

    /** @var ConstraintViolationList */
    private $formErrors;

    /** @var ValidatorInterface */
    private $validator;

    /** @var bool */
    private $isValid;

    /**
     * ChatModelFactory Constructor
     * 
     * @param ValidatorInterface $validator
     * 
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * isValid Check is model data valid
     * @param $dataModel Model data object which will be validated
     * @param $groups Validation groups [optional] 
     * @return bool
     */
    public function isValid($dataModel, $groups=null): bool
    {
        if ($groups === null) {
            $this->formErrors = $this->validator->validate($dataModel);
        } else {
            $this->formErrors = $this->validator->validate($dataModel, null, $groups);
        }

        if(count($this->formErrors) > 0) {
            return $this->isValid = false;
        }

        return $this->isValid = true;
    }

    /**
     * mapErrorsToForm Map Errors to given form 
     * @param  FormInterface $form Symfony form object
     * @return void
     */
    public function mapErrorsToForm(FormInterface $form): void
    {
        if (!$this->isValid) {
            foreach ($this->formErrors as $error) {
                $formError = new FormError($error->getMessage());
                $form->get($error->getPropertyPath())->addError($formError);
            }
        }
    }

    /**
    * getErrors Get all validated errors
    * @return ConstraintViolationList
    */
    public function getErrors(): ConstraintViolationList
    {
        if (!$this->isValid) {
            return $this->formErrors;
        }
    }

    /**
     * getErrorMessage Get first error from list
     * @return string
     */
    public function getErrorMessage(): string
    {
        if (!$this->isValid) {
            return $this->formErrors[0]->getMessage();
        }
    }

}
