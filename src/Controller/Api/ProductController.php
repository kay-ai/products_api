<?php

// src/Controller/Api/ProductController.php
namespace App\Controller\Api;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


#[Route('/api/products')]
class ProductController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ProductRepository $productRepository;

    public function __construct(EntityManagerInterface $entityManager, ProductRepository $productRepository)
    {
        $this->entityManager = $entityManager;
        $this->productRepository = $productRepository;
    }

    #[Route('/', name: 'api_product_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $products = $this->productRepository->findAll();
        return $this->json($products);
    }

    #[Route('/', name: 'api_product_create', methods: ['POST'])]
    public function create(Request $request, ValidatorInterface $validator, SerializerInterface $serializer): JsonResponse
    {
        try{
            $requestData = $request->getContent();
            $data = json_decode($requestData, true);

            $product = $serializer->deserialize($requestData, Product::class, 'json');

            $errors = $validator->validate($product);
            if (count($errors) > 0) {
                return new JsonResponse($errors, Response::HTTP_BAD_REQUEST);
            }

            if (isset($data['price'])) {
                $product->setPrice((float) $data['price']);
            }

            $this->entityManager->persist($product);
            $this->entityManager->flush();

            $data = $serializer->serialize($product, 'json');

            return new JsonResponse(['message' => 'Product created!', 'product' => json_decode($data)], 201);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }


    #[Route('/{id}', name: 'api_product_show', methods: ['GET'])]
    public function show(int $id, SerializerInterface $serializer): JsonResponse
    {
        $product = $this->productRepository->find($id);
        if (!$product) {
            return $this->json(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }
        $data = $serializer->serialize($product, 'json');
        return new JsonResponse(['message' => 'Product found!', 'product' => json_decode($data)], 200);
    }

    #[Route('/{id}', name: 'api_product_update', methods: ['PUT'])]
    public function update(int $id, Request $request, ValidatorInterface $validator, SerializerInterface $serializer): JsonResponse
    {
        $product = $this->productRepository->find($id);
        if (!$product) {
            return $this->json(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        try {
            $requestData = $request->getContent();
            $updatedProduct = $serializer->deserialize($requestData, Product::class, 'json');

            $product->setName($updatedProduct->getName());
            $product->setDescription($updatedProduct->getDescription());
            $product->setPrice($updatedProduct->getPrice());

            $this->entityManager->flush();

            $responseData = $serializer->serialize($product, 'json');
            return new JsonResponse(['message' => 'Product updated!', 'product' => json_decode($responseData)], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }


    #[Route('/{id}', name: 'api_product_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $product = $this->productRepository->find($id);
        if (!$product) {
            return $this->json(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($product);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Product deleted successfully!'], 200);
    }
}
