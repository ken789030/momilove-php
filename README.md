# 上恩資訊第五點問題

## 針對以下範例程式，協助 Code Review 下訂單功能
**表達出「高內聚、低耦合、職責分離」的程式架構**

```php=
<?php
class OrderProcessor {
    public function __construct(BillerInterface $biller)
    {
        $this->biller = $biller;
    }

    public function process(Order $order)
    {
        $recent = $this->getRecentOrderCount($order);

        if ($recent > 0)
        {
            throw new Exception('Duplicate order likely.');
        }

        $this->biller->bill($order->account->id, $order->amount);

        DB::table('orders')->insert(array(
            'account' => $order->account->id,
            'amount' => $order->amount;
            'created_at' => Carbon::now();
        ));
    }

    protected function getRecentOrderCount(Order $order)
    {
        $timestamp = Carbon::now()->subMinutes(5);

        return DB::table('orders')
        ->where('account', $order->account->id)
        ->where('created_at', '>=', $timestamps)
        ->count();
    }

}

```

### 題目分析

**首先可以針對，下訂單這個功能內，有兩個職責，處理訂單與判斷訂單是否重複，
依照此思路來說可以先將getRecentOrderCount 與process中寫入訂單這部分獨立出來，寫成讀取資料庫的類別，OrderRepository.php，這時候分成兩個類別，分別是OrderProcessor.php與OrderRepository.php**

**OrderRepository**

```php
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


```
**需要將此類別注入，因此OrderProcess將改寫成以下方式**

```php
<?php

class OrderProcessor {
    public function __construct(BillerInterface $biller, OrderRepositoriy $orders)
    {
        $this->biller = $biller;
        $this->orders = $orders;
    }

    public function process(Order $order)
    {
        $recent = $this->orders->getRecentOrderCount($order);

        if ($recent > 0)
        {
            throw new Exception('Duplicate order likely.');
        }

        $this->biller->bill($order->account->id, $order->amount);

        $this->orders->createOrder($order);
    }

}
```