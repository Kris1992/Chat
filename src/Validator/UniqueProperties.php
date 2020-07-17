<?php declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class UniqueProperties extends Constraint
{
    
    /** @var string Path to show error property */
    public $errorPath;

    /** @var array Array with properties list which should be unique */
    public $fields = [];

    /** @var string Name of Entity */
    public $entityClass = null;
	
	public function getRequiredOptions()
	{
		return ['fields', 'entityClass', 'errorPath'];
	}

	/**
	* {@inheritdoc}
	*/
	public function getTargets()
	{  
        //get all properties of class
		return self::CLASS_CONSTRAINT;
	}

    public $message = 'Record in database with the same {{ fieldName[0] }} and {{ fieldName[1] }} already exist';
}
