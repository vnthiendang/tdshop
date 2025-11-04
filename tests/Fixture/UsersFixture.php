<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\TestSuite\Fixture\TestFixture;

class UsersFixture extends TestFixture
{
    public string $table = 'users';

    public array $fields = [
        'id' => ['type' => 'integer'],
        'email' => ['type' => 'string', 'length' => 255, 'null' => false],
        'password' => ['type' => 'string', 'length' => 255, 'null' => false],
        'full_name' => ['type' => 'string', 'length' => 255, 'null' => false],
        'phone' => ['type' => 'string', 'length' => 50, 'null' => true],
        'address' => ['type' => 'text', 'null' => true],
        'role' => ['type' => 'string', 'length' => 50, 'null' => true, 'default' => 'customer'],
        'status' => ['type' => 'string', 'length' => 50, 'null' => true, 'default' => 'active'],
        'created' => 'datetime',
        'modified' => 'datetime',
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
            'unique' => ['type' => 'unique', 'columns' => ['email']],
        ],
    ];

    public function init(): void
    {
        $hasher = new DefaultPasswordHasher();
        $this->records = [
            [
                'id' => 1,
                'email' => 'test@example.com',
                'password' => $hasher->hash('password123'),
                'full_name' => 'Test User',
                'phone' => '1234567890',
                'address' => '123 Test Street',
                'role' => 'customer',
                'status' => 'active',
                'created' => '2025-01-01 00:00:00',
                'modified' => '2025-01-01 00:00:00',
            ],
            [
                'id' => 2,
                'email' => 'admin@example.com',
                'password' => $hasher->hash('admin123'),
                'full_name' => 'Admin User',
                'phone' => '0987654321',
                'address' => '456 Admin Avenue',
                'role' => 'admin',
                'status' => 'active',
                'created' => '2025-01-01 00:00:00',
                'modified' => '2025-01-01 00:00:00',
            ],
            [
                'id' => 4,
                'email' => 'test@gmail.com',
                'password' => $hasher->hash('12345678'),
                'full_name' => 'John Doe',
                'phone' => '5555555555',
                'address' => '789 Main St',
                'role' => 'customer',
                'status' => 'active',
                'created' => '2025-10-29 00:00:00',
                'modified' => '2025-10-29 00:00:00',
            ],
        ];
        parent::init();
    }
}
