<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

class Order extends Entity
{
    protected array $_accessible = [
        '*' => true,
        'id' => false,
    ];
}
