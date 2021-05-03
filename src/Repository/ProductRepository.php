<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

// use Doctrine\Common\Annotations\AnnotationReader;
// use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
// use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
// use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
// use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    private $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        parent::__construct($registry, Product::class);
    }

    public function serialize($entity) {

        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        
        $serializer = new Serializer($normalizers, $encoders);  

        return $serializer->serialize($entity, 'json');
    }

    // public function serialize($entities, $options = []) {

    //     $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));

    //     $normalizers = [
    //         new DateTimeNormalizer(),             
    //         new ObjectNormalizer($classMetadataFactory, null, null, new ReflectionExtractor()),
    //     ];
        
    //     $serializer = new Serializer($normalizers);
        
    //     return $serializer->normalize($entities, null, $options);
    // } 

    public function create(object $raw_product)
    {
        $product = new Product();
        $category = null;

        $category_id = $raw_product->category_id;
        if (!is_null($category_id)) {
            $category = $this->entityManager->getRepository(Category::class)->find($category_id);
        }

        $product
            ->setName($raw_product->name)
            ->setPrice($raw_product->price)
            ->setDescription($raw_product->description)
            ->setCategory($category)
        ;
        
        $errors = $this->validator->validate($product);
        
        if (count($errors) > 0) {
            dump($errors);exit;
            throw new \Exception('Produit non valide');
        }

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return $this->serialize($product);

        // $products = $this
        //     ->createQueryBuilder('product')
        //     ->select('product')
        //     ->where('product.id = :id')
        //     ->setParameter(':id', $product->getId())
        //     ->getQuery()
        //     ->getArrayResult()
        // ;

        // return $products[0];
    }

    public function findAllProductsQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('p');
    }

    public function findByPriceQuery(QueryBuilder $qb, int $price): QueryBuilder
    {
        return $qb
            ->andWhere('p.price > :price')
            ->setParameter('price', $price, \PDO::PARAM_INT)
            ->orderBy('p.price', 'ASC')
        ;
    }

    public function findByNameQuery(QueryBuilder $qb, string $name): QueryBuilder
    {
        return $qb
            ->andWhere("p.name LIKE :name")
            ->setParameter('name', "%$name%")
        ;
    }

    public function findAllProductsByPriceAndName(int $price, string $name = null)
    {
        $qb = $this->findAllProductsQuery($price);
        
        if (!is_null($name)) $qb = $this->findByNameQuery($qb, $name);
        
        $qb = $this->findByPriceQuery($qb, $price);

        return $qb            
            ->getQuery()
            ->getArrayResult()
        ;
    }

    public function findAllProductsByPrice(int $price) 
    {
        $qb = $this->findAllProductsQuery();
        
        return $this
            ->findByPriceQuery($price)
            ->getQuery()
            ->getArrayResult()
        ;
    }

    /**
     * @param $price
     * @return Product[]
     */
    public function findAllGreaterThanPrice(int $price): array
    {
        // automatically knows to select Products
        // the "p" is an alias you'll use in the rest of the query
        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.price > :price')
            ->setParameter('price', $price, \PDO::PARAM_INT)
            ->orderBy('p.price', 'ASC')
            ->getQuery();

        // to return array of arrays
        return $qb->getArrayResult();
        
        // to return array of entities
        // return $qb->execute();
        
        // to get just one result:
        // $product = $qb->setMaxResults(1)->getOneOrNullResult();
    }

    // /**
    //  * @return Product[] Returns an array of Product objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Product
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
