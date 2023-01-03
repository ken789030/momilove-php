<?php

class RecentOrderValidator implements OrderVaildatorInterface
{
    public function __construct(OrderRepositoriy $orders)
    {
        $this->orders = $orders;
    }

    public function validator(Order $order)
    {
        $recent = $this->orders->getRecentOrderCount($order);
        if ($recent > 0) {
            throw new Exception('Duplicate order likely.');
        }
    }
}
