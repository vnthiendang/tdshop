<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Log\Log;
use Firebase\JWT\JWT;

class UsersController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        // $this->httpClient = new Client();
    }
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);

        $this->Authentication->allowUnauthenticated(['login', 'register']);
    }

    /**
     * register new user
     */
    public function register()
    {
        $user = $this->Users->newEmptyEntity();

        if ($this->request->is('post')) {

            $user = $this->Users->patchEntity($user, $this->request->getData());
            $user->status = 'active';
            Log::error('Register user Data: ' . json_encode($user));
            if ($this->Users->save($user)) {
                // return json response
                return $this->response
                    ->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => true,
                        'message' => 'Registration successful! Please login.',
                    ]));

            }
            // Log validation errors for debugging
            if ($user->getErrors()) {
                Log::error('User save failed: ' . json_encode($user->getErrors()));
                return $this->response
                    ->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode([
                        'success' => false,
                        'errors' => $user->getErrors(),
                    ]));
            }
        }

    }

    /**
     * login user
     */
    public function login()
    {
        $this->request->allowMethod(['get', 'post']);

        $result = $this->Authentication->getResult();

        if ($this->request->is('post') && $result->isValid()) {
            $user = $this->Authentication->getIdentity();
            $key = env('JWT_SECRET');

            $payload = [
                'sub' => $user->id,
                'email' => $user->email,
                'iat' => time(),
                'exp' => time() + 86400,
            ];

            $jwt = JWT::encode($payload, $key, 'HS256');

            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => true,
                    'token' => $jwt,
                    'user' => $user,
                ]));
        }

        if ($this->request->is('post') && !$result->isValid()) {
            return $this->response
                ->withType('application/json')
                ->withStatus(401)
                ->withStringBody(json_encode(['success' => false, 'error' => 'Invalid credentials']));
        }
    }

    /**
     * Logout
     */
    public function logout()
    {
        $this->request->allowMethod(['post', 'get']);
        $this->Authentication->logout();
        // invalidate token
        // Stateless JWT: client discards token; server keeps no session
        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode(['message' => 'Logged out']));
    }

    public function profile()
    {
        $this->request->allowMethod(['get', 'patch', 'post', 'put']);
        $user = $this->Authentication->getIdentity();
        if (!$user) {
            return $this->response
                ->withStatus(401)
                ->withType('application/json')
                ->withStringBody(json_encode(['error' => 'Authentication required']));
        }
        $userEntity = $this->Users->get($user->id);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $userEntity = $this->Users->patchEntity($userEntity, $this->request->getData(), [
                'fieldList' => ['full_name', 'phone', 'address']
            ]);

            if ($this->Users->save($userEntity)) {
                return $this->response
                    ->withType('application/json')
                    ->withStringBody(json_encode([
                        'message' => 'Profile updated successfully!',
                        // 'success' => true,
                        'data' => $userEntity,
                    ]));
            }

            return $this->response
                ->withType('application/json')
                ->withStatus(400)
                ->withStringBody(json_encode(['error' => 'Could not update profile!']));
        }

        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode([
                'data' => $userEntity
            ]));
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
