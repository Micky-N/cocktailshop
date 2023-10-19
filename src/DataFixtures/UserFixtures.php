<?php

namespace App\DataFixtures;

use App\Entity\Cocktail;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

class UserFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $adminUser = new User();
        $adminUser->setFirstname('Mickael');
        $adminUser->setLastname('Ndinga');
        $adminUser->setEmail('micky@cocktail.com');
        $adminUser->setPassword($this->hasher->hashPassword($adminUser, 'password'));
        $adminUser->setRoles('ROLE_ADMIN');
        $manager->persist($adminUser);

        $vipUser = new User();
        $vipUser->setFirstname('Steve');
        $vipUser->setLastname('Roger');
        $vipUser->setEmail('steve@cocktail.com');
        $vipUser->setPassword($this->hasher->hashPassword($vipUser, 'password'));
        $vipUser->setRoles('ROLE_VIP');
        $manager->persist($vipUser);

        $simpleUser = new User();
        $simpleUser->setFirstname('Diana');
        $simpleUser->setLastname('Ross');
        $simpleUser->setEmail('diana@cocktail.com');
        $simpleUser->setPassword($this->hasher->hashPassword($simpleUser, 'password'));
        $manager->persist($simpleUser);

        $manager->flush();
    }
}
