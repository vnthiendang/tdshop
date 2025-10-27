<?php
namespace App\Listener;


use Cake\Event\EventInterface;
use Cake\Mailer\Mailer;


class EmailNotificationListener
{
public function implementedEvents(): array
{
return [
'Order.placed' => 'onOrderPlaced'
];
}


public function onOrderPlaced(EventInterface $event)
{
$data = $event->getData();
$order = $data['order'];


$mailer = new Mailer('default');
$mailer->setTo($order->customer->email)
->setSubject('Order confirmed: ' . $order->id)
->deliver('Thank you for your order!');
}
}