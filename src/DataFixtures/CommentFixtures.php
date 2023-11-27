<?php

namespace App\DataFixtures;

use App\Entity\Cocktail;
use App\Entity\Comment;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\String\Slugger\AsciiSlugger;

class CommentFixtures extends Fixture
{

    public function getDependencies()
    {
        return [
            UserFixtures::class,
            CocktailFixtures::class
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        /** @var User[] $users */
        $users = $this->getReference(UserFixtures::USERS_REFERENCE)->users;
        /** @type Cocktail[] $cocktails */
        $cocktails = $this->getReference(CocktailFixtures::COCKTAILS_REFERENCE)->cocktails;

        foreach ($cocktails as $cocktail){
            for($i = 0; $i < random_int(1,10); $i++){
                $index = array_rand($users);
                $comment = new Comment();
                $comment->setContent($faker->paragraph());
                $comment->setCocktailId($cocktail);
                $comment->setUserId($users[$index]);
                $manager->persist($comment);
            }
        }
        $manager->flush();
    }
}
