<?php

class OrderRepositoriy 
{
    public function getRecentOrderCount(Order $order)
    {
        $timestamp = Carbon::now()->subMinutes(5);

        return DB::table('orders')
        ->where('account', $order->account->id)
        ->where('created_at', '>=', $timestamps)
        ->count();
    }

    public function createOrder(Order $order) 
    {
        DB::table('orders')->insert(array(
            'account' => $order->account->id,
            'amount' => $order->amount;
            'created_at' => Carbon::now();
        ));
    }
}