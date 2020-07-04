<?php
declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Friend;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class FriendFixtures extends BaseFixture implements DependentFixtureInterface
{

    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }

    protected function loadData(ObjectManager $manager)
    {

        $this->createMany(10, 'main_friends', function($i)
        {
            $friend = new Friend();
            $friend
                ->setInviter($this->getReferenceByIndex('admin_users', 0))
                ->setInvitee($this->getReferenceByIndex('main_users', $i))
                ->setStatus('Accepted')
                ; 

            return $friend;
        });

        $manager->flush();
    }

}
