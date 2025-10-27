<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

class CartItem extends Entity
{

    // cal total price of item
    protected function _getSubtotal()
    {
        return $this->price * $this->quantity;
    }
}
