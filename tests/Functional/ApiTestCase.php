<?php

// src/Tests/Functional/ApiTestCase.php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class ApiTestCase extends WebTestCase
{
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
    }

    protected function request(string $method, string $url, array $data = [], array $headers = []): Response
    {
        $this->client->request(
            $method,
            $url,
            [],
            [],
            array_merge(['CONTENT_TYPE' => 'application/json'], $headers),
            json_encode($data)
        );

        return $this->client->getResponse();
    }
}
