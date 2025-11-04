<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class ProductsFixture extends TestFixture
{
    public string $table = 'products';

    public array $fields = [
        'id' => ['type' => 'integer'],
        'name' => ['type' => 'string', 'length' => 255, 'null' => false],
        'slug' => ['type' => 'string', 'length' => 255, 'null' => false],
        'price' => ['type' => 'decimal', 'length' => 10, 'precision' => 2, 'null' => false],
        'stock' => ['type' => 'integer', 'null' => true, 'default' => 0],
        'status' => ['type' => 'string', 'length' => 50, 'null' => false, 'default' => 'active'],
        'featured' => ['type' => 'boolean', 'null' => true, 'default' => false],
        'category_id' => ['type' => 'integer', 'null' => false],
        'created' => 'datetime',
        'modified' => 'datetime',
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ];

    public array $records = [
        [
            'id' => 1,
            'name' => 'Active Phone',
            'slug' => 'active-phone',
            'price' => 199.99,
            'stock' => 10,
            'status' => 'active',
            'featured' => true,
            'category_id' => 1,
            'created' => '2025-01-01 00:00:00',
            'modified' => '2025-01-01 00:00:00',
        ],
        [
            'id' => 2,
            'name' => 'Active Laptop',
            'slug' => 'active-laptop',
            'price' => 999.00,
            'stock' => 5,
            'status' => 'active',
            'featured' => false,
            'category_id' => 1,
            'created' => '2025-01-01 00:00:00',
            'modified' => '2025-01-01 00:00:00',
        ],
        [
            'id' => 3,
            'name' => 'Inactive Mouse',
            'slug' => 'inactive-mouse',
            'price' => 20.00,
            'stock' => 100,
            'status' => 'inactive',
            'featured' => false,
            'category_id' => 2,
            'created' => '2025-01-01 00:00:00',
            'modified' => '2025-01-01 00:00:00',
        ],
        [
            'id' => 4,
            'name' => 'Active Chair',
            'slug' => 'active-chair',
            'price' => 49.99,
            'stock' => 20,
            'status' => 'active',
            'featured' => false,
            'category_id' => 2,
            'created' => '2025-01-01 00:00:00',
            'modified' => '2025-01-01 00:00:00',
        ],
    ];
}
