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

            $animal->addResourceFamily($manager->getRepository(ResourceFamily::class)->findOneBy(['name' => $familyName]));
            $carcasse->addResourceFamily($manager->getRepository(ResourceFamily::class)->findOneBy(['name' => $familyName]));
            $demiCarcasse->addResourceFamily($manager->getRepository(ResourceFamily::class)->findOneBy(['name' => $familyName]));

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
                $morceau->addResourceFamily($manager->getRepository(ResourceFamily::class)->findOneBy(['name' => $familyName]));
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
            $pS->setAddress('22 rue Nationale'. $productionSite);
            $pS->setProductionSiteTel('0123456789');
            $pS->setValidate(true);
            $pS->setCountry('France'.$productionSite);
            $pS->setApprovalNumber('123456'.$productionSite);
            $manager->persist($pS);
            $manager->flush();
        }
        //Generals
        for ($i = 0; $i < 10; $i++) {
            $pS = new ProductionSite();
            $pS->setProductionSiteName('Production Site ' . $i);
            $pS->setAddress('23 rue Nationale');
            $pS->setProductionSiteTel('0123456789');
            $pS->setValidate(true);
            $manager->persist($pS);
            $manager->flush();
        }

        // Users
        $users = ['admin', 'eleveur', 'transporteur', 'equarrisseur', 'usine', 'distributeur'];
        $userWalletAddress = [
            "0xFE3B557E8Fb62b89F4916B721be55cEb828dBd73",
            "0x2DFc6e58d8a388cE38b5413ca2458a7b59d1B844",
            "0xeb754AE8d476b90e1021002A0f8DA4EC5870e0a0",
            "0x9F6C344071C0FDf43132eEfA8309a770A063D82D",
            "0x0b97F7B3FC38bF1DFf740d65B582c61b3E84FfC6",
            "0x03B950EC5b1D893CDEB5d9A8A9165FeC3eF7914e"
        ];
        for($i = 0; $i<count($users);$i++ ){
            $newUser = new User();
            $newUser->setEmail($users[$i] . '@gmail.com');
            $newUser->setPassword($this->passwordHasher->hashPassword($newUser, $users[$i]));
            $newUser->setRoles(["ROLE_" . strtoupper($users[$i]), "ROLE_PRO"]);
            $newUser->setFirstname(ucfirst($users[$i]));
            $newUser->setLastname("The" . ucfirst($users[$i]));
            $newUser->setWalletAddress($userWalletAddress[$i]);
            

            if (isset($productionSites[$users[$i]])) { // If the user is a professional with a production site
                $newUser->setProductionSite($manager->getRepository(ProductionSite::class)->findOneBy(['ProductionSiteName' => $productionSites[$users[$i]]]));
            }
            $manager->persist($newUser);
            $manager->flush();
        }

        for($i = 0; $i < 10; $i++) {
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
