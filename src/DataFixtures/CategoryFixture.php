<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Category;

class CategoryFixture extends Fixture
{
    public const CATEGORY1_REFERENCE = 'cat-1';
    public const CATEGORY2_REFERENCE = 'cat-2';

    public function load(ObjectManager $manager)
    {
        $category1 = new Category();
        $category1->setName('Catégorie 1');
        $manager->persist($category1);

        $category2 = new Category();
        $category2->setName('Catégorie 2');
        $manager->persist($category2);

        $manager->flush();

        $this->addReference(self::CATEGORY1_REFERENCE, $category1);
        $this->addReference(self::CATEGORY2_REFERENCE, $category2);
    }
}
