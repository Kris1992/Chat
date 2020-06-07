<?php
declare(strict_types=1);

namespace App\Services\Checker\User;

use App\Services\Checker\CheckerInterface;
use App\Repository\UserRepository;

class UserUniqueChecker implements CheckerInterface 
{

    private $fields = ['email', 'login']; 

    /** @var UserRepository */
    private $userRepository;

    /**
     * UserUniqueChecker Constructor
     * 
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)  
    {
        $this->userRepository = $userRepository;
    }
    
    public function check($data): bool
    {
        //Security
        if(!in_array($data['fieldName'], $this->fields)) {
            throw new \Exception("You cannot ask about that data!");
        }

        $user = $this->userRepository->findOneBy([ $data['fieldName'] => $data['fieldValue'] ]);
        
        if($user) {
            return false;
        }

        return true;
    }
}




       

        

