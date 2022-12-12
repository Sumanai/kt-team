<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $category = new Category();
        $category
            ->setName('Test 1')
            ->setCode('test_1');

        $manager->persist($category);

        foreach (range(1, 20) as $key) {
            $product = new Product();
            $product
                ->setName('Test ' . $key)
                ->setDescription('Test desc ' . $key)
                ->setWeight(100 + $key)
                ->setCategory($category);

            $manager->persist($product);
        }

        $manager->flush();
    }
}
