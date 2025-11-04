<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class ProductsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    protected array $fixtures = [
        'app.Products',
        'app.Categories',
    ];

    public function testIndexShowsActiveProducts(): void
    {
        $this->get('/products');

        $this->assertResponseOk();
        // Should include active products
        $this->assertResponseContains('Active Phone');
        $this->assertResponseContains('Active Laptop');
        $this->assertResponseContains('Active Chair');
        // Should not include inactive product
        $this->assertResponseNotContains('Inactive Mouse');
    }

    public function testIndexFiltersByCategory(): void
    {
        // category_id=1 => Electronics => products 1,2
        $this->get('/products?category_id=1');

        $this->assertResponseOk();
        $this->assertResponseContains('Active Phone');
        $this->assertResponseContains('Active Laptop');
        $this->assertResponseNotContains('Active Chair');
    }

    public function testIndexSearchFilter(): void
    {
        $this->get('/products?search=Chair');

        $this->assertResponseOk();
        $this->assertResponseContains('Active Chair');
        $this->assertResponseNotContains('Active Phone');
    }

    public function testViewShowsProductDetails(): void
    {
        $this->get('/products/view/1');

        $this->assertResponseOk();
        $this->assertResponseContains('Active Phone');
    }

    public function testViewNotFound(): void
    {
        $this->get('/products/view/9999');
        $this->assertResponseCode(404);
    }

    public function testCategoryBySlugShowsProducts(): void
    {
        // furniture category has product id 4 (Active Chair)
        $this->get('/products/category/furniture');

        $this->assertResponseOk();
        $this->assertResponseContains('Active Chair');
        $this->assertResponseNotContains('Active Phone');
    }

    public function testCategoryNotFound(): void
    {
        $this->get('/products/category/does-not-exist');
        $this->assertResponseCode(404);
    }

    public function testCategoryWithoutSlugShowsAllActive(): void
    {
        $this->get('/products/category');

        $this->assertResponseOk();
        $this->assertResponseContains('Active Phone');
        $this->assertResponseContains('Active Laptop');
        $this->assertResponseContains('Active Chair');
        $this->assertResponseNotContains('Inactive Mouse');
    }
}
