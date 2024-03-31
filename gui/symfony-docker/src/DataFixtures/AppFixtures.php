<?php

namespace App\DataFixtures;

use App\Entity\ProductionSite;
use App\Entity\ResourceCategory;
use App\Entity\ResourceFamily;
use App\Entity\ResourceName;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture implements FixtureGroupInterface
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }
    public static function getGroups(): array
    {
        return ['app'];
    }

    public function load(ObjectManager $manager): void
    {
        $familiesNames = ['Vache', 'Boeuf', 'Veau', 'Mouton', 'Porc', 'Brebis'];
        $morceauxNames = ['Filet', 'Côte', 'Epaule', 'Gigot', 'Poitrine', 'Jarret', 'Foie', 'Rognon', 'Coeur', 'Langue', 'Pied', 'Queue', 'Gras'];
        $categoriesNames = ['ANIMAL', 'CARCASSE', 'DEMI-CARCASSE', 'MORCEAU', 'PRODUIT'];

        // ResourceFamily
        foreach ($familiesNames as $familyName) {
            $family = new ResourceFamily();
            $family->setName($familyName);
            $manager->persist($family);
            $manager->flush();
        }

        // ResourceCategory
        foreach ($categoriesNames as $categoryName) {
            $category = new ResourceCategory();
            $category->setCategory($categoryName);
            $manager->persist($category);
            $manager->flush();
        }

        // ResourceName
        // Animaux, Carcasses et Demi-Carcasses
        foreach ($familiesNames as $familyName) {
            $animal = new ResourceName();
            $carcasse = new ResourceName();
            $demiCarcasse = new ResourceName();

            $animal->setName($familyName);
            $carcasse->setName('Carcasse de ' . $familyName);
            $demiCarcasse->setName('Demi-carcasse de ' . $familyName);

            $animal->setFamily($manager->getRepository(ResourceFamily::class)->findOneBy(['name' => $familyName]));
            $carcasse->setFamily($manager->getRepository(ResourceFamily::class)->findOneBy(['name' => $familyName]));
            $demiCarcasse->setFamily($manager->getRepository(ResourceFamily::class)->findOneBy(['name' => $familyName]));

            $animal->setResourceCategory($manager->getRepository(ResourceCategory::class)->findOneBy(['category' => 'ANIMAL']));
            $carcasse->setResourceCategory($manager->getRepository(ResourceCategory::class)->findOneBy(['category' => 'CARCASSE']));
            $demiCarcasse->setResourceCategory($manager->getRepository(ResourceCategory::class)->findOneBy(['category' => 'DEMI-CARCASSE']));

            $manager->persist($animal);
            $manager->persist($carcasse);
            $manager->persist($demiCarcasse);
            $manager->flush();
        }

        // Morceaux
        foreach ($familiesNames as $familyName) {
            foreach ($morceauxNames as $morceauName) {
                $morceau = new ResourceName();
                $morceau->setName($morceauName . ' de ' . $familyName);
                $morceau->setFamily($manager->getRepository(ResourceFamily::class)->findOneBy(['name' => $familyName]));
                $morceau->setResourceCategory($manager->getRepository(ResourceCategory::class)->findOneBy(['category' => 'MORCEAU']));
                $manager->persist($morceau);
                $manager->flush();
            }
        }

        // Production Sites
        $productionSites = ['eleveur'=>'Ferme1', 'equarrisseur'=>'Abattoir1', 'usine'=>'Usine1', 'distributeur'=>'Magasin1'];
        //Specifics
        foreach ($productionSites as $productionSite) {
            $pS = new ProductionSite();
            $pS->setProductionSiteName($productionSite);
            $pS->setAddress('22 rue Nationale');
            $pS->setProductionSiteTel('0123456789');
            $manager->persist($pS);
            $manager->flush();
        }
        //Generals
        for ($i = 0; $i < 10; $i++) {
            $pS = new ProductionSite();
            $pS->setProductionSiteName('Production Site ' . $i);
            $pS->setAddress('23 rue Nationale');
            $pS->setProductionSiteTel('0123456789');
            $manager->persist($pS);
            $manager->flush();
        }

        // Users
        $users = ['admin', 'eleveur', 'transporteur', 'equarrisseur', 'usine', 'distributeur'];

        foreach ($users as $userName) {
            $newUser = new User();
            $newUser->setEmail($userName . '@gmail.com');
            $newUser->setPassword($this->passwordHasher->hashPassword($newUser, $userName));
            $newUser->setRoles(["ROLE_" . strtoupper($userName), "ROLE_PRO"]);
            $newUser->setFirstname(ucfirst($userName));
            $newUser->setLastname("The" . ucfirst($userName));

            if (isset($productionSites[$userName])) { // If the user is a professional with a production site
                $newUser->setProductionSite($manager->getRepository(ProductionSite::class)->findOneBy(['ProductionSiteName' => $productionSites[$userName]]));
            }
            $manager->persist($newUser);
            $manager->flush();
        }

        for($i = 0; $i < 100; $i++) {
            $newUser = new User();
            $newUser->setEmail('user' . $i . '@gmail.com');
            $newUser->setPassword($this->passwordHasher->hashPassword($newUser, 'user'));
            $newUser->setRoles(["ROLE_USER"]);
            $newUser->setFirstname("FirstName " . $i);
            $newUser->setLastname("LastName " . $i);
            $manager->persist($newUser);
            $manager->flush();
        }
    }
}
