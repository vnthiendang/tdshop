<?php
namespace App\Service\Event;


interface EventPublisher
{
    public function publish(string $name, $data): void;
}