<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class CartsFixture extends TestFixture
{
    public string $table = 'carts';

    public array $fields = [
        'id' => ['type' => 'integer'],
        'user_id' => ['type' => 'integer', 'null' => true],
        'session_id' => ['type' => 'string', 'length' => 64, 'null' => true],
        'created' => 'datetime',
        'modified' => 'datetime',
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ];

    public array $records = [
        [
            'id' => 1,
            'user_id' => 1,
            'session_id' => null,
            'created' => '2025-01-01 00:00:00',
            'modified' => '2025-01-01 00:00:00',
        ],
        [
            'id' => 2,
            'user_id' => null,
            'session_id' => 'test-session-id',
            'created' => '2025-01-01 00:00:00',
            'modified' => '2025-01-01 00:00:00',
        ],
    ];
}
