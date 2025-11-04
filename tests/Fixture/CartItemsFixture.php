<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class CartItemsFixture extends TestFixture
{
    public string $table = 'cart_items';

    public array $fields = [
        'id' => ['type' => 'integer'],
        'cart_id' => ['type' => 'integer', 'null' => false],
        'product_id' => ['type' => 'integer', 'null' => false],
        'quantity' => ['type' => 'integer', 'null' => false, 'default' => 1],
        'price' => ['type' => 'decimal', 'length' => 10, 'precision' => 2, 'null' => false, 'default' => 0],
        'created' => 'datetime',
        'modified' => 'datetime',
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ];

    public array $records = [
        [
            'id' => 1,
            'cart_id' => 1,
            'product_id' => 1,
            'quantity' => 1,
            'price' => 199.99,
            'created' => '2025-01-01 00:00:00',
            'modified' => '2025-01-01 00:00:00',
        ],
    ];
}
