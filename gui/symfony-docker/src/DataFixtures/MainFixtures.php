<?php

namespace App\DataFixtures;

use App\Entity\ProductionSite;
use App\Entity\ResourceCategory;
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


        //ResourceCategory
        $resourceCategory = new ResourceCategory();
        $resourceCategory->setCategory("ANIMAL");
        $manager->persist($resourceCategory);
        $resourceCategory2 = new ResourceCategory();
        $resourceCategory2->setCategory("CARCASSE");
        $manager->persist($resourceCategory2);
        $resourceCategory3 = new ResourceCategory();
        $resourceCategory3->setCategory("DEMI-CARCASSE");
        $manager->persist($resourceCategory3);
        $resourceCategory4 = new ResourceCategory();
        $resourceCategory4->setCategory("MORCEAU");
        $manager->persist($resourceCategory4);
        $resourceCategory5 = new ResourceCategory();
        $resourceCategory5->setCategory("PRODUIT");
        $manager->persist($resourceCategory5);

        //resource names
        $resourceName = new ResourceName();
        $resourceName->setName("Carcasse de boeuf");
        $resourceName->setResourceCategory($resourceCategory2);
        $manager->persist($resourceName);
        $resourceName2 = new ResourceName();
        $resourceName2->setName("Boeuf");
        $resourceName2->setResourceCategory($resourceCategory);
        $manager->persist($resourceName2);
        $resourceName3 = new ResourceName();
        $resourceName3->setName("Vache");
        $resourceName3->setResourceCategory($resourceCategory);
        $manager->persist($resourceName3);
        $resourceName4 = new ResourceName();
        $resourceName4->setName("Veau");
        $resourceName4->setResourceCategory($resourceCategory);
        $manager->persist($resourceName4);
        $resourceName5 = new ResourceName();
        $resourceName5->setName("Porc");
        $resourceName5->setResourceCategory($resourceCategory);
        $manager->persist($resourceName5);
        $resourceName6 = new ResourceName();
        $resourceName6->setName("Carcasse de porc");
        $resourceName6->setResourceCategory($resourceCategory2);
        $manager->persist($resourceName6);
        $resourceName7 = new ResourceName();
        $resourceName7->setName("Demi-carcasse de porc");
        $resourceName7->setResourceCategory($resourceCategory3);
        $manager->persist($resourceName7);
        $resourceName8 = new ResourceName();
        $resourceName8->setName("Cuisse de veau");
        $resourceName8->setResourceCategory($resourceCategory4);
        $manager->persist($resourceName8);
        $resourceName9 = new ResourceName();
        $resourceName9->setName("Steak de boeuf");
        $resourceName9->setResourceCategory($resourceCategory5);
        $manager->persist($resourceName9);

        $manager->flush();
    }
}
