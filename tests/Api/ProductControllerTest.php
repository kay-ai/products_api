<?php

namespace App\Tests\Controller\Api;

use App\Tests\Functional\ApiTestCase;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class ProductControllerTest extends ApiTestCase
{
    private $entityManager;
    private $passwordHasher;
    private $createdProductId;

    protected function setUp(): void
    {
        parent::setUp();

        $container = self::getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->passwordHasher = $container->get(UserPasswordHasherInterface::class);

        $this->createTestUser();
    }

    private function createTestUser()
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $existingUser = $userRepository->findOneBy(['username' => 'gitstartuser']);

        if (!$existingUser) {
            $user = new User();
            $user->setUsername('gitstartuser');
            $user->setPassword(
                $this->passwordHasher->hashPassword($user, 'justapassword')
            );
            $user->setEmail('user@gitstart.com');
            $user->setRoles(['ROLE_ADMIN']);

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }

    private function loginAndGetToken()
    {
        $response = $this->request('POST', '/api/login', 
            [
                'email' => 'user@gitstart.com',
                'password' => 'justapassword'
            ]
        );

        $statusCode = $response->getStatusCode();
        $content = $response->getContent();

        $data = json_decode($content, true);

        if (isset($data['token'])) {
            return $data['token'];
        } else {
            throw new \Exception("Failed to retrieve token: " . $content);
        }
    }

    private function createProduct()
    {
        $token = $this->loginAndGetToken();

        $response = $this->request(
            Request::METHOD_POST,
            '/api/products/',
            [
                'name' => 'Product name',
                'price' => 100.00,
                'description' => 'Product description'
            ],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token
            ]
        );

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('product', $responseData);

        $this->createdProductId = $responseData['product']['id'];
    }

    public function testCreateProduct()
    {
        $this->createProduct();
    }


    public function testGetAllProducts()
    {
        $token = $this->loginAndGetToken();
        $response = $this->request(
            Request::METHOD_GET,
            '/api/products/',
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
                'HTTP_ACCEPT' => 'application/json'
            ]
        );

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);

        $this->assertIsArray($responseData);
    }

    public function testGetProductById()
    {
        $this->createProduct();
        $token = $this->loginAndGetToken();

        $productId = $this->createdProductId;

        $response = $this->request(
            Request::METHOD_GET,
            '/api/products/' . $productId,
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
                'HTTP_ACCEPT' => 'application/json'
            ]
        );

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals($productId, $responseData['product']['id']);
    }

    public function testUpdateProduct()
    {
        $this->createProduct();

        $token = $this->loginAndGetToken();
        $productId = $this->createdProductId;

        $response = $this->request(
            Request::METHOD_PUT,
            '/api/products/' . $productId,
            [
                'name' => 'Updated Product',
                'price' => 150.0,
                'description' => 'New Product description'
            ],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
                'CONTENT_TYPE' => 'application/json'
            ]
        );

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('Updated Product', $responseData['product']['name']);
        $this->assertEquals(150.0, $responseData['product']['price']);
        $this->assertEquals("New Product description", $responseData['product']['description']);
    }

    public function testDeleteProduct()
    {
        $this->createProduct();

        $token = $this->loginAndGetToken();
        $productId = $this->createdProductId;

        $response = $this->request(
            Request::METHOD_DELETE,
            '/api/products/' . $productId,
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token
            ]
        );

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    // protected function tearDown(): void
    // {
    //     parent::tearDown();

    //     $userRepository = $this->entityManager->getRepository(User::class);
    //     $testUser = $userRepository->findOneBy(['username' => 'gitstartuser']);

    //     if ($testUser) {
    //         $this->entityManager->remove($testUser);
    //         $this->entityManager->flush();
    //     }

    //     $this->entityManager->close();
    //     $this->entityManager = null;
    // }

}
