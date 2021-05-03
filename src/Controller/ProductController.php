<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProductController extends AbstractController
{
    private $entityManager;
    private $productRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->productRepository = $entityManager->getRepository(Product::class);
    }

    /**
     * @Route("/products", methods={"POST"})
     */
    public function create(Request $request): Response
    {
        $body = $request->getContent();
        $raw_product = json_decode($body);

        $product = $this->productRepository->create($raw_product);
        $response = new Response($product);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/products", methods={"GET"})
     */
    public function index(): Response
    {
        $products = $this->productRepository->findAll();

        return new JsonResponse(["products" => $products]);
    }

    /**
     * @Route("/products/{id}", methods={"GET"})
     */
    public function show(Product $product): Response
    {
        return new Response('Check out this great product: '.$product->getName());
    }    

    /**
     * @Route("/products/{id}", methods={"PUT"})
     */
    public function update($id)
    {
        $product = $this->productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException('No product found for id '.$id);
        }

        $product->setName('New product name!');
        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return new Response('New product name: '.$product->getName());
    }    

    /**
     * @Route("/products/{id}", methods={"DELETE"})
     */
    public function delete($id, Product $product)
    {
        $name = $product->getName();
        $this->entityManager->remove($product);
        $this->entityManager->flush();

        return new Response("$name has been deleted !");
    }       


    /**
     * @Route("/product/greater/{price}")
     */
    public function greater($price)
    {
        // $products = $this->productRepository->findAllGreaterThanPrice($price);
        $products = $this->productRepository->findAllProductsByPrice($price);

        return new JsonResponse($products);
    }      

    /**
     * @Route("/product/named/{name}/{price}")
     */
    public function named($name, $price)
    {
        // $products = $this->productRepository->findAllGreaterThanPrice($price);
        $products = $this->productRepository->findAllProductsByPriceAndName($price, $name);

        return new JsonResponse($products);
    }      
}
