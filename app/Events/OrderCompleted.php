<?php
namespace App\Events;

class OrderCompleted
{
    public function __construct(public int $orderId) {}
}
