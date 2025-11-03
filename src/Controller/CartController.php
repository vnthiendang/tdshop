<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;
use Cake\Http\Exception\NotFoundException;use Cake\Log\Log;
use App\Controller\Traits\ResponseTrait;

class CartController extends AppController
{
    use ResponseTrait;
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        
        // allow adding to cart without login
        $this->Authentication->addUnauthenticatedActions(['index', 'add', 'update', 'remove', 'clear']);
    }

    public function index()
    {
        // use cart fetched from AppController
        $cart = $this->viewBuilder()->getVar('headerCart');
        if (!$cart) {
            $cart = $this->getCart();
        }
        $this->set('cart', $cart);
    }
    public function add()
    {
        $this->request->allowMethod(['post']);
        $reqData = $this->request->getData();
        Log::error('Cart Add Request Data: ' . json_encode($reqData));

        try {
            $product = $this->fetchTable('Products')->getActiveProductWithDetails($reqData['product_id']);
            $quantity = (int)($reqData['quantity'] ?? 1);
            $cart = $this->getCart();

            $existingItem = $this->fetchTable('CartItems')->find()
                ->where(['cart_id' => $cart->id, 'product_id' => $product->id])
                ->first();
            if ($existingItem) {
                $quantity += $existingItem->quantity;
            }
            if ($quantity > $product->stock) {
                return $this->respondError('Quantity exceeds available stock!', 400, '/products');
            }

            $cartItemTable = $this->fetchTable('CartItems');
            $cartItemTable->addOrUpdate(
                $cart->id,
                $product->id,
                $quantity,
                $product->price
            );

            return $this->respondSuccess([
                'message' => 'Added to cart successfully!',
                'data' => ['cartTotal' => (float)$cart->total]
            ], '/products');
        } catch (RecordNotFoundException $e) {
            return $this->respondError('Product not found', 404, '/products');
        } catch (\Exception $e) {
            Log::error('Cart Add Error: ' . $e->getMessage());
            return $this->respondError('Could not add to cart!', 500, '/products');
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
                return $this->respondError('Invalid quantity', 400);
            }
        
            $product = $productTable->getActiveProductWithDetails($cartItem->product_id);
            if ($quantity > $product->stock) {
                return $this->respondError('Quantity exceeds available stock!', 400);
            }
        
            $cartItemTable->updateQuantity($cartItem, $quantity);
            $cart = $this->getCart();
        
            return $this->respondSuccess([
                'message' => 'Cart updated successfully!',
                'data' => [
                    'itemSubtotal' => (float)$cartItem->quantity * (float)$cartItem->price,
                    'cartTotal' => (float)$cart->total,
                    'cartTotalWithShipping' => (float)$cart->total,
                    'totalItems' => $cart->total_items
                ]
            ], '/cart');
        
        } catch (RecordNotFoundException $e) {
            return $this->respondError('Cart item not found', 404, '/cart');
        } catch (\Exception $e) {
            return $this->respondError('Could not update cart!', 500, '/cart');
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
        
        if ($cartItemTable->delete($cartItem)) {
            $this->Flash->success('Item removed from cart successfully!');
        } else {
            $this->Flash->error('Could not remove item!');
        }
        
        return $this->redirect(['action' => 'index']);
    }
    
    /**
     * Clear entire cart
     */
    public function clear()
    {
        $this->request->allowMethod(['post']);
        
        $cart = $this->getCart();
        
        $this->fetchTable('CartItems')->deleteAll(['cart_id' => $cart->id]);
        
        $this->Flash->success('Cart cleared successfully!');
        return $this->redirect(['action' => 'index']);
    }
    
    /**
     * Helper: Get current cart
     */
    private function getCart()
    {
        $user = $this->Authentication->getIdentity();
        
        if ($user) {
            return $this->fetchTable('Carts')->getOrCreateCart($user->id);
        } else {
            $sessionId = $this->request->getSession()->id();
            return $this->fetchTable('Carts')->getOrCreateCart(null, $sessionId);
        }
    }
}
