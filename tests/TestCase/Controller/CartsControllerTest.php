<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Cake\ORM\TableRegistry;

class CartsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    protected array $fixtures = [
        'app.Users',
        'app.Categories',
        'app.Products',
        'app.Carts',
        'app.CartItems'
    ];

    public function setUp(): void
    {
        parent::setUp();

        // Fake session login user
        $this->session([
            'Auth' => [
                'id' => 1,
                'email' => 'test@example.com',
                'role' => 'customer'
            ]
        ]);

        $this->Products = TableRegistry::getTableLocator()->get('Products');
        $this->CartItems = TableRegistry::getTableLocator()->get('CartItems');
        $this->Carts = TableRegistry::getTableLocator()->get('Carts');
    }

    public function testIndexShowsCart(): void
    {
        $this->get('/cart');
        $this->assertResponseOk();
    }

    public function testAddToCartSuccess(): void
    {
        $product = $this->Products->newEntity([
            'name' => 'Sample Product',
            'slug' => 'sample-product',
            'price' => 200000,
            'stock' => 10,
            'status' => 'active',
            'category_id' => 1,
        ]);
        $this->Products->save($product);

        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/cart/add', [
            'product_id' => $product->id,
            'quantity' => 2
        ]);

        $this->assertResponseCode(302);
        $this->assertRedirectContains('/');

        $cartItem = $this->CartItems->find()->where(['product_id' => $product->id])->first();
        $this->assertNotEmpty($cartItem);
        $this->assertEquals(2, $cartItem->quantity);
    }

    public function testAddExceedsStock(): void
    {
        $product = $this->Products->newEntity([
            'name' => 'Limited Product',
            'slug' => 'limited-product',
            'price' => 150000,
            'stock' => 1,
            'status' => 'active',
            'category_id' => 1,
        ]);
        $this->Products->save($product);

        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/cart/add', [
            'product_id' => $product->id,
            'quantity' => 5
        ]);

        $this->assertResponseCode(302);
        $this->assertFlashMessage('Quantity exceeds available stock!');

        $count = $this->CartItems->find()->where(['product_id' => $product->id])->count();
        $this->assertEquals(0, $count);
    }

    public function testAddAjaxSuccess(): void
    {
        $product = $this->Products->newEntity([
            'name' => 'Ajax Product',
            'slug' => 'ajax-product',
            'price' => 100000,
            'stock' => 5,
            'status' => 'active',
            'category_id' => 1,
        ]);
        $this->Products->save($product);

        $this->configRequest([
            'headers' => ['Accept' => 'application/json'],
            'ajax' => true
        ]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/cart/add', [
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        // Controller redirects for non-errors even if Accept JSON is set
        $this->assertResponseCode(302);
        $this->assertRedirectContains('/');
    }

    public function testAddMissingProductId(): void
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/cart/add', ['quantity' => 1]);

        $this->assertResponseCode(302);
        $this->assertFlashMessage('Could not add to cart!');
    }

    public function testUpdateQuantitySuccess(): void
    {
        $item = $this->CartItems->find()->first();
        $this->assertNotEmpty($item);

        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/cart/update/' . $item->id, ['quantity' => 3]);

        $this->assertResponseCode(302);
        $this->assertRedirectContains('/cart');
    }

    public function testUpdateQuantityInvalid(): void
    {
        $item = $this->CartItems->find()->first();

        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/cart/update/' . $item->id, ['quantity' => 0]);

        $this->assertResponseCode(302);
        $this->assertRedirectContains('/cart');
        $this->assertFlashMessage('Invalid quantity');
    }

    public function testRemoveItem(): void
    {
        $item = $this->CartItems->find()->first();
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/cart/remove/' . $item->id);

        $this->assertResponseCode(302);
        $this->assertRedirect(['controller' => 'Cart', 'action' => 'index']);
        $this->assertFlashMessage('Item removed from cart successfully!');
    }

    public function testClearCart(): void
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/cart/clear');

        $this->assertResponseCode(302);
        $this->assertRedirect(['controller' => 'Cart', 'action' => 'index']);
        $this->assertFlashMessage('Cart cleared successfully!');
    }
}
