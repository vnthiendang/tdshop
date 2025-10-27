<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

class Cart extends Entity
{
    // Calculate cart total
    protected function _getTotal()
    {
        if (empty($this->cart_items)) {
            return 0;
        }

        $total = 0;
        foreach ($this->cart_items as $item) {
            $total += $item->subtotal;
        }
        return $total;
    }

    // Total number of items
    protected function _getTotalItems()
    {
        if (empty($this->cart_items)) {
            return 0;
        }

        $count = 0;
        foreach ($this->cart_items as $item) {
            $count += $item->quantity;
        }
        return $count;
    }
}
