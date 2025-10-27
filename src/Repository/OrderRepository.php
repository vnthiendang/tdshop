<?php
namespace App\Repository;


use Cake\ORM\TableRegistry;
use App\Model\Entity\Order;


class OrderRepository
{
    protected $ordersTable; 


    public function __construct()
    {
        $this->ordersTable = TableRegistry::getTableLocator()->get('Orders');
    }   

    public function saveOrder(array $data)
    {
        $order = $this->ordersTable->newEntity($data, ['associated' => ['OrderItems']]);
        return $this->ordersTable->save($order);
    }   

    public function findById($id)
    {
        return $this->ordersTable->get($id, ['contain' => ['OrderItems', 'Customers']]);
    }
}