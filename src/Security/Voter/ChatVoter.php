<?php declare(strict_types=1);

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Repository\ParticipantRepository;
use App\Entity\Chat;

class ChatVoter extends Voter
{
    /** @var ParticipantRepository */
    private $participantRepository;

    public function __construct(ParticipantRepository $participantRepository)
    {
        $this->participantRepository = $participantRepository;
    }

    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, ['CHAT_VIEW', 'CHAT_MANAGE'])
            && $subject instanceof Chat;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case 'CHAT_VIEW':
                $participant = $this->participantRepository->findParticipantByUserAndChat($user, $subject);
                
                if ($participant) {
                    return true;    
                }
            break;
            case 'CHAT_MANAGE':
                if ($user ===  $subject->getOwner()) {
                    return true;    
                }
            break;
        }

        return false;
    }
}
