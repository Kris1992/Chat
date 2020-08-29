<?php declare(strict_types=1);

namespace App\Services\Factory\Petition;

use Symfony\Component\HttpFoundation\File\File;
use App\Model\Petition\PetitionModel;
use App\Entity\{Petition, User};

class PetitionFactory implements PetitionFactoryInterface 
{

     public function create(PetitionModel $petitionModel, User $petitioner, ?File $uploadedFile): Petition
    {

        $petition = new Petition();
        $petition
            ->setTitle($petitionModel->getTitle())
            ->setDescription($petitionModel->getDescription())
            ->setType($petitionModel->getType())
            ->setPetitioner($petitioner)
            ->setIsOpened(true)
            ;
            
        return $petition;
    }

}
