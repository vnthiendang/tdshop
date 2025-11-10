<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Log\Log;

class CartController extends AppController
{
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        
        // allow adding to cart without login
        $this->Authentication->addUnauthenticatedActions(['index', 'add', 'update', 'remove', 'clear']);
    }

    public function index()
    {
        $user = $this->Authentication->getIdentity();
        if (!$user) {
            return $this->response
                ->withStatus(401)
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'status_code'=> 401,
                    'error' => 'Authentication required'
                ]));
        }
        // use cart fetched from AppController
        $cart = $this->viewBuilder()->getVar('headerCart');
        if (!$cart) {
            $cart = $this->getCart($user);
        }
        // $this->set('cart', $cart);
        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode([
                'success' => true,
                'data' => $cart,
            ]));
    }
    public function add()
    {
        $this->request->allowMethod(['post']);
        $reqData = $this->request->getData();

        try {
            $product = $this->fetchTable('Products')->getActiveProductWithDetails($reqData['product_id']);
            if (!$product) {
                throw new RecordNotFoundException('Product not found');
            }
            $quantity = (int)($reqData['quantity'] ?? 1);
            $cart = $this->getCart();

            $existingItem = $this->fetchTable('CartItems')->find()
                ->where(['cart_id' => $cart->id, 'product_id' => $product->id])
                ->first();
            if ($existingItem) {
                $quantity += $existingItem->quantity;
            }
            if ($quantity > $product->stock) {
                // return $this->respondError('Quantity exceeds available stock!', 400, '/products');
                return $this->response
                    ->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode([
                        'success' => false,
                        'message' => 'Quantity exceeds available stock!',
                    ]));
            }

            $cartItemTable = $this->fetchTable('CartItems');
            $cartItemTable->addOrUpdate(
                $cart->id,
                $product->id,
                $quantity,
                $product->price
            );

            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => true,
                    'message' => 'Added to cart successfully!',
                    'data' => ['cartTotal' => (float)$cart->total],
                ]));
        } catch (RecordNotFoundException $e) {
            return $this->response
                ->withType('application/json')
                ->withStatus(404)
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Product not found',
                ]));
        } catch (\Exception $e) {
            Log::error('Cart Add Error: ' . $e->getMessage());
            return $this->response
                ->withType('application/json')
                ->withStatus(500)
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => $e->getMessage(),
                ]));
        }
    }
    
    /**
     * Update quantity in cart
     */
    public function update($itemId = null)
    {
        $this->request->allowMethod(['post']);
        $cartItemTable = $this->fetchTable('CartItems');
        $productTable = $this->fetchTable('Products');
    
        try {
            $cartItem = $cartItemTable->getItemWithProduct($itemId);
            $quantity = (int)$this->request->getData('quantity', 1);
        
            if ($quantity <= 0) {
                return $this->response
                    ->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode([
                        'success' => false,
                        'message' => 'Invalid quantity',
                    ]));
            }
        
            $product = $productTable->getActiveProductWithDetails($cartItem->product_id);
            if ($quantity > $product->stock) {
                return $this->response
                    ->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode([
                        'success' => false,
                        'message' => 'Quantity exceeds available stock!',
                    ]));
            }
        
            $cartItemTable->updateQuantity($cartItem, $quantity);
            $cart = $this->getCart();
        
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => true,
                    'message' => 'Cart updated successfully!',
                    'data' => [
                        'itemSubtotal' => (float)$cartItem->quantity * (float)$cartItem->price,
                        'cartTotal' => (float)$cart->total,
                        'cartTotalWithShipping' => (float)$cart->total,
                        'totalItems' => $cart->total_items
                    ]
                ]));
        } catch (RecordNotFoundException $e) {
            return $this->response
                ->withType('application/json')
                ->withStatus(404)
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Cart item not found',
                ]));
        } catch (\Exception $e) {
            return $this->response
                ->withType('application/json')
                ->withStatus(500)
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Could not update cart!',
                ]));
        }
    }
    
    /**
     * Remove item from cart
     */
    public function remove($itemId = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $cartItemTable = $this->fetchTable('CartItems');
        
        $cartItem = $cartItemTable->get($itemId);
        
        try {
            if ($cartItemTable->delete($cartItem)) {
                return $this->response
                    ->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => true,
                        'message' => 'Item removed from cart successfully!',
                    ]));
            } else {
                return $this->response
                    ->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode([
                        'success' => false,
                        'message' => 'Could not remove item!',
                    ]));
            }
        } catch (\Exception $e) {
            return $this->response
                ->withType('application/json')
                ->withStatus(500)
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Could not remove item!',
                    "error" => $e->getMessage(),
                ]));
        }
    }
    
    /**
     * Clear entire cart
     */
    public function clear()
    {
        $this->request->allowMethod(['post']);
        
        $cart = $this->getCart();
        
        $this->fetchTable('CartItems')->deleteAll(['cart_id' => $cart->id]);
        
        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode([
                'success' => true,
                'message' => 'Cart cleared successfully!',
            ]));
    }
    
    /**
     * Helper: Get current cart
     */
    private function getCart($user = null)
    {   
        if ($user) {
            return $this->fetchTable('Carts')->getOrCreateCart($user->id);
        } else {
            $sessionId = $this->request->getSession()->id();
            return $this->fetchTable('Carts')->getOrCreateCart(null, $sessionId);
        }
    }
}
