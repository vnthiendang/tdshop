<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

class PaymentLogs extends Entity
{
    protected array $_accessible = [
        '*' => true,
        'id' => false,
    ];
}
