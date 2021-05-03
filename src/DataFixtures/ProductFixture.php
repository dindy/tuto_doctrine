<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Product;
use App\DataFixtures\CategoryFixture;

class ProductFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $product = new Product();
        $product->setName('Test 1');
        $product->setPrice(2000);
        $product->setDescription('Ceci est un test');
        $product->setCategory($this->getReference(CategoryFixture::CATEGORY1_REFERENCE));
        $manager->persist($product);
        
        $product = new Product();
        $product->setName('Test 2');
        $product->setPrice(1000);
        $product->setDescription('Ceci est un test');
        $product->setCategory($this->getReference(CategoryFixture::CATEGORY2_REFERENCE));
        $manager->persist($product);

        $manager->flush();
    }
}
