<?php declare(strict_types=1);

namespace App\Services\Factory\Petition;

use Symfony\Component\HttpFoundation\File\File;
use App\Model\Petition\PetitionModel;
use App\Entity\{Petition, User};

/**
 *  Take care about creating petitions
 */
interface PetitionFactoryInterface
{   

    /**
     * create Create petition
     * @param   PetitionModel       $petitionModel      Model with petition data
     * @param   User                $petitioner         User object whose is the owner of petition
     * @return  Petition                                Return petition object
     */
    public function create(PetitionModel $petitionModel, User $petitioner): Petition;

}
