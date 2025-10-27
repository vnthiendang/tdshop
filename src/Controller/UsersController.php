<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;
use Cake\Log\Log;

class UsersController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        // $this->httpClient = new Client();
        $this->Users = TableRegistry::getTableLocator()->get('Users');
    }
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        
        $this->Authentication->addUnauthenticatedActions(['register', 'login']);
    }
    
    /**
     * register new user
     */
    public function register()
    {
        if ($this->Authentication->getIdentity()) {
            return $this->redirect(['controller' => 'Products', 'action' => 'index']);
        }
        $user = $this->Users->newEmptyEntity();
        
        if ($this->request->is('post')) {
            
            $user = $this->Users->patchEntity($user, $this->request->getData());
            $user->status = 'active';
            Log::error('Register user Data: ' . json_encode($user));
            if ($this->Users->save($user)) {
                $this->Flash->success('Successfully! Please login.');
                return $this->redirect(['action' => 'login']);
            }
            // Log validation errors for debugging
            if ($user->getErrors()) {
                Log::error('User save failed: ' . json_encode($user->getErrors()));
            }
            $this->Flash->error('Cannot register.');
        }
        
        $this->set(compact('user'));
    }
    
    /**
     * login user
     */
    public function login()
    {
        $this->request->allowMethod(['get', 'post']);
        if ($this->Authentication->getIdentity()) {
            return $this->redirect(['controller' => 'Products', 'action' => 'index']);
        }
        $result = $this->Authentication->getResult();
        
        if ($result->isValid()) {
            $this->mergeSessionCartToUser();
            $redirect = $this->request->getQuery('redirect', '/products');
            return $this->redirect($redirect);
        }
        
        if ($this->request->is('post') && !$result->isValid()) {
            $this->Flash->error('Email or password incorrect!');
        }
    }
    
    /**
     * Logout
     */
    public function logout()
    {
        $result = $this->Authentication->getResult();
        if ($result->isValid()) {
            $this->Authentication->logout();
            $this->Flash->success('Logged out successfully!');
        }
        return $this->redirect(['controller' => 'Products', 'action' => 'index']);
    }

    public function profile()
    {
        $user = $this->Authentication->getIdentity();
        $userEntity = $this->Users->get($user->id);
        
        if ($this->request->is(['patch', 'post', 'put'])) {
            $userEntity = $this->Users->patchEntity($userEntity, $this->request->getData(), [
                'fieldList' => ['full_name', 'phone', 'address']
            ]);
            
            if ($this->Users->save($userEntity)) {
                $this->Flash->success('Profile updated successfully!');
                return $this->redirect(['action' => 'profile']);
            }
            $this->Flash->error('Could not update profile!');
        }
        
        $this->set('user', $userEntity);
    }

    public function changePassword()
    {
        $user = $this->Authentication->getIdentity();
        $userEntity = $this->Users->get($user->id);
        
        if ($this->request->is(['post', 'put'])) {
            // Verify old password
            $hasher = new \Authentication\PasswordHasher\DefaultPasswordHasher();
            $oldPassword = $this->request->getData('old_password');
            
            if (!$hasher->check($oldPassword, $userEntity->password)) {
                $this->Flash->error('Current password is incorrect!');
            } else {
                $userEntity->password = $this->request->getData('new_password');
                
                if ($this->Users->save($userEntity)) {
                    $this->Flash->success('Password changed successfully!');
                    return $this->redirect(['action' => 'profile']);
                }
                $this->Flash->error('Could not change password!');
            }
        }
    }

    private function mergeSessionCartToUser()
    {
        $sessionId = $this->request->getSession()->id();
        $user = $this->Authentication->getIdentity();
        
        // Find session cart
        $sessionCart = $this->fetchTable('Carts')
            ->find()
            ->where(['session_id' => $sessionId, 'user_id IS' => null])
            ->first();
        
        if ($sessionCart) {
            // Find or create user cart
            $userCart = $this->fetchTable('Carts')->getOrCreateCart($user->id);
            
            // Transfer items from session cart to user cart
            $cartItemsTable = $this->fetchTable('CartItems');
            
            foreach ($sessionCart->cart_items as $sessionItem) {
                // Check if product already exists in user cart
                $existingItem = $cartItemsTable->find()
                    ->where([
                        'cart_id' => $userCart->id,
                        'product_id' => $sessionItem->product_id
                    ])
                    ->first();
                
                if ($existingItem) {
                    // If exists, update quantity
                    $existingItem->quantity += $sessionItem->quantity;
                    $cartItemsTable->save($existingItem);
                } else {
                    // If not exists, create new item
                    $newItem = $cartItemsTable->newEntity([
                        'cart_id' => $userCart->id,
                        'product_id' => $sessionItem->product_id,
                        'quantity' => $sessionItem->quantity,
                        'price' => $sessionItem->price,
                    ]);
                    $cartItemsTable->save($newItem);
                }
            }
            
            // Delete session cart
            $this->fetchTable('Carts')->delete($sessionCart);
        }
    }
}
