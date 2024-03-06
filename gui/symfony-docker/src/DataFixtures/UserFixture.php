<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixture extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['user'];
    }
    
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }


    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        $admin = new User();
        $admin->setEmail("admin@gmail.com");
        $admin->setPassword($this->passwordHasher->hashPassword($admin,'admin'));
        $admin->setRoles(["ROLE_ADMIN"]);
        $admin->setFirstname("Raymond");
        $admin->setLastname("Zheng");
        $manager->persist($admin);

        $admin2 = new User();
        $admin2->setEmail("admin2@gmail.com");
        $admin2->setPassword($this->passwordHasher->hashPassword($admin2,'admin'));
        $admin2->setRoles(["ROLE_ADMIN"]);
        $admin2->setFirstname("Dan");
        $admin2->setLastname("Klecz");
        $manager->persist($admin2);

        $manager->flush();
    }
}
