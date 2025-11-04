<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Cake\ORM\TableRegistry;

/**
 * UsersControllerTest class
 * 
 * Integration tests for UsersController using CakePHP 5 conventions
 */
class UsersControllerTest extends TestCase
{
    use IntegrationTestTrait;

    protected array $fixtures = [
        'app.Users',
    ];

    protected $Users;

    public function setUp(): void
    {
        parent::setUp();
        $this->Users = TableRegistry::getTableLocator()->get('Users');
    }

    /**
     * Test register() - GET request shows registration form
     */
    public function testRegisterGet(): void
    {
        $this->get('/users/register');
        
        $this->assertResponseOk();
        $this->assertResponseContains('Create account');
    }

    /**
     * Test register() - Successful registration
     */
    public function testRegisterSuccess(): void
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        
        $data = [
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'full_name' => 'New User',
            'phone' => '1234567890',
            'address' => '123 New Street',
        ];
        
        $this->post('/users/register', $data);
        
        $this->assertResponseCode(302);
        $this->assertRedirect(['controller' => 'Users', 'action' => 'login']);
        $this->assertFlashMessage('Successfully! Please login.');
        
        // Verify user was created in database
        $user = $this->Users->find()
            ->where(['email' => 'newuser@example.com'])
            ->first();
        
