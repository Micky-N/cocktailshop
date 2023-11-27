<?php

namespace App\DataFixtures;

use App\Entity\Cocktail;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

class UserFixtures extends Fixture
{
    public const USERS_REFERENCE = 'users';

    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        $users = [];
        $adminUser = new User();
        $adminUser->setFirstname('Mickael');
        $adminUser->setLastname('Ndinga');
        $adminUser->setEmail('micky@cocktail.com');
        $adminUser->setPassword($this->hasher->hashPassword($adminUser, 'password'));
        $adminUser->setRoles('ROLE_ADMIN');
        $manager->persist($adminUser);

        $users[] = $adminUser;

        for($i = 0; $i < 3; $i++){
            $user = new User();
            $user->setFirstname($faker->firstName);
            $user->setLastname($faker->lastName);
            $user->setEmail($faker->email);
            $user->setPassword($this->hasher->hashPassword($user, 'password'));
            $user->setRoles($i <= 1 ? 'ROLE_VIP' : '');
            $manager->persist($user);
            $users[] = $user;
        }
        $manager->flush();

        $users = (object) ['users' => $users];
        $this->addReference(self::USERS_REFERENCE, $users);
    }
}
