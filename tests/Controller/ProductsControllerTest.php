<?php

namespace App\Tests\Controller;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductsControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/products');

        $this->assertResponseIsSuccessful();

        $this->assertCount(
            1,
            $crawler->filter('li'),
            'The page displays the right number of products.'
        );
    }
}