        $this->assertNotEmpty($user);
        $this->assertEquals('New User', $user->full_name);
        $this->assertEquals('active', $user->status);
    }

    /**
     * Test register() - Validation error: missing required fields
     */
    public function testRegisterValidationErrorMissingFields(): void
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        
        $data = [
            'email' => 'incomplete@example.com',
            // Missing password, full_name
        ];
        
        $this->post('/users/register', $data);
        
        $this->assertResponseOk(); // Stays on registration page
        $this->assertFlashMessage('Cannot register.');
    }

    /**
     * Test register() - Validation error: invalid email
     */
    public function testRegisterValidationErrorInvalidEmail(): void
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        
        $data = [
            'email' => 'invalid-email',
            'password' => 'password123',
            'full_name' => 'Test User',
        ];
        
        $this->post('/users/register', $data);
        
        $this->assertResponseOk();
        $this->assertFlashMessage('Cannot register.');
    }

    /**
     * Test register() - Validation error: password too short
     */
    public function testRegisterValidationErrorPasswordTooShort(): void
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        
        $data = [
            'email' => 'shortpass@example.com',
            'password' => '12345', // Less than 6 characters
            'full_name' => 'Test User',
        ];
        
        $this->post('/users/register', $data);
        
        $this->assertResponseOk();
        $this->assertFlashMessage('Cannot register.');
    }

    /**
     * Test register() - Validation error: duplicate email
     */
    public function testRegisterValidationErrorDuplicateEmail(): void
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        
        $data = [
            'email' => 'test@example.com', // Already exists in fixture
            'password' => 'password123',
            'full_name' => 'Duplicate User',
        ];
        
        $this->post('/users/register', $data);
        
        $this->assertResponseOk();
        $this->assertFlashMessage('Cannot register.');
    }

    /**
     * Test register() - Redirect if already logged in
     */
    public function testRegisterRedirectWhenLoggedIn(): void
    {
        // Set up authenticated session
        $this->session([
            'Auth' => [
                'id' => 1,
                'email' => 'test@example.com',
                'role' => 'customer'
            ]
        ]);
        
        $this->get('/users/register');
        
        $this->assertResponseCode(302);
        $this->assertRedirect(['controller' => 'Products', 'action' => 'index']);
    }

    /**
     * Test login() - GET request shows login form
     */
    public function testLoginGet(): void
    {
        $this->get('/users/login');
        
        $this->assertResponseOk();
    }

    /**
     * Test login() - Successful login
     */
    public function testLoginSuccess(): void
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        
        $data = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];
        
        $this->post('/users/login', $data);
        
        $this->assertResponseCode(302);
        $this->assertRedirect('/products');
        
        // Successful redirect indicates authentication was successful
        // The actual session structure is handled by CakePHP Authentication plugin
    }

    /**
     * Test login() - Successful login with redirect parameter
     */
    public function testLoginSuccessWithRedirect(): void
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        
        $data = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];
        
        $this->post('/users/login?redirect=/orders', $data);
        
        $this->assertResponseCode(302);
        $this->assertRedirect('/orders');
    }

    /**
     * Test login() - Invalid credentials
     */
    public function testLoginInvalidCredentials(): void
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        
        $data = [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ];
        
        $this->post('/users/login', $data);
        
        $this->assertResponseOk(); // Stays on login page
        $this->assertFlashMessage('Email or password incorrect!');
        
        // No redirect indicates authentication failed
    }

    /**
     * Test login() - Non-existent user
     */
    public function testLoginNonExistentUser(): void
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        
        $data = [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ];
        
        $this->post('/users/login', $data);
        
        $this->assertResponseOk();
        $this->assertFlashMessage('Email or password incorrect!');
    }

    /**
     * Test login() - Redirect if already logged in
     */
    public function testLoginRedirectWhenLoggedIn(): void
    {
        // Set up authenticated session
        $this->session([
            'Auth' => [
                'id' => 1,
                'email' => 'test@example.com',
                'role' => 'customer'
            ]
        ]);
        
        $this->get('/users/login');
        
        $this->assertResponseCode(302);
        $this->assertRedirect(['controller' => 'Products', 'action' => 'index']);
    }

    /**
     * Test logout() - Successful logout
     */
    public function testLogoutSuccess(): void
    {
        // Set up authenticated session
        $this->session([
            'Auth' => [
                'id' => 1,
                'email' => 'test@example.com',
                'role' => 'customer'
            ]
        ]);
        
        $this->post('/users/logout');
        
        $this->assertResponseCode(302);
        $this->assertRedirect(['controller' => 'Products', 'action' => 'index']);
        $this->assertFlashMessage('Logged out successfully!');
        
        // Verify user is no longer authenticated
        $this->assertSession(null, 'Auth.id');
    }

    /**
     * Test logout() - Redirect when not logged in
     */
    public function testLogoutWhenNotLoggedIn(): void
    {
        $this->post('/users/logout');
        
        $this->assertResponseCode(302);
        $this->assertRedirect(['controller' => 'Products', 'action' => 'index']);
    }

    /**
     * Test profile() - GET request shows profile form
     */
    public function testProfileGet(): void
    {
        // Set up authenticated session
        $this->session([
            'Auth' => [
                'id' => 1,
                'email' => 'test@example.com',
                'role' => 'customer'
            ]
        ]);
        
        $this->get('/users/profile');
        
        $this->assertResponseOk();
        
        // Verify user data is set in view
        $user = $this->Users->get(1);
        $this->assertNotEmpty($user);
    }

    /**
     * Test profile() - Update profile successfully
     */
    public function testProfileUpdateSuccess(): void
    {
        // Set up authenticated session
        $this->session([
            'Auth' => [
                'id' => 1,
                'email' => 'test@example.com',
                'role' => 'customer'
            ]
        ]);
        
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        
        $data = [
            'full_name' => 'Updated Name',
            'phone' => '9999999999',
            'address' => 'Updated Address',
        ];
        
        $this->put('/users/profile', $data);
        
        $this->assertResponseCode(302);
        $this->assertRedirect(['controller' => 'Users', 'action' => 'profile']);
        $this->assertFlashMessage('Profile updated successfully!');
        
        // Verify user was updated in database
        $user = $this->Users->get(1);
        $this->assertEquals('Updated Name', $user->full_name);
        $this->assertEquals('9999999999', $user->phone);
        $this->assertEquals('Updated Address', $user->address);
    }

    /**
     * Test profile() - Update profile with empty fields (optional fields)
     */
    public function testProfileUpdateWithEmptyOptionalFields(): void
    {
        // Set up authenticated session
        $this->session([
            'Auth' => [
                'id' => 1,
                'email' => 'test@example.com',
                'role' => 'customer'
            ]
        ]);
        
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        
        $data = [
            'full_name' => 'Updated Name',
            'phone' => '',
            'address' => '',
        ];
        
        $this->put('/users/profile', $data);
        
        $this->assertResponseCode(302);
        $this->assertFlashMessage('Profile updated successfully!');
    }

    /**
     * Test profile() - Access denied when not logged in
     */
    public function testProfileAccessDeniedWhenNotLoggedIn(): void
    {
        $this->get('/users/profile');
        
        // Should redirect to login or show error
        // CakePHP Authentication typically redirects to login
        $this->assertResponseCode(302);
    }

    /**
     * Test that unauthenticated actions are accessible
     */
    public function testUnauthenticatedActionsAreAccessible(): void
    {
        // No session set
        $this->get('/users/register');
        $this->assertResponseOk();
        
        $this->get('/users/login');
        $this->assertResponseOk();
    }

    /**
     * Test that protected actions require authentication
     */
    public function testProtectedActionsRequireAuthentication(): void
    {
        // No session set
        $this->get('/users/profile');
        // Should redirect (302) or show error
        $this->assertResponseCode(302);
        
        $this->get('/users/change-password');
        $this->assertResponseCode(302);
    }
}

