<?php
namespace App\Service\Event;


use Cake\Event\EventManager;


class CakeEventBus implements EventPublisher
{
    protected $em;

    public function __construct()
    {
        $this->em = EventManager::instance();
    }

    public function publish(string $name, $data): void
    {
        $this->em->dispatch(new \Cake\Event\Event($name, $this, $data));
    }
}