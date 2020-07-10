<?php
declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\{TwigFilter, TwigFunction};
use App\Repository\FriendRepository;
use App\Entity\User;

class PendingFriendsExtension extends AbstractExtension
{   

    /** @var FriendRepository */
    private $friendRepository;
    
    public function __construct(FriendRepository $friendRepository)
    {
        $this->friendRepository = $friendRepository;
    }

    public function getFunctions(): Array
    {
        return [
            new TwigFunction(
                'pendingFriends',
                [$this, 'getPendingFriendsCount'],
                ['needs_environment' => false]
            ),
        ];
    }

    public function getPendingFriendsCount(User $currentUser): int
    {
        return $this->friendRepository->countInvitationsByUser($currentUser);
    }
}
