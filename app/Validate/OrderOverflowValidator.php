<?php

class OrderOverflowValidator implements OrderVaildatorInterface
{
 

    public function validator(Order $order)
    {
        $overflowAmount = 5000;
        if ($this->order->amount > $overflowAmount) {
            throw new Exception('The Order amount is overflow.');
        }
    }
}