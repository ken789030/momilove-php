<?php

namespace app\Controllers;


class OrderProcessor {
    public function __construct(BillerInterface $biller, OrderRepository $orders, array $validates = [])
    {
        $this->biller = $biller;
        $this->orders = $orders;
        $this->validates = $validates;
    }

    public function process(Order $order)
    {

        foreach ($this->validates as $validate) {
            $validate->validate($order);
        }

        $this->biller->bill($order->account->id, $order->amount);

        $this->orders->createOrder($order);
    }

}