<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/5/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{
    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('FormProtection');`
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Flash');

        /*
         * Enable the following component for recommended CakePHP form protection settings.
         * see https://book.cakephp.org/5/en/controllers/components/form-protection.html
         */
        //$this->loadComponent('FormProtection');

        // Authentication
        $this->loadComponent('Authentication.Authentication');
        
        // $this->loadComponent('Cart');
    }

    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        
        // get current user info
        $user = $this->Authentication->getIdentity();
        $this->set('currentUser', $user);
        
        // Only populate header cart for full page GET requests (avoid doing this for
        // AJAX or non-GET requests to prevent extra DB work on background calls)
        if ($this->request->is('ajax') || !$this->request->is('get')) {
            $this->set('headerCart', null);
            return;
        }

        if ($user) {
            $cart = $this->fetchTable('Carts')->getOrCreateCart($user->id);
        } else {
            $sessionId = $this->request->getSession()->id();
            $cart = $this->fetchTable('Carts')->getOrCreateCart(null, $sessionId);
        }
        $this->set('headerCart', $cart);
    }

    protected function isAdmin()
    {
        $user = $this->Authentication->getIdentity();
        return $user && $user->role === 'admin';
    }
    
    /**
     * Helper: request admin role access
     */
    protected function requireAdmin()
    {
        if (!$this->isAdmin()) {
            $this->Flash->error('Do not have access permission!');
            return $this->redirect(['controller' => 'Products', 'action' => 'index']);
        }
        return null;
    }
}
