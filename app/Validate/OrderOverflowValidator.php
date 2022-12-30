<?php

class OrderOverflowValidator implements OrderVaildatorInterface
{
 

    public function validator(Order $order)
    {
        if ($this->order->amount->isOverflow())
        {
            throw new Exception('The Order amount is overflow.');
        }
    }
}