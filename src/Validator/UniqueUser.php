<?php declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */

class UniqueUser extends Constraint
{
    
    /** @var string */
    public $errorPath;

    /** @var string */
    public $field;
	
	public function getRequiredOptions()
	{
		return ['field', 'errorPath'];
	}

	/**
	* {@inheritdoc}
	*/
	public function getTargets()
	{
		return self::CLASS_CONSTRAINT;
	}

    public $message = 'This {{ fieldName }} is already registered!';
}