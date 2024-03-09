<?php

namespace App\DataFixtures;

use App\Entity\ProductionSite;
use App\Entity\ResourceName;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class MainFixtures extends Fixture implements FixtureGroupInterface
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }
    public static function getGroups(): array
    {
        return ['main'];
    }

    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        //resource names
        for ($i = 0; $i < 100; $i++) {
            $resourceName = new ResourceName();
            $resourceName->setName("Resource Name $i");
            $manager->persist($resourceName);
        }

        //users
        $admin = new User();
        $admin->setEmail("admin@gmail.com");
        $admin->setPassword($this->passwordHasher->hashPassword($admin,'admin'));
        $admin->setRoles(["ROLE_ADMIN", "ROLE_PRO"]);
        $admin->setFirstname("Dan");
        $admin->setLastname("Kleczewski");
        $manager->persist($admin);

        for ($i = 3; $i<100; $i++){
            $user = new User();
            $user->setEmail("user$i@gmail.com");
            $user->setPassword($this->passwordHasher->hashPassword($user,'user'));
            $user->setRoles(["ROLE_USER"]);
            $user->setFirstname("FirstNameUser");
            $user->setLastname("LastName$i");
            $manager->persist($user);

        }


        //production sites
        for ($i = 1; $i<11; $i++){
            $productionSite = new ProductionSite();
            $productionSite->setProductionSiteName("Production Site $i");
            $productionSite->setAddress("Address $i");
            $productionSite->setProductionSiteTel("1234567890");
            $manager->persist($productionSite);
        }

        $manager->flush();
    }
}
