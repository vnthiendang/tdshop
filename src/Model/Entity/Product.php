<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

class Product extends Entity
{

    protected function _getDisplayPrice()
    {
        return $this->sale_price ?? $this->price;
    }

    protected function _getOnSale()
    {
        return $this->sale_price !== null && $this->sale_price < $this->price;
    }

    protected function _getDiscountPercent()
    {
        if ($this->on_sale) {
            return round((($this->price - $this->sale_price) / $this->price) * 100);
        }
        return 0;
    }
}
