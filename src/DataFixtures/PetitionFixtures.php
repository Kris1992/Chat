<?php declare(strict_types=1);

namespace App\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Model\Petition\PetitionConstants;
use App\Entity\Petition;

class PetitionFixtures extends BaseFixture implements DependentFixtureInterface
{

    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }

    protected function loadData(ObjectManager $manager)
    {

        $this->createMany(100, 'main_petitions', function($i)
        {
            $petition = new Petition();
            $petition
                ->setTitle($this->faker->sentence($nbWords = 3, $variableNbWords = true))
                ->setType($this->faker->randomElement(PetitionConstants::VALID_TYPES))
                ->setDescription($this->faker->text($maxNbChars = 200))
                ->setIsOpened($this->faker->boolean($chanceTrue = 30))
                ->setPetitioner($this->getRandomReference('main_users'))
                ; 

            return $petition;
        });

        $manager->flush();
    }

}
