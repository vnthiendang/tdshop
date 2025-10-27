<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;
use Cake\Http\Exception\NotFoundException;use Cake\Log\Log;

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
        
        $product = $this->fetchTable('Products')->get($reqData['product_id']);
        
        if ($product->status !== 'active') {
            $this->Flash->error('Product is not available!');
            return $this->redirect(['controller' => 'Products', 'action' => 'index']);
        }
        
        $quantity = (int)$this->request->getData('quantity', 1);
        
        // Check stock availability
        if ($quantity > $product->stock) {
            $this->Flash->error('Quantity exceeds available stock!');
            return $this->redirect($this->referer());
        }
        
        $cart = $this->getCart();
        $price = $product->price; // Get the discounted price
        
        $this->fetchTable('CartItems')->addOrUpdate(
            $cart->id,
            $product->id,
            $quantity,
            $price
        );
        
        $this->Flash->success('Added to cart successfully!');
        return $this->redirect($this->referer());
    }
    
    /**
     * Update quantity in cart
     */
    public function update($itemId = null)
    {
        $this->request->allowMethod(['post']);
        
        // Use find() to avoid deprecated Table::get() options usage
        $cartItem = $this->fetchTable('CartItems')
            ->find()
            ->where(['CartItems.id' => $itemId])
            ->contain(['Products'])
            ->firstOrFail();
        $quantity = (int)$this->request->getData('quantity', 1);
        
        if ($quantity <= 0) {
            if ($this->request->is('ajax')) {
                $this->response = $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false,
                        'error' => 'Invalid quantity'
                    ]));
                return $this->response->withStatus(400);
            }
            return $this->redirect(['action' => 'remove', $itemId]);
        }
        
        // Check stock
        $product = $this->fetchTable('Products')->get($cartItem->product_id);
        if ($quantity > $product->stock) {
            if ($this->request->is('ajax')) {
                $this->response = $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false,
                        'error' => 'Quantity exceeds available stock!'
                    ]));
                return $this->response->withStatus(400);
            }
            $this->Flash->error('Quantity exceeds available stock!');
            return $this->redirect(['action' => 'index']);
        }
        
        $cartItem->quantity = $quantity;
        
        if ($this->fetchTable('CartItems')->save($cartItem)) {
            // get cart to recalculate totals
            $cart = $this->getCart();
            
            if ($this->request->is('ajax')) {
                $this->response = $this->response->withType('application/json')
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
                return $this->response->withStatus(200);
            }
            $this->Flash->success('Cart updated successfully!');
        } else {
            if ($this->request->is('ajax')) {
                $this->response = $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false,
                        'error' => 'Could not update cart!'
                    ]));
                return $this->response->withStatus(500);
            }
            $this->Flash->error('Could not update cart!');
        }
        
        return $this->redirect(['action' => 'index']);
    }
    
    /**
     * Remove item from cart
     */
    public function remove($itemId = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        
        $cartItem = $this->fetchTable('CartItems')->get($itemId);
        
        if ($this->fetchTable('CartItems')->delete($cartItem)) {
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
