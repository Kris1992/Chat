<?php 
declare(strict_types=1);

namespace App\Services\ModelValidator;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\ConstraintViolationList;

/** 
 *  Validator for model data
 */
interface ModelValidatorInterface
{
    /**
     * isValid Check is model data valid
     * @param  $dataModel Model data object which will be validated
     * @return boolean
     */
    public function isValid($dataModel, $groups=null): bool;

    /**
     * mapErrorsToForm Map Errors to given form 
     * @param  FormInterface $form Symfony form object
     * @return void
     */
    public function mapErrorsToForm(FormInterface $form): void;

    /**
     * getErrors Get Errors to render without form binding
     * @return ConstraintViolationList
     */
    public function getErrors(): ConstraintViolationList;

    /**
     * getErrorMessage Get single Error message to render without form binding
     * @return string
     */
    public function getErrorMessage(): string;

}
