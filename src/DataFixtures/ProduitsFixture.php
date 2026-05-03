<?php

namespace App\DataFixtures;

use App\Entity\Categorie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Produit;
use Faker\Factory;

class ProduitsFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create("fr_FR");
        for ($i = 1; $i <= 4; $i++) {
            $cat = new Categorie();
            $cat->setNomCat($faker->name);
            $cat->setDescription($faker->text);
            $manager->persist($cat);
            for ($j = 1; $j <= 20; $j++) {
                $produit = new Produit();
                $produit->setNomProduit($faker->words(3, true))
                    ->setImage("https://loremflickr.com/200/200/pet,animal?lock=" . random_int(1, 500))
                    ->setPrix($faker->randomFloat(2, 5, 200))
                    ->setDescription($faker->paragraph())
                    ->setCat($cat)
                    ->setDispo($faker->boolean(random_int(20, 80)))
                    ->setStock(random_int(0, 50));;
                $manager->persist($produit);
            }
        }

        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}
