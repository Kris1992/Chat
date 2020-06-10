<?php
declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Chat;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ChatFixtures extends BaseFixture implements DependentFixtureInterface
{

    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }

    protected function loadData(ObjectManager $manager)
    {

        $this->createMany(10, 'public_chats', function($i)
        {
            $chat = new Chat();
            $chat
                ->setTitle($this->faker->sentence(6))
                ->setIsPublic(true)
                ; 

            for($index = 0; $index < $this->faker->numberBetween(2, 10); $index++) {
                $chat
                    ->addUser($this->getReferenceByIndex('main_users', $index))
                ;
            }

            return $chat;
        });

        $this->createMany(3, 'private_chats', function($i) {
            $chat = new Chat();
            $chat
                ->setTitle($this->faker->sentence(6))
                ->setIsPublic(false)
                ; 

            for($index = 0; $index < $this->faker->numberBetween(2, 30); $index++) {
                $chat
                    ->addUser($this->getReferenceByIndex('main_users', $index))
                ;
            }
            
            return $chat;
        });


        $manager->flush();
    }

}
